# Tasks: Core DX & Dev Middleware Improvements

> Granularity rules (do not delete this block):
> - One task = one cohesive set of files (max ~3 unrelated files).
> - Every task MUST list **Files**, **Depends on**, **Verification**, and **Done when**.
> - Order tasks by dependency; each step must end in a verifiable state.

Verification baseline: `composer install` once, then `vendor/bin/phpunit tests`.

## Phase 1: Framework-Agnostic Config

- [x] **Task 1: Add `Env` helper**
  - **Files:** `src/Config/Env.php`, `tests/EnvTest.php`
  - **Depends on:** None
  - **Verification:** `vendor/bin/phpunit tests/EnvTest.php`
  - **Done when:** `Env::get()` resolves getenv → `$_ENV` → `$_SERVER` → default; tests cover precedence and missing key.

- [x] **Task 2: Add `ConfigLoader` + `PHPWindConfig::toArray()`**
  - **Files:** `src/Config/ConfigLoader.php`, `src/Config/PHPWindConfig.php`, `tests/ConfigLoaderTest.php`
  - **Depends on:** Task 1
  - **Verification:** `vendor/bin/phpunit tests/ConfigLoaderTest.php tests/PHPWindConfigTest.php`
  - **Done when:** `fromArray()` and `load()` return a validated `PHPWindConfig`; invalid config throws `InvalidConfigurationException`; `toArray()` round-trips with `fromArray()`; existing config test passes.

- [x] **Task 3: Make `config/phpwind.php` framework-agnostic**
  - **Files:** `config/phpwind.php`, `src/Laravel/PHPWindServiceProvider.php`
  - **Depends on:** Tasks 1, 2
  - **Verification:** `vendor/bin/phpunit tests/CommandHandlerTest.php tests/SymfonyTest.php`
  - **Done when:** Config file uses relative paths + `Env::get` (no `resource_path`/`env()`); Laravel provider post-resolves to `resource_path`/`public_path`/`base_path`; existing tests pass.

## Phase 2: Captured Process Output

- [x] **Task 4: Add `ProcessResult` + `Runner::runResult()`**
  - **Files:** `src/Binary/ProcessResult.php`, `src/Binary/Runner.php`, `tests/RunnerTest.php`
  - **Depends on:** None
  - **Verification:** `vendor/bin/phpunit tests/RunnerTest.php`
  - **Done when:** `runResult()` returns `ProcessResult` with captured stdout/stderr (non-watch); `run()` still returns `int` (BC); watch mode streams to parent stdio; null-byte path still throws `BinaryExecutionException`.

- [x] **Task 5: Extend `CompilationResult` + `TailwindCompiler`**
  - **Files:** `src/Compiler/CompilationResult.php`, `src/Compiler/TailwindCompiler.php`, `tests/TailwindCompilerTest.php`
  - **Depends on:** Task 4
  - **Verification:** `vendor/bin/phpunit tests/TailwindCompilerTest.php`
  - **Done when:** `CompilationResult` exposes `stdout`/`stderr` (default `''`); `compileResult()` populates them via `runResult()`; `compile(): int` unchanged.

## Phase 3: Change-Aware Dev Middleware

- [x] **Task 6: Add `FileChangeDetector`**
  - **Files:** `src/ChangeDetection/FileChangeDetector.php`, `tests/FileChangeDetectorTest.php`
  - **Depends on:** None
  - **Verification:** `vendor/bin/phpunit tests/FileChangeDetectorTest.php`
  - **Done when:** `hasChanged()` true on first run / after change, false when unchanged; `record()` persists cross-"request" via state file; unwritable state dir degrades without throwing.

- [x] **Task 7: Wire change detection into middleware**
  - **Files:** `src/Middleware/OnDemandCompilerMiddleware.php`, `tests/OnDemandCompilerMiddlewareTest.php`
  - **Depends on:** Task 6
  - **Verification:** `vendor/bin/phpunit tests/OnDemandCompilerMiddlewareTest.php`
  - **Done when:** With `checkForChanges=true`, unchanged request skips compile; changed request compiles; `checkForChanges=false` always compiles.

## Phase 4: Configurable Asset Public Dir

- [x] **Task 8: Add `publicDir` to `AssetHelper::css()` + helper + Twig**
  - **Files:** `src/Helper/AssetHelper.php`, `src/Helper/functions.php`, `src/Symfony/Twig/PHPWindTwigExtension.php`, `tests/AssetHelperTest.php`
  - **Depends on:** None
  - **Verification:** `vendor/bin/phpunit tests/AssetHelperTest.php tests/SymfonyTest.php`
  - **Done when:** `css()` accepts trailing optional `publicDir` (default `getcwd()/public`); `phpwind_css()` and Twig `renderCss()` forward it; existing asset/Symfony tests pass.

## Phase 5: Platform Cleanup & Regression

- [x] **Task 9: Deduplicate `PlatformResolver::getBinaryName()` branches**
  - **Files:** `src/Binary/PlatformResolver.php`, `tests/PlatformResolverTest.php`
  - **Depends on:** None
  - **Verification:** `vendor/bin/phpunit tests/PlatformResolverTest.php`
  - **Done when:** No duplicate v3/v4 branches remain; all existing platform tests pass unchanged.

- [x] **Task 10: Full suite + living spec**
  - **Files:** `tests/`, `specs/living/core/spec.md`
  - **Depends on:** Tasks 1–9
  - **Verification:** `vendor/bin/phpunit tests`
  - **Done when:** Complete suite passes with 0 failures/errors on PHP 8.1+; living spec reflects change-aware middleware, config loader, captured output, and configurable public dir.
