> Merged living specification for the **Core** domain of PHPWind.

## Overview

PHPWind is a zero-Node PHP integration for Tailwind CSS v3/v4. The core domain manages configuration, binary download/cache, compilation orchestration, and asset cache busting.

## Configuration

- `PHPWind\Config\PHPWindConfig` is the central DTO.
- Constructed via constructor or `fromArray()` with snake_case keys.
- `toArray()` round-trips a config back to snake_case keys.
- `validate()` enforces:
  - Non-empty `inputCss`, `outputCss`, `binaryDir`.
  - `version` matches a semantic version prefix (`v4.0.0`, `3.4.17`, etc.).
- `PHPWind\Config\ConfigLoader::load(string $file)` reads a PHP config file and returns a validated `PHPWindConfig`; `fromArray()` builds and validates one. Throws `InvalidConfigurationException` for missing/unreadable files, non-array returns, or invalid values.
- `PHPWind\Config\Env::get(string $key, mixed $default)` reads an environment value from getenv → `$_ENV` → `$_SERVER` → default. It is framework-agnostic (no Laravel `env()`/`resource_path()`).
- The shipped `config/phpwind.php` is framework-agnostic (relative paths + `Env`). The Laravel `ServiceProvider` resolves shipped relative path defaults to Laravel absolute paths, leaving user-customized values untouched.

## Binary Management

- `PHPWind\Binary\PlatformResolver` maps host OS/arch to the correct GitHub release asset.
- `PHPWind\Binary\Downloader` fetches the binary via cURL with configurable timeout and SSL verification.
- `PHPWind\Binary\BinaryManager` caches binaries using versioned filenames:
  - Unix: `tailwind-v4.0.0`
  - Windows: `tailwind-v4.0.0.exe`
- Changing `PHPWindConfig::$version` triggers a new download automatically.
- `BinaryManager::clearCachedBinary()` removes generic, specific, or all versioned binaries.

## Compilation

- `PHPWind\Compiler\TailwindCompiler` orchestrates binary resolution and execution.
- `compile(PHPWindConfig $config): int` remains for backward compatibility.
- `compileResult(PHPWindConfig $config): CompilationResult` returns structured output:
  - `exitCode`
  - `outputPath`
  - `durationMs`
  - `stdout`
  - `stderr`
- `PHPWind\Binary\Runner` executes the binary with `-i`, `-o`, `--minify`, `--watch` flags.
  - `run()` returns the exit code (BC).
  - `runResult()` returns a `ProcessResult` (`exitCode`, `stdout`, `stderr`), capturing child output concurrently.
  - In watch mode, output streams directly to parent stdio to avoid buffering.
  - Rejects null-byte binary paths with `BinaryExecutionException`.

## Asset Manifest

- `PHPWind\Manifest\AssetEntry` stores `path` and `hash` for a single asset.
- `PHPWind\Manifest\AssetManifest` provides:
  - `generate()` from files on disk.
  - `read()` / `write()` JSON persistence.
  - `get()` / `set()` lookup and mutation.
- `PHPWind\Helper\AssetHelper` generates `<link>` tags:
  - `css()` uses query-string cache busting (`?v=<md5>`) by default, and accepts an optional trailing `publicDir` to read the source file from a non-default public directory (defaults to `getcwd()/public`).
  - `cssFromManifest()` resolves URLs from a manifest.
- Global helpers: `phpwind_css()` and `phpwind_manifest_css()` (both forward an optional `publicDir`).

## Development Middleware

- `PHPWind\Middleware\OnDemandCompilerMiddleware` recompiles CSS during dev HTTP requests.
- Change detection is on by default: it recompiles only when the input CSS file changes, skipping otherwise (use `checkForChanges=false` to always compile).
- `PHPWind\ChangeDetection\FileChangeDetector` fingerprints `inputCss` via `mtime|size` and persists the last fingerprint to `.phpwind/.cache/middleware.json`. When the state directory is unwritable it degrades to always-compile without throwing.

## Exception Hierarchy

All domain exceptions extend `PHPWind\Exception\PHPWindException`:

- `InvalidConfigurationException`
- `BinaryDownloadException`
- `BinaryExecutionException`
- `AssetManifestException`

## Backward Compatibility

- `PHPWindConfig` constructor and `fromArray()` are unchanged.
- `TailwindCompiler::compile()` still returns `int`.
- `AssetHelper::css()` still returns query-string tags by default.
- `phpwind_css()` signature is backward compatible (an optional trailing `publicDir` was added).
- `Runner::run()` still returns `int`.
- `OnDemandCompilerMiddleware` constructor keeps its original parameters; `checkForChanges` and `detector` are new trailing optional parameters.
