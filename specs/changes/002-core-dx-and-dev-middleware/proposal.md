# Proposal: Core DX & Dev Middleware Improvements

> Metadata (status, dates, author, affected files) lives in `.spec.yaml`.

## Problem Statement

PHPWind's core works but has several friction points that surface in real projects and contradict its own docs/claims:

1. **Dev middleware recompiles on every request.** `OnDemandCompilerMiddleware` runs `compile()` unconditionally when `isDev`, even if the input CSS is unchanged. The README promises *"Recompiles CSS dynamically during incoming HTTP requests"* — but there is no change detection, so a dev server pays a process spawn + binary run cost on every page load.
2. **The shipped config file depends on Laravel-only helpers.** `config/phpwind.php` calls `resource_path()`, `public_path()`, `base_path()`, and `env()`. These are undefined in vanilla PHP and Symfony, and since the file is a plain PHP array, a user who `require`s it outside Laravel gets a fatal error. There is no framework-agnostic way to load config into `PHPWindConfig`.
3. **Build failures are opaque.** `Runner` discards child stdout/stderr, and `CompilationResult` only exposes `exitCode`, `outputPath`, `durationMs`. When the Tailwind CLI fails, the user has no captured output to diagnose why.
4. **Asset public dir is hardcoded.** `AssetHelper::css()` assumes `getcwd()/public/` for the source file used by cache busting. Non-standard layouts (e.g. `web/`, `dist/`) cannot be served without a custom public directory.
5. **Dead code.** `PlatformResolver::getBinaryName()` duplicates the v3/v4 branches — they are byte-for-byte identical per OS. This is misleading and invites divergent edits.

## Goals

1. Recompile in dev only when the input file actually changes (mtime/size fingerprint), eliminating wasted compilation on unchanged requests.
2. Provide a framework-agnostic config loading path (`ConfigLoader` + `Env` helper) so `config/phpwind.php` works in vanilla/Symfony/Laravel alike.
3. Capture and surface child process stdout/stderr through `ProcessResult` and `CompilationResult`.
4. Make the asset public directory configurable in `AssetHelper::css()` and the `phpwind_css()` helper.
5. Remove the duplicated v3/v4 branches in `PlatformResolver::getBinaryName()`.

## Non-Goals

- Adding new CLI commands or new framework integrations.
- Rewiring the Symfony `PHPWindBundle` to auto-register the config loader (out of scope; the loader is available to callers).
- Full integration tests against real GitHub downloads or a live binary (unit tests mock network/process boundaries).
- Changing the default URL scheme or default public/binary directories.
- Extending change detection to watch arbitrary content-scan directories; it tracks the input CSS file only.

## Constraints

- **Backward compatibility:** All existing public signatures must keep working unchanged for current callers:
  `PHPWindConfig::__construct`/`fromArray`, `TailwindCompiler::compile(): int`, `Runner::run(): int`, `AssetHelper::css($path, $versioned)`, `phpwind_css($path, $versioned)`, `PHPWindServiceProvider` boot behavior, and the Twig `phpwind_css` function.
- **PHP 8.1+:** compatible with 8.1–8.4.
- **No new runtime dependencies.**
- **Cross-platform:** Windows, Linux, macOS path/permission handling must remain correct (watch mode must still stream to parent stdio).

## Risks

| Risk | Impact | Mitigation |
|------|--------|------------|
| Captured-output refactor breaks streaming in `--watch` mode | High | Only capture pipes when `$config->watch === false`; watch mode keeps direct STDIN/STDOUT/STDERR descriptors. |
| Change detection misses changes (same size + no mtime bump) | Low | Use `mtime|size` fingerprint; mtime updates on write in practice. Document the limitation. |
| Config file rewrite regresses Laravel absolute paths | Medium | Laravel `ServiceProvider` post-resolves relative paths with `resource_path`/`public_path`/`base_path`; default file stays valid for all frameworks. |
| Adding optional param to `AssetHelper::css()` breaks positional callers | Low | New param is trailing and optional; existing calls unaffected. |

## Success Criteria

- [ ] Dev middleware compiles only when the input file changes (unchanged request → no compile). → verified by T1
- [ ] `ConfigLoader::load()` returns a valid `PHPWindConfig` from the shipped config file in a non-Laravel process. → verified by T2
- [ ] `Runner::runResult()` and `CompilationResult` expose captured stdout/stderr; `Runner::run()` and `TailwindCompiler::compile()` remain int/BC. → verified by T3
- [ ] `AssetHelper::css()` accepts a configurable public dir while preserving default behavior. → verified by T4
- [ ] `PlatformResolver::getBinaryName()` has no duplicated branches and existing tests still pass. → verified by T5
- [ ] Full suite passes with no regressions. → verified by T6
