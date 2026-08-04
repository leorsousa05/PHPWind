# Design: Improve API, Binary Cache, Asset Manifest, and Test Coverage

## 1. Seven Analysis Questions

### 1.1 Domain and bounded context placement?

PHPWind is a single bounded context: **Tailwind CSS integration for PHP**. Within it, the change touches four sub-domains:

- **Configuration** (`src/Config`) — validation and defaults.
- **Binary Management** (`src/Binary`) — discovery, download, caching, and invalidation of the Tailwind CLI.
- **Compilation** (`src/Compiler`) — orchestration of binary execution.
- **Asset Publishing** (`src/Helper`, `src/Manifest`) — cache-busting URLs and manifest generation.

No new bounded contexts are introduced; the change strengthens existing internal boundaries.

### 1.2 Core responsibilities of new/changed components?

- **`PHPWindException` hierarchy** — provides domain-specific, catchable failures instead of generic `RuntimeException`.
- **`PHPWindConfig::validate()`** — enforces invariants (non-empty paths, supported version format) without breaking construction-time BC.
- **`BinaryManager`** — owns the lifecycle of the downloaded binary: resolves the versioned path, triggers download on miss/version change, and clears stale binaries.
- **`Downloader`** — performs the actual HTTP fetch with safe defaults and configurable overrides; remains stateless.
- **`Runner`** — executes the binary and returns a structured result; unchanged in scope but gains testability.
- **`TailwindCompiler`** — orchestrates `BinaryManager` + `Runner`; gains `compileResult()` for structured output while keeping `compile()` backward compatible.
- **`AssetManifest` + `AssetEntry`** — immutable value objects representing a logical-path → versioned-path/hash mapping.
- **`AssetHelper`** — continues generating `<link>` tags; gains optional manifest-based resolution with transparent fallback.

### 1.3 Contracts (interfaces, types, APIs) to define or change?

New contracts:

- `PHPWindException` (base abstract exception)
- `InvalidConfigurationException`, `BinaryDownloadException`, `BinaryExecutionException`, `AssetManifestException`
- `CompilationResult` value object
- `AssetEntry` value object
- `AssetManifest` value object

Changed contracts (BC preserved):

- `PHPWindConfig` — adds `validate(): void`; constructor and `fromArray()` unchanged.
- `TailwindCompiler` — adds `compileResult(PHPWindConfig): CompilationResult`; `compile()` keeps returning `int`.
- `AssetHelper` — adds `cssFromManifest(AssetManifest, string, bool): string`; `css()` signature unchanged.
- `BinaryManager` — new public class, injected into `TailwindCompiler`.

### 1.4 Which parts need tests per TDD skip criteria?

Every new or modified public class needs unit tests:

- `PHPWindConfig::validate()` and edge cases in `fromArray()`.
- `BinaryManager` — versioned path resolution, re-download trigger, cache hit, clear behavior.
- `Downloader` — successful download, HTTP failure, partial file cleanup, permissions on Unix, Windows filename.
- `Runner` — command construction (`minify`, `watch`, escaping), exit code propagation, exception on `proc_open` failure.
- `TailwindCompiler` — orchestration with injected doubles, BC `compile()` returns int.
- `AssetManifest`/`AssetEntry` — construction, serialization, lookup, write/read round-trip.
- `AssetHelper` — query-string mode, manifest mode, missing file behavior, HTML escaping.

### 1.5 Architecture that minimizes ambiguity?

A **layered, dependency-injection friendly** architecture:

- **Value/Config layer** — immutable data (`PHPWindConfig`, `CompilationResult`, `AssetManifest`, `AssetEntry`).
- **Service layer** — stateless collaborators (`Downloader`, `Runner`, `BinaryManager`, `AssetHelper`).
- **Orchestration layer** — `TailwindCompiler` wires services using constructor injection.
- **Exception layer** — domain exceptions signal failures clearly.

