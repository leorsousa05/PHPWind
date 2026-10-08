# Feature: Wrapper Reliability Improvements

## Goal

Improve reliability of PHPWind's existing wrapper around the official standalone Tailwind CLI without adding a CSS engine, plugin system, Node.js runtime dependency, or unrelated framework features.

## Status

Validated — independently reviewed against all acceptance criteria.

## Requirements

1. Validate a cached/downloaded CLI binary before returning it, including non-empty/executable checks where supported. The versioned cache filename binds a binary to the requested version; do not invoke the CLI for a separate version check. Invalid cached files must be recoverable by redownloading rather than silently reused.
2. Serialize resolution/download for the same cached binary so concurrent PHP processes cannot corrupt or race replacement of the file. Preserve atomic temporary-file installation.
3. Make compilation failures actionable through structured result/exception and CLI/framework output, retaining exit code and available stdout/stderr without breaking `compile(): int` compatibility.
4. Tighten semantic-version validation and fail early on invalid configuration values that can be established safely (including required source input existence at compilation time); retain valid existing configuration and constructor behavior.
5. Explicitly resolve only supported OS/architecture combinations (Windows, Linux, macOS; x64 and ARM64 where upstream assets exist) and produce a clear unsupported-platform error rather than silently selecting an unrelated binary.

## Scope boundaries and assumptions

- PHPWind remains a lightweight PHP wrapper/installer/runner for the official Tailwind standalone CLI.
- No Node.js/npm requirement, CSS compilation implementation, plugin manager, or new runtime dependency.
- Preserve existing public method signatures and return behavior where possible; additive structured diagnostics are acceptable.
- Use deterministic unit tests with injected/fake downloaders and platform facts; tests must not require network access or real Tailwind binaries.
- DX follow-up items moved to `features/dx-tooling/spec.md`: `phpwind doctor`, consistent human-readable CLI/Artisan/Symfony messaging and exit codes, configurable download timeout/SSL through primary config, and expanded real-installation/CI examples.

## Acceptance criteria

- Tests demonstrate invalid/empty cached binary recovery and safe concurrent resolution behavior with two actual synchronized subprocesses resolving the same cache entry.
- Tests demonstrate useful compilation failure diagnostics while `compile()` and `Runner::run()` retain integer exit-code behavior.
- Tests reject malformed semantic versions and invalid required compilation input before process execution.
- Platform tests cover supported mappings and clear rejection of unsupported OS and architecture; SemVer tests reject numeric prerelease identifiers with leading zeros.
- Full `vendor/bin/phpunit tests` passes without network access.
- The deferred DX backlog is present in this spec; the diff for this change contains reliability work only and does not add the deferred doctor command, primary-config download settings, or installation examples.

## Verification proof

Passed:

- `guarana specs validate /home/arch/codes/PHPWind --json` — `ok: true`, no errors (two non-blocking feature-title warnings).
- `rtk vendor/bin/phpunit tests` — 91 tests, 153 assertions, 1 skipped; no network required.
- `rtk git diff --check` — clean.
- Independent `worker-verify` confirmed all behavior criteria and the deferred-DX scope condition. Evidence: `tests/BinaryManagerTest.php`, `tests/PlatformResolverTest.php`, `tests/TailwindCompilerTest.php`, `src/Binary/BinaryManager.php`, `src/Binary/PlatformResolver.php`, `src/Compiler/TailwindCompiler.php`, and framework build commands.
