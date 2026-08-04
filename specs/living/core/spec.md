> Merged living specification for the **Core** domain of PHPWind.

## Overview

PHPWind is a zero-Node PHP integration for Tailwind CSS v3/v4. The core domain manages configuration, binary download/cache, compilation orchestration, and asset cache busting.

## Configuration

- `PHPWind\Config\PHPWindConfig` is the central DTO.
- Constructed via constructor or `fromArray()` with snake_case keys.
- `validate()` enforces:
  - Non-empty `inputCss`, `outputCss`, `binaryDir`.
  - `version` matches a semantic version prefix (`v4.0.0`, `3.4.17`, etc.).

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
- `PHPWind\Binary\Runner` executes the binary with `-i`, `-o`, `--minify`, `--watch` flags.

## Asset Manifest

- `PHPWind\Manifest\AssetEntry` stores `path` and `hash` for a single asset.
- `PHPWind\Manifest\AssetManifest` provides:
  - `generate()` from files on disk.
  - `read()` / `write()` JSON persistence.
  - `get()` / `set()` lookup and mutation.
- `PHPWind\Helper\AssetHelper` generates `<link>` tags:
  - `css()` uses query-string cache busting (`?v=<md5>`) by default.
  - `cssFromManifest()` resolves URLs from a manifest.
- Global helpers: `phpwind_css()` and `phpwind_manifest_css()`.

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
- `phpwind_css()` signature is unchanged.