This keeps responsibilities small and lets tests inject fakes/doubles without global state.

### 1.6 Project structure changes needed?

```
src/
├── Binary/
│   ├── BinaryManager.php        [NEW]
│   ├── Downloader.php           [MODIFY]
│   ├── PlatformResolver.php     [MODIFY lightly]
│   └── Runner.php               [MODIFY lightly]
├── Compiler/
│   ├── CompilationResult.php    [NEW]
│   └── TailwindCompiler.php     [MODIFY]
├── Config/
│   └── PHPWindConfig.php        [MODIFY]
├── Exception/
│   ├── PHPWindException.php     [NEW]
│   ├── InvalidConfigurationException.php [NEW]
│   ├── BinaryDownloadException.php       [NEW]
│   ├── BinaryExecutionException.php      [NEW]
│   └── AssetManifestException.php        [NEW]
├── Helper/
│   ├── AssetHelper.php          [MODIFY]
│   └── functions.php            [MODIFY lightly]
└── Manifest/
    ├── AssetEntry.php           [NEW]
    └── AssetManifest.php        [NEW]

tests/
├── BinaryManagerTest.php        [NEW]
├── DownloaderTest.php           [NEW]
├── RunnerTest.php               [NEW]
├── TailwindCompilerTest.php     [NEW]
├── AssetHelperTest.php          [NEW]
├── AssetManifestTest.php        [NEW]
├── PHPWindConfigTest.php        [MODIFY]
└── ...existing tests remain unchanged
```

### 1.7 Key trade-offs?

| Decision | Alternative | Why chosen |
|---|---|---|
| Versioned binary filenames (`tailwind-v4.0.0`) | Metadata file alongside generic binary | Filenames are self-describing, trivial to clean, and avoid extra file I/O on every run. |
| Add `compileResult()` instead of changing `compile()` | Break BC by returning object | The user explicitly requested BC. |
| Manifest as optional opt-in | Replace query-string behavior | Maintains zero-config behavior while enabling advanced CDN scenarios. |
| Validate config lazily at use time | Validate in constructor | Constructor validation would silently break callers who build configs incrementally or via frameworks. |
| cURL `SSL_VERIFYPEER` kept true by default | Hard-code false for compatibility | Safer default; add a constructor flag for offline/self-signed environments. |

---

## 2. Applied Patterns

### 2.1 Senior Architecture & Design Pillars

- **Single Responsibility Principle (SRP):** `BinaryManager` owns cache invalidation; `Downloader` only fetches bytes; `Runner` only executes processes.
- **Dependency Inversion:** `TailwindCompiler` accepts injected `BinaryManager` and `Runner`, enabling unit tests with doubles.
- **Fail Fast / Domain Exceptions:** A small exception hierarchy replaces opaque `RuntimeException` and integer codes.
- **Immutable Value Objects:** `CompilationResult`, `AssetEntry`, and `AssetManifest` are read-only after construction.
- **Backward Compatibility:** New features are additive; existing public signatures remain unchanged.

### 2.2 Design Patterns

- **Value Object** — `CompilationResult`, `AssetEntry`.
- **Data Transfer Object (DTO)** — `PHPWindConfig`.
- **Strategy / Injectable Service** — `Downloader` and `Runner` are swappable through constructor injection.
- **Factory Method** — `AssetManifest::fromArray()` and `AssetManifest::read()`.

---

## 3. Implementation Strategy

### Phase 1 — Foundation

1. Create `src/Exception/` hierarchy.
2. Add `PHPWindConfig::validate()` with clear rules:
   - `inputCss` and `outputCss` must be non-empty strings.
   - `binaryDir` must be non-empty.
   - `version` must match `/^v?\d+\.\d+\.\d+/` (allow `v4.0.0`, `4.0.0`, `v3.4.17`).
3. Call `validate()` at the start of every handler/compiler public method that consumes a config.

