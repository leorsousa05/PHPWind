# PHPWind — Spec Tracker (System of Record)

Master tracker for PHPWind engineering. Disk is truth (ADR-004): never reconstruct state from memory.

## Conventions
- `DONE` — merged into the living feature spec and verified.
- `NEXT` — the single next unit of work (only one at a time).
- `BLOCKED` — started but stalled; list the blocker in `state/project-state.md`.

## Master tracker

**DONE:** Completed and verified work.
**NEXT:** The single next unit of work.
**BLOCKED:** Started work stalled by a listed blocker.

| Skill | Status | Item | Spec | Flags |
|-------|--------|------|------|-------|
| core | DONE | Core domain (configuration, binary download/cache, compilation, asset manifest, dev middleware) | `features/core/spec.md` | — |
| change-001 | DONE | Change 001 — Improve API, Binary Cache, Asset Manifest, Test Coverage | archived legacy `002` in git: `specs/archive/` | — |
| change-002 | DONE | Change 002 — Core DX & Dev Middleware Improvements | migrated into `features/core/spec.md` | — |
| wrapper-reliability | VALIDATED | Reliability improvements for binary, configuration, and compilation flows | `features/wrapper-reliability/spec.md` | — |
| dx-tooling | VALIDATED | Developer diagnostics, consistent command output, downloader settings, and usage examples | `features/dx-tooling/spec.md` | — |
| none | NEXT | *(none — awaiting next task)* | — | — |
| none | BLOCKED | none | — | — |

## Sources

- Legacy `specs/` tree recovered from git HEAD (deleted from worktree) as the initial Core feature context.
- ADRs: `.specs/decisions/ADR-*.md` (append-only).
