# Specification: PHPWind (Tailwind CSS v4 for PHP)

## 1. Overview
PHPWind is a zero-dependency (Node-free) PHP library that wraps the Tailwind CSS v4 standalone CLI. It automatically downloads the appropriate binary for the host OS/architecture and provides PHP helpers, CLI commands, and dev middleware for instant Tailwind v4 integration.

## 2. Target Requirements & Compatibility
- PHP >= 8.1
- Standalone Tailwind CSS v4 CLI binary execution
- Supported OS: Windows (x64), Linux (x64, arm64), macOS (x64, arm64)
- Framework Agnostic core with optional Laravel / Symfony integrations

## 3. Architecture & Directory Structure

```
PHPWind/
├── bin/
│   └── phpwind
├── src/
│   ├── Binary/
│   │   ├── Downloader.php
│   │   ├── PlatformResolver.php
│   │   └── Runner.php
│   ├── Compiler/
│   │   └── TailwindCompiler.php
│   ├── Config/
│   │   └── PHPWindConfig.php
│   ├── Helper/
│   │   └── AssetHelper.php
│   ├── Middleware/
│   │   └── OnDemandCompilerMiddleware.php
│   └── Laravel/
│       ├── PHPWindServiceProvider.php
│       └── Commands/
│           ├── BuildCommand.php
│           └── WatchCommand.php
├── composer.json
└── README.md
```

## 4. Component Details

### 4.1 PlatformResolver & Downloader
- `PlatformResolver`: Detects OS (`PHP_OS_FAMILY`) and Architecture (`php_uname('m')`) to build the GitHub Release download URL for Tailwind v4 CLI (e.g., `tailwind-windows-x64.exe`, `tailwind-linux-x64`, `tailwind-macos-arm64`).
- `Downloader`: Downloads the binary into `vendor/bin/tailwind-cli/` or a configurable cache directory, granting execution permissions (`chmod +x` on Unix).

### 4.2 TailwindCompiler & Runner
- `Runner`: Shell wrapper executing the downloaded binary with arguments (`--input`, `--output`, `--minify`, `--watch`).
- `TailwindCompiler`: Manages compilation tasks, triggering `Downloader` if binary is missing.

### 4.3 AssetHelper & OnDemandCompilerMiddleware
- `AssetHelper`: Returns HTML stylesheet link tags with file hash for cache busting.
- `OnDemandCompilerMiddleware`: Triggers recompilation during HTTP requests when in development mode if input files change.

### 4.4 Framework Integration
- `Laravel`: `PHPWindServiceProvider` registers Artisan commands `phpwind:build` and `phpwind:watch`, plus Blade directive `@phpwind`.

## 5. Verification Plan
- Unit tests for `PlatformResolver` and `PHPWindConfig`.
- Integration tests for binary download and compilation execution.
- Verification of generated CSS output and asset tag generation.