### Phase 2 — Binary Management

1. Add `PlatformResolver::getVersionedBinaryName(string $version): string` that returns `tailwind-v4.0.0` / `tailwind-v4.0.0.exe`. Keep `getLocalBinaryFilename()` for BC.
2. Refactor `Downloader`:
   - Constructor accepts optional `int $timeoutSeconds = 120` and `bool $verifySsl = true`.
   - `download(string $url, string $destinationPath): void` becomes the public entry point (extracted from `ensureBinaryInstalled`).
   - On failure, remove partially written files.
   - Apply executable permissions only on non-Windows systems.
3. Introduce `BinaryManager`:
   - `resolveBinaryPath(string $version): string` returns the versioned local path; downloads on miss.
   - `clearCachedBinary(?string $version = null): bool` removes the generic binary, the versioned binary, or all versioned binaries.
4. Update `CleanHandler` to use `BinaryManager::clearCachedBinary()`.

### Phase 3 — Compilation Result

1. Add `CompilationResult` value object.
2. Add `TailwindCompiler::compileResult(PHPWindConfig $config): CompilationResult`.
3. Rewrite `TailwindCompiler::compile()` to delegate to `compileResult()` and return `exitCode`.

### Phase 4 — Asset Manifest

1. Add `AssetEntry` and `AssetManifest` value objects.
2. Add `AssetManifest::generate(string $publicDir, array $logicalPaths = ['css/app.css']): self`:
   - For each logical path, compute MD5 of `publicDir/$path` if it exists.
   - Store `logicalPath → AssetEntry(path, hash)`.
3. Add `AssetManifest::write(string $path): void` and `AssetManifest::read(string $path): self` for JSON persistence.
4. Extend `AssetHelper`:
   - `cssFromManifest(AssetManifest $manifest, string $path = 'css/app.css', bool $versioned = true): string`.
   - `css()` remains unchanged; optionally, add a global `useManifestFile(string $manifestPath): void` setter for framework integrations.

### Phase 5 — Tests

1. Write tests in dependency order: value objects → services → orchestrators.
2. Use PHPUnit mocks and temporary directories; never hit real network or real Tailwind binary in unit tests.
3. Ensure existing tests still pass.

---

## 4. Contracts / Interfaces

### 4.1 Exception Hierarchy

```php
namespace PHPWind\Exception;

abstract class PHPWindException extends \Exception {}

class InvalidConfigurationException extends PHPWindException {}
class BinaryDownloadException extends PHPWindException {}
class BinaryExecutionException extends PHPWindException {}
class AssetManifestException extends PHPWindException {}
```

### 4.2 Configuration

```php
namespace PHPWind\Config;

class PHPWindConfig
{
    public function __construct(
        public string $inputCss = 'resources/css/app.css',
        public string $outputCss = 'public/css/app.css',
        public string $binaryDir = 'vendor/bin/tailwind-cli',
        public string $version = 'v4.0.0',
        public bool $minify = false,
        public bool $watch = false
    ) {}

    public static function fromArray(array $config): self {}

    /**
     * @throws InvalidConfigurationException
     */
    public function validate(): void {}
}
```

### 4.3 Binary Management

```php
namespace PHPWind\Binary;

class PlatformResolver
{
    public static function getBinaryName(string $version = 'v4.0.0'): string {}
    public static function getVersionedBinaryName(string $version): string {}
    public static function getDownloadUrl(string $version = 'v4.0.0'): string {}
    public static function getLocalBinaryFilename(): string {}
}

class Downloader
{
    public function __construct(
        private int $timeoutSeconds = 120,
        private bool $verifySsl = true
    ) {}

    /**
     * @throws BinaryDownloadException
     */
    public function download(string $url, string $destinationPath): void {}
}

class BinaryManager
{
    public function __construct(
        private string $binaryDir,
        private ?Downloader $downloader = null
    ) {}

    /**
     * @throws BinaryDownloadException
     */
    public function resolveBinaryPath(string $version): string {}

    public function clearCachedBinary(?string $version = null): bool {}
}
```

