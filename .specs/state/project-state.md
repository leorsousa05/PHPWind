# Project State — PHPWind

## Current Step
No work in flight. `dx-tooling` is implemented and independently validated.

## Goal
- Deliver a lightweight offline `doctor` command, consistent build outcomes, configurable download timeout/SSL, and actionable installation/CI examples within PHPWind's wrapper scope.

## Checkpoint
- Goal: Harden PHPWind's lightweight Tailwind CLI wrapper while preserving scope and backward compatibility; record follow-up DX proposals.
- Migrated legacy `specs/` tree (from git HEAD) into `.specs/features/core/spec.md`.
- `.specs/README.md` and `.specs/state/project-state.md` scaffolded.
- `.specs/decisions/` created empty; ADRs to be generated only from this project's Rule 0 answers (ADR-006).
- Outcome: five wrapper reliability improvements and all four deferred DX proposals implemented.
- Verification: independent verifier confirmed DX acceptance; `vendor/bin/phpunit tests` → 102 tests, 199 assertions, 1 skipped; `guarana specs validate /home/arch/codes/PHPWind --json` → `ok: true`, no errors (three feature-title warnings); PHP lint passed for CLI and doctor; `git diff --check` passed.
- Assumption: `doctor` performs read-only local checks, never downloads binaries or makes network requests; this keeps diagnostics safe to run in CI/offline environments.
- Pending writes: none.

## Budgets (ADR-005)
- code: 8k tokens per dispatch
- verify: 4k tokens per dispatch
- debug: 6k tokens per dispatch

## Telemetry
- `.specs/state/telemetry/events.jsonl` — session events.

## BLOCKED Items
None.
