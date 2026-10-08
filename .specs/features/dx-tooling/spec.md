# Feature: DX Tooling

## Goal

Make the existing lightweight PHP wrapper easier to diagnose, configure, use consistently across entry points, and install in real projects/CI without adding runtime dependencies or Node.js.

## Status

Validated — independently reviewed against all acceptance criteria.

## Requirements

1. Add `vendor/bin/phpwind doctor`, a read-only offline diagnostic reporting PHP/runtime requirements, supported platform/architecture, cURL/proc_open availability, configured Tailwind version and paths, and input/output/cache status. It must not download or execute a Tailwind binary. Exit 0 when required local prerequisites pass; nonzero when a required check fails; print actionable labels and a summary.
2. Standardize human-readable build success/failure messages and preserve meaningful nonzero process exit codes across standalone CLI, Laravel Artisan, and Symfony Console. Include captured compiler output on failures.
3. Add backward-compatible config settings for download timeout (positive integer, default 120 seconds) and SSL verification (default true), round-tripped by `PHPWindConfig`, supported in shipped config/environment and consumed by the default compiler downloader.
4. Expand English, Portuguese, and Spanish docs with copyable setup examples for vanilla PHP and one framework path, plus a CI example that installs dependencies/builds CSS and fails on build errors.

## Scope and assumptions

- `doctor` is local-only/read-only and performs no file writes, downloads, or network probes.
- Config keys: `download_timeout` / `PHPWIND_DOWNLOAD_TIMEOUT` and `verify_ssl` / `PHPWIND_VERIFY_SSL`; invalid timeout values fail configuration validation.
- Existing constructor argument order, `compile(): int`, and command exit-code behavior remain compatible.
- No Node.js dependency, new runtime package, CSS engine, or broad framework features.

## Acceptance criteria

- Subprocess integration tests invoke `bin/phpwind doctor` in isolated valid/invalid projects and cover exit status, diagnostic labels, and no filesystem side effects; code review confirms the doctor path has no downloader/runner calls.
- Config tests prove defaults, validation, array round-trip, shipped environment config; compiler wiring passes the exact config settings to its default downloader.
- Tests verify shared formatter success/failure output and CLI/Symfony exit-code behavior; each framework/CLI adapter delegates to that formatter and returns the compiler's original code (Laravel adapter contract is inspected because Illuminate is not a project dev dependency).
- Each language doc includes a PHPWind setup/build flow and CI YAML/build command with exit status preserved.
- Full `vendor/bin/phpunit tests` and `guarana specs validate <project-root> --json` pass.

## Verification proof

Implementation proof: offline `doctor` diagnostics report `not installed`, `present/valid`, and `present/invalid` cache states without downloading or executing a binary; shared formatting is used by CLI, Laravel, and Symfony build adapters; `download_timeout` and `verify_ssl` retain defaults and feed the default downloader; all three localized guides remain present.

Verification: `php -l bin/phpwind` and `php -l src/Command/DoctorHandler.php` passed; `vendor/bin/phpunit tests/DoctorCliIntegrationTest.php` passed (2 tests, 17 assertions); `vendor/bin/phpunit tests` passed (102 tests, 199 assertions, 1 skipped); `guarana specs validate /home/arch/codes/PHPWind --json` returned `ok: true` with no errors (three feature-title warnings); `git diff --check` passed. Independent verifier confirmed `supportedPlatform()` uses only local `getBinaryName()` mapping and all documented requirements.
