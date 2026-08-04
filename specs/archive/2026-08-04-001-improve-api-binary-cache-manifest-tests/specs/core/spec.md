> Spec delta for the **Core** domain of PHPWind.

## Current State (before change)

- `PHPWindConfig` is a plain DTO with no validation.
- `Downloader` downloads the Tailwind CLI to a generic filename (`tailwind` / `tailwind.exe`). Version changes are silently ignored if the file already exists.
- `TailwindCompiler::compile()` returns only an `int` exit code.
- `AssetHelper` generates query-string cache busting (`?v=<md5>`) but no persistent manifest.
- No domain-specific exceptions; failures use generic `RuntimeException`.
- Unit tests exist for `PlatformResolver`, `PHPWindConfig`, command handlers, and Symfony bundle only.

## Desired State (after change)

### ADDED

- Domain exception hierarchy under `src/Exception/`.
- `PHPWindConfig::validate()` for explicit, catchable configuration failures.
- `PlatformResolver::getVersionedBinaryName()` for per-version binary filenames.
- `BinaryManager` to own binary cache lifecycle and version invalidation.
- Configurable timeout and SSL verification in `Downloader`.
- `CompilationResult` value object and `TailwindCompiler::compileResult()`.
- `AssetEntry` and `AssetManifest` value objects with JSON persistence.
- `AssetHelper::cssFromManifest()` for manifest-based asset URLs.
- Unit tests for `Downloader`, `Runner`, `TailwindCompiler`, `BinaryManager`, `AssetHelper`, `AssetManifest`, and extended `PHPWindConfig` tests.

### MODIFIED

- `src/Binary/Downloader.php` — refactored for testability and safer defaults.
- `src/Binary/Runner.php` — returns structured result; throws domain exception on `proc_open` failure.
- `src/Compiler/TailwindCompiler.php` — injects `BinaryManager` and `Runner`; adds `compileResult()`.
- `src/Config/PHPWindConfig.php` — adds `validate()`.
- `src/Helper/AssetHelper.php` — adds manifest support without changing `css()`.
- `src/Command/CleanHandler.php` — uses `BinaryManager::clearCachedBinary()`.
- `src/Command/InitHandler.php` — calls `$config->validate()`.

### REMOVED

- Nothing. Backward compatibility is preserved.

## Compatibility Notes

- `TailwindCompiler::compile()` keeps returning `int`.
- `AssetHelper::css()` keeps returning query-string tags by default.
- `PHPWindConfig` constructor and `fromArray()` signatures are unchanged.
- `phpwind_css()` global helper signature is unchanged.