### 4.4 Compilation

```php
namespace PHPWind\Compiler;

class CompilationResult
{
    public function __construct(
        public readonly int $exitCode,
        public readonly string $outputPath,
        public readonly int $durationMs
    ) {}
}

class TailwindCompiler
{
    public function __construct(
        ?BinaryManager $binaryManager = null,
        ?Runner $runner = null
    ) {}

    public function compile(PHPWindConfig $config): int {}

    /**
     * @throws InvalidConfigurationException|BinaryDownloadException|BinaryExecutionException
     */
    public function compileResult(PHPWindConfig $config): CompilationResult {}
}
```

### 4.5 Asset Manifest

```php
namespace PHPWind\Manifest;

class AssetEntry
{
    public function __construct(
        public readonly string $path,
        public readonly string $hash
    ) {}
}

class AssetManifest
{
    /**
     * @param array<string, AssetEntry> $entries
     */
    public function __construct(private array $entries = []) {}

    public static function fromArray(array $data): self {}
    public static function read(string $path): self {}
    public function toArray(): array {}
    public function write(string $path): void {}
    public function get(string $logicalPath): ?AssetEntry {}
    public function set(string $logicalPath, AssetEntry $entry): self {}
    public static function generate(string $publicDir, array $logicalPaths = ['css/app.css']): self {}
}
```

### 4.6 Asset Helper

```php
namespace PHPWind\Helper;

use PHPWind\Manifest\AssetManifest;

class AssetHelper
{
    public static function css(
        string $path = 'css/app.css',
        bool $versioned = true
    ): string {}

    public static function cssFromManifest(
        AssetManifest $manifest,
        string $path = 'css/app.css',
        bool $versioned = true
    ): string {}
}
```

---

## 5. File-by-File Changes

| File | Change | Rationale |
|---|---|---|
| `src/Exception/PHPWindException.php` | Create | Base domain exception. |
| `src/Exception/InvalidConfigurationException.php` | Create | Configuration validation failures. |
| `src/Exception/BinaryDownloadException.php` | Create | HTTP/network/binary download failures. |
| `src/Exception/BinaryExecutionException.php` | Create | `proc_open` / binary execution failures. |
| `src/Exception/AssetManifestException.php` | Create | Manifest read/write/parse failures. |
| `src/Config/PHPWindConfig.php` | Modify | Add `validate()`; keep constructor and `fromArray()` unchanged. |
| `src/Binary/PlatformResolver.php` | Modify | Add `getVersionedBinaryName()`; keep existing methods. |
| `src/Binary/Downloader.php` | Modify | Refactor into injectable, timeout-aware, SSL-verifying service with explicit `download()` method. |
| `src/Binary/BinaryManager.php` | Create | Own binary cache lifecycle and version invalidation. |
| `src/Binary/Runner.php` | Modify | Return structured result; throw `BinaryExecutionException` on `proc_open` failure. |
| `src/Compiler/CompilationResult.php` | Create | Value object for compilation outcome. |
| `src/Compiler/TailwindCompiler.php` | Modify | Add `compileResult()`; keep `compile()` returning `int`. |
| `src/Manifest/AssetEntry.php` | Create | Value object for manifest entries. |
| `src/Manifest/AssetManifest.php` | Create | Manifest generation, persistence, lookup. |
| `src/Helper/AssetHelper.php` | Modify | Add manifest-based resolution; keep `css()` unchanged. |
| `src/Helper/functions.php` | Modify | Optionally expose `phpwind_manifest()` helper (additive). |
| `src/Command/InitHandler.php` | Modify | Call `config->validate()` at start. |
| `src/Command/CleanHandler.php` | Modify | Use `BinaryManager::clearCachedBinary()`. |
| `bin/phpwind` | Modify | No changes required unless adopting `compileResult()`. Leave untouched to preserve BC. |
| `tests/*` | Create/Modify | Add coverage; extend existing config tests. |
| `specs/living/core/spec.md` | Create on ship | Merged state after implementation. |

