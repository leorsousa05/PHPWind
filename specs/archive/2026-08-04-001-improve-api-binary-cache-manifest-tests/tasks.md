# Implementation Tasks

## Phase 1 — Domain Exceptions & Config Validation

### Task 1.1: Create exception hierarchy ✅
- **Files:**
  - `src/Exception/PHPWindException.php`
  - `src/Exception/InvalidConfigurationException.php`
  - `src/Exception/BinaryDownloadException.php`
  - `src/Exception/BinaryExecutionException.php`
  - `src/Exception/AssetManifestException.php`
- **Depends on:** None
- **Done when:** All five exception classes exist, extend `PHPWindException`, and are autoloadable.

### Task 1.2: Add config validation ✅
- **Files:**
  - `src/Config/PHPWindConfig.php`
  - `tests/PHPWindConfigTest.php`
- **Depends on:** Task 1.1
- **Done when:**
  - `PHPWindConfig::validate()` exists and enforces non-empty `inputCss`, `outputCss`, `binaryDir`, and a version matching `/^v?\d+\.\d+\.\d+/`.
  - Existing tests still pass.
  - New tests cover valid configs and each invalid case.

## Phase 2 — Binary Cache by Version

### Task 2.1: Add versioned binary filename ✅
- **Files:**
  - `src/Binary/PlatformResolver.php`
  - `tests/PlatformResolverTest.php`
- **Depends on:** None
- **Done when:**
  - `PlatformResolver::getVersionedBinaryName(string $version)` returns `tailwind-v{version}` or `tailwind-v{version}.exe` on Windows.
  - Existing methods remain unchanged.
  - Tests cover v3 and v4 on Windows and Unix-like platforms.

### Task 2.2: Refactor Downloader ✅
- **Files:**
  - `src/Binary/Downloader.php`
  - `tests/DownloaderTest.php`
- **Depends on:** Task 1.1
- **Done when:**
  - Constructor accepts `int $timeoutSeconds = 120` and `bool $verifySsl = true`.
  - `download(string $url, string $destinationPath): void` is the public method and throws `BinaryDownloadException` on failure.
  - Partial files are removed on any failure.
  - Unix executable permissions are applied after successful download.
  - Tests mock cURL behavior and verify success, HTTP failure, network failure, and cleanup.

### Task 2.3: Introduce BinaryManager ✅
- **Files:**
  - `src/Binary/BinaryManager.php`
  - `tests/BinaryManagerTest.php`
- **Depends on:** Tasks 2.1, 2.2
- **Done when:**
  - `resolveBinaryPath(string $version)` returns the versioned local path and triggers download only when missing.
  - `clearCachedBinary(?string $version = null)` removes generic binary, a specific version, or all versioned binaries.
  - Tests verify cache hit, cache miss, download propagation, and clear modes.

### Task 2.4: Update CleanHandler ✅
- **Files:**
  - `src/Command/CleanHandler.php`
- **Depends on:** Task 2.3
- **Done when:** `CleanHandler` uses `BinaryManager::clearCachedBinary()` and existing `CommandHandlerTest` still passes.

## Phase 3 — Structured Compilation Result

### Task 3.1: Add CompilationResult value object ✅
- **Files:**
  - `src/Compiler/CompilationResult.php`
- **Depends on:** None
- **Done when:** Value object has public readonly properties `exitCode`, `outputPath`, `durationMs`.

### Task 3.2: Extend TailwindCompiler ✅
- **Files:**
  - `src/Compiler/TailwindCompiler.php`
  - `tests/TailwindCompilerTest.php`
- **Depends on:** Tasks 1.2, 2.3, 3.1
- **Done when:**
  - Constructor injects `BinaryManager` and `Runner`.
  - `compileResult(PHPWindConfig $config): CompilationResult` orchestrates binary resolution + execution + timing.
  - `compile(PHPWindConfig $config): int` delegates to `compileResult()` and returns the exit code (BC).
  - Tests verify both methods and exception propagation.

## Phase 4 — Asset Manifest

### Task 4.1: Create manifest value objects ✅
- **Files:**
  - `src/Manifest/AssetEntry.php`
  - `src/Manifest/AssetManifest.php`
  - `tests/AssetManifestTest.php`
- **Depends on:** Task 1.1
- **Done when:**
  - `AssetEntry` has `path` and `hash` readonly properties.
  - `AssetManifest` supports `fromArray`, `toArray`, `read`, `write`, `get`, `set`, and `generate`.
  - Tests cover all public methods including JSON round-trip.

### Task 4.2: Extend AssetHelper ✅
- **Files:**
  - `src/Helper/AssetHelper.php`
  - `tests/AssetHelperTest.php`
  - `src/Helper/functions.php` (optional new helper)
- **Depends on:** Task 4.1
- **Done when:**
  - `css()` continues to work exactly as before.
  - `cssFromManifest(AssetManifest, string, bool)` generates manifest-based URLs.
  - Tests cover query-string fallback and manifest-based resolution.

## Phase 5 — Handler Hardening

### Task 5.1: Validate config in handlers ✅
- **Files:**
  - `src/Command/InitHandler.php`
- **Depends on:** Task 1.2
- **Done when:** `InitHandler::handle()` calls `$config->validate()` at the top and existing tests pass.

## Phase 6 — Regression & Quality

### Task 6.1: Run full test suite ✅
- **Files:** `tests/`
- **Depends on:** All previous tasks
- **Done when:** `vendor/bin/phpunit tests` passes on PHP 8.1+ with no warnings or deprecations introduced.

### Task 6.2: Update living spec ✅
- **Files:**
  - `specs/living/core/spec.md`
- **Depends on:** Task 6.1
- **Done when:** Living spec reflects the new binary cache, manifest, and API contracts.
