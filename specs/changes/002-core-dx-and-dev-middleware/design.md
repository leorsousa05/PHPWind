# Design: Core DX & Dev Middleware Improvements

## Overview

Five cohesive, backward-compatible improvements to the PHPWind core and its dev tooling: change-aware dev middleware, a framework-agnostic config loader, captured process output, a configurable asset public dir, and removal of duplicated platform-branch code.

## Assumptions & Defaults

- Change detection tracks **only** the input CSS file (`inputCss`) via a `mtime|size` fingerprint. Revisit if content scanning is later required.
- Middleware change detection is **on by default**; callers may opt out via a constructor flag.
- The middleware state file lives at `.phpwind/.cache/middleware.json` (git-ignored; created on demand).
- `Runner` captures output **only when not in watch mode**; watch mode streams to parent stdio to avoid unbounded buffering.
- `config/phpwind.php` becomes framework-agnostic (relative paths + `Env`). Laravel's `ServiceProvider` restores absolute paths.

## Proposed Directory & File Structure

```
specs/changes/002-core-dx-and-dev-middleware/
├── .spec.yaml
├── proposal.md
├── design.md
└── tasks.md

src/ChangeDetection/FileChangeDetector.php      (new)
src/Config/Env.php                              (new)
src/Config/ConfigLoader.php                     (new)
src/Binary/ProcessResult.php                    (new)

src/Config/PHPWindConfig.php                    (modify: add toArray())
src/config/phpwind.php                          (modify: framework-agnostic)
src/Binary/PlatformResolver.php                 (modify: dedupe branches)
src/Binary/Runner.php                           (modify: add runResult())
src/Compiler/CompilationResult.php              (modify: add stdout/stderr)
src/Compiler/TailwindCompiler.php               (modify: populate output)
src/Helper/AssetHelper.php                      (modify: publicDir param)
src/Helper/functions.php                        (modify: phpwind_css publicDir)
src/Symfony/Twig/PHPWindTwigExtension.php       (modify: optional publicDir)
src/Middleware/OnDemandCompilerMiddleware.php   (modify: change detection)
src/Laravel/PHPWindServiceProvider.php          (modify: absolute path resolution)

tests/EnvTest.php                               (new)
tests/ConfigLoaderTest.php                      (new)
tests/FileChangeDetectorTest.php                (new)
tests/OnDemandCompilerMiddlewareTest.php        (new)
tests/RunnerTest.php                            (modify: runResult)
tests/AssetHelperTest.php                       (modify: publicDir)
tests/PlatformResolverTest.php                  (modify: unchanged names)
tests/TailwindCompilerTest.php                  (modify: output fields)
```

## File-by-File Changes

| File | Action | What changes | Design ref |
|------|--------|--------------|------------|
| `src/Config/Env.php` | Add | `get(string, mixed): mixed` reads getenv → `$_ENV` → `$_SERVER` → default | §Config |
| `src/Config/ConfigLoader.php` | Add | `load(string): PHPWindConfig` requires file array + `fromArray`; `fromArray(array): PHPWindConfig` delegate | §Config |
| `src/Config/PHPWindConfig.php` | Modify | Add `toArray(): array` for round-tripping loader overrides | §Config |
| `config/phpwind.php` | Modify | Replace Laravel helpers with relative paths + `Env::get` | §Config |
| `src/Binary/PlatformResolver.php` | Modify | Deduplicate `getBinaryName()` v3/v4 branches | §Platform |
| `src/Binary/ProcessResult.php` | Add | `exitCode:int`, `stdout:string`, `stderr:string` | §Runner |
| `src/Binary/Runner.php` | Modify | Add `runResult()` capturing pipes (non-watch); `run()` stays BC delegating | §Runner |
| `src/Compiler/CompilationResult.php` | Modify | Add `stdout`, `stderr` readonly props (defaults `''`) | §Runner |
| `src/Compiler/TailwindCompiler.php` | Modify | `compileResult()` uses `runResult()` and populates output | §Runner |
| `src/Helper/AssetHelper.php` | Modify | `css()` gains trailing `?string $publicDir = null`; `cssFromManifest` unchanged | §Assets |
| `src/Helper/functions.php` | Modify | `phpwind_css()` gains optional `publicDir` | §Assets |
| `src/Symfony/Twig/PHPWindTwigExtension.php` | Modify | `renderCss()` gains optional `publicDir` | §Assets |
| `src/Middleware/OnDemandCompilerMiddleware.php` | Modify | Add `FileChangeDetector`, `checkForChanges` flag | §Middleware |
| `src/ChangeDetection/FileChangeDetector.php` | Add | Fingerprint + state persistence | §Middleware |
| `src/Laravel/PHPWindServiceProvider.php` | Modify | Resolve relative config paths to absolute for Laravel | §Config |

## Code Architecture & Design Patterns