---

## 6. Test Plan

### 6.1 Unit Tests

- **`PHPWindConfigTest`**
  - Default values unchanged.
  - `fromArray()` with snake_case keys.
  - `validate()` accepts valid configs.
  - `validate()` rejects empty paths and invalid versions.

- **`BinaryManagerTest`**
  - Returns existing versioned binary without download.
  - Triggers `Downloader::download()` when versioned binary is missing.
  - Clears generic binary, single version, and all versions.
  - Throws `BinaryDownloadException` when download fails.

- **`DownloaderTest`**
  - Successful download writes file and applies Unix permissions.
  - Failed HTTP status removes partial file and throws.
  - Network error removes partial file and throws.
  - Windows path results in `.exe` filename.

- **`RunnerTest`**
  - Builds command with `-i`, `-o`, `--minify`, `--watch`.
  - Escapes binary path and file paths.
  - Returns exit code from `proc_close`.
  - Throws `BinaryExecutionException` when `proc_open` fails.

- **`TailwindCompilerTest`**
  - `compile()` returns exit code (BC).
  - `compileResult()` returns `CompilationResult` with correct fields.
  - Calls `BinaryManager::resolveBinaryPath()` then `Runner::run()`.
  - Propagates exceptions from services.

- **`AssetManifestTest`**
  - `fromArray()` / `toArray()` round-trip.
  - `generate()` computes MD5 hashes for existing files.
  - `read()` / `write()` JSON round-trip.
  - `get()` returns `null` for missing entries.

- **`AssetHelperTest`**
  - `css()` returns query-string tag when file exists.
  - `css()` returns unversioned tag when file missing or `$versioned=false`.
  - `cssFromManifest()` returns manifest-based URL.
  - HTML-escapes special characters in paths.

### 6.2 Regression

- Run full `vendor/bin/phpunit tests` suite.
- Existing `PlatformResolverTest`, `CommandHandlerTest`, and `SymfonyTest` must pass unchanged.

---

## 7. Risk Assessment

| Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|
| BC break in `TailwindCompiler::compile()` return type | Low | High | Do not change signature; add `compileResult()` instead. |
| cURL SSL verification breaks CI or corporate proxies | Medium | Medium | Make `verifySsl` a constructor flag; default to `true`. |
| Versioned binary filenames collide with user expectations | Low | Low | Document naming convention; keep `getLocalBinaryFilename()` for reference. |
| Extra file I/O from manifest generation slows builds | Low | Low | Manifest generation is opt-in; helper still defaults to query string. |
| Tests become flaky due to real network/process use | Medium | High | Mock `Downloader`, `Runner`, and `proc_open`; use temp directories. |

---

## 8. Subagent Parallelization

This change is **not** a good fit for parallel subagents. The components are tightly coupled through `TailwindCompiler` and share the `PHPWindConfig` contract. Changing the exception hierarchy and validation rules first is a sequential gate for the other phases. A single engineer implementation is safer and faster.

```yaml
subagents:
  approved: false
  components: []
```

---

## 9. Assumptions & Defaults

- The user wants **additive improvements** with strict backward compatibility.
- Default cURL timeout: **120 seconds**.
- Default SSL verification: **enabled** (`true`).
- Versioned binary filename template: `tailwind-{version}` (e.g., `tailwind-v4.0.0`) on Unix, `tailwind-v4.0.0.exe` on Windows.
- Default manifest path (when used): `public/phpwind-manifest.json`.
- Manifest JSON schema: `{ "<logical-path>": { "path": "<physical-path>", "hash": "<md5>" } }`.
- PHP version support remains **8.1+**.
