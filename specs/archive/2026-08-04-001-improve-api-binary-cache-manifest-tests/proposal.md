# Proposal: Improve API, Binary Cache, Asset Manifest, and Test Coverage

## 1. Motivation

PHPWind currently ships a working but minimal core. Several gaps make it harder to use as a library and risk silent failures in production:

1. **No version-aware binary cache** — `Downloader` saves the Tailwind CLI as a generic `tailwind` / `tailwind.exe`. Changing `version` in `PHPWindConfig` does not trigger a re-download; the old binary is reused silently.
2. **Weak programmatic API** — `TailwindCompiler::compile()` returns only an exit code. Configuration values are not validated, and failures are communicated through generic `RuntimeException` or integer codes.
3. **Basic asset cache busting** — `AssetHelper` only appends an MD5 query string. There is no persistent manifest mapping logical asset names to versioned URLs, which complicates CDN/integration scenarios.
4. **Low test coverage** — `Downloader`, `Runner`, `TailwindCompiler`, and `AssetHelper` have no unit tests. The existing suite covers only `PlatformResolver`, `PHPWindConfig`, command handlers, and the Symfony bundle.

This change closes those gaps while preserving backward compatibility for public API consumers.

## 2. Scope

### In Scope
- Introduce a small domain-specific exception hierarchy rooted at `PHPWindException`.
- Validate `PHPWindConfig` values (non-empty paths, valid version prefix) without changing its public constructor or `fromArray()` signature.
- Add a `BinaryManager` that tracks the downloaded binary version and re-downloads when the requested version changes.
- Harden `Downloader` with configurable timeouts and safer cURL defaults, while keeping an escape hatch for self-signed/offline environments.
- Introduce an `AssetManifest` value object and generator. `AssetHelper` gains optional manifest-based resolution with fallback to the current query-string behavior.
- Expand unit tests to cover `Downloader`, `Runner`, `TailwindCompiler`, `AssetHelper`, `AssetManifest`, and `BinaryManager`.

### Out of Scope
- Adding new CLI commands or framework integrations.
- Rewriting the Laravel/Symfony adapters beyond the minimal changes required to keep them working.
- Full integration tests against real GitHub downloads (unit tests will mock network/process boundaries).
- Changing the default public/binary directories or asset URL scheme.

## 3. Constraints

- **Backward compatibility:** Existing public signatures (`PHPWindConfig`, `TailwindCompiler::compile()`, `AssetHelper::css()`, `phpwind_css()`, `InitHandler::handle()`, `CleanHandler::handle()`) must continue to work unchanged for callers that use them today.
- **PHP 8.1+:** All code must remain compatible with PHP 8.1, 8.2, 8.3, and 8.4.
- **No new runtime dependencies:** Only PHP extensions already required by the project (`curl`, `mbstring`, `dom`, `fileinfo`) may be used.
- **Cross-platform:** Windows, Linux, and macOS paths/permissions must remain correct.

## 4. Success Criteria

1. Switching `PHPWindConfig::$version` from `v3.4.17` to `v4.0.0` (or vice versa) triggers a new download and executes the correct binary.
2. `AssetHelper::css()` can resolve URLs from a generated `AssetManifest` and still falls back to `?v=<hash>` when no manifest is present.
3. `TailwindCompiler::compile()` returns a structured `CompilationResult` with `exitCode`, `outputPath`, and `durationMs`, while the previous `int` return behavior is preserved through a compatibility path.
4. Unit tests exist for every public class under `src/Binary`, `src/Compiler`, `src/Helper`, and `src/Manifest`.
5. Existing tests continue to pass without modification.