- **Architecture Model:** Framework-agnostic core + thin adapters.
- **Design Patterns:** Value objects (`ProcessResult`, `CompilationResult`), static factory/loader (`ConfigLoader`, `Env`), Strategy-adjacent change detector (swappable, single default impl).

## Data Model & Interfaces

```php
namespace PHPWind\Config;

final class Env
{
    public static function get(string $key, mixed $default = null): mixed;
}

final class ConfigLoader
{
    public static function fromArray(array $config): \PHPWind\Config\PHPWindConfig;
    /** @throws \PHPWind\Exception\InvalidConfigurationException */
    public static function load(string $file): \PHPWind\Config\PHPWindConfig;
}
```

```php
namespace PHPWind\Config;

// added to PHPWindConfig (BC: all existing methods unchanged)
public function toArray(): array; // keys: input_css, output_css, binary_dir, version, minify, watch
```

```php
namespace PHPWind\Binary;

final class ProcessResult
{
    public function __construct(
        public readonly int $exitCode,
        public readonly string $stdout = '',
        public readonly string $stderr = '',
    ) {}
}

class Runner
{
    public function run(string $binaryPath, PHPWindConfig $config): int;            // BC, delegates
    public function runResult(string $binaryPath, PHPWindConfig $config): ProcessResult; // new
}
```

```php
namespace PHPWind\Compiler;

class CompilationResult
{
    public function __construct(
        public readonly int $exitCode,
        public readonly string $outputPath,
        public readonly int $durationMs,
        public readonly string $stdout = '',
        public readonly string $stderr = '',
    ) {}
}
```

```php
namespace PHPWind\ChangeDetection;

final class FileChangeDetector
{
    public function __construct(private string $stateFile = '.phpwind/.cache/middleware.json') {}

    /** @param \PHPWind\Config\PHPWindConfig $config */
    public function hasChanged($config): bool;
    public function record($config): void;
}
```

```php
namespace PHPWind\Middleware;

class OnDemandCompilerMiddleware
{
    public function __construct(
        PHPWindConfig $config,
        bool $isDev = true,
        ?TailwindCompiler $compiler = null,
        bool $checkForChanges = true,                       // new
        ?FileChangeDetector $detector = null,               // new
    ) {}
    public function handle(callable $next, mixed $request): mixed;
}
```

```php
namespace PHPWind\Helper;

class AssetHelper
{
    public static function css(string $path = 'css/app.css', bool $versioned = true, ?string $publicDir = null): string;
    public static function cssFromManifest(AssetManifest $manifest, string $path = 'css/app.css', bool $versioned = true): string;
}
```

## Edge Case & Error Handling Matrix

| Scenario / Input | Expected Behavior | Return Value / Error Thrown |
|------------------|-------------------|-----------------------------|
| Middleware, input unchanged | Skip compilation | `$next($request)` unchanged |
| Middleware, input changed / first run | Compile, record state | Recompiled output |
| Middleware, `checkForChanges=false` | Always compile (old behavior) | Recompiled output |
| Middleware, state file dir unwritable | Degrade to always-compile, log once | Recompiles (no throw) |
| `Env::get` missing key | Return default | `$default` |
| `ConfigLoader::load` file returns non-array / missing | Throw | `InvalidConfigurationException` |
| `ConfigLoader::load` invalid version | Propagate | `InvalidConfigurationException` |
| `Runner::runResult`, watch mode | Stream to parent stdio | Empty stdout/stderr capture |
| `Runner::runResult`, non-watch, child emits output | Capture | Output in `ProcessResult` |
| `Runner` null byte in path | Reject early | `BinaryExecutionException` |
| `AssetHelper::css` with `publicDir` set | Read hash from that dir | Link with `?v=<hash>` |
| `AssetHelper::css` file missing | Skip versioning | Link without query string |

## Flow Diagrams

1. Dev request → middleware `handle()`.
2. If `checkForChanges` and not `hasChanged()` → skip compile → `$next`.
3. Else → `compile()` → `detector->record()` → `$next`.

## State Management & Caching

- `FileChangeDetector` persists the last fingerprint to `.phpwind/.cache/middleware.json` (cross-request).
- No other state introduced; `ProcessResult`/`CompilationResult` are immutable value objects.

## Performance Considerations

- Change-aware middleware removes a process spawn + binary run on every unchanged dev request — the dominant win.
- Fingerprint uses `filemtime` + `filesize` (no content hash), keeping per-request cost near zero.
- Output capture buffers child output; explicitly disabled in watch mode to bound memory.

## Security Considerations

- `Runner` continues to reject null-byte binary paths.
- `ConfigLoader::load` includes a user-supplied PHP file — documented that it must only be pointed at trusted config files.
- Captured stdout/stderr is plain text surfaced to the caller; no secret filtering added (config files do not carry secrets by convention).
