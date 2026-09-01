# PHPWind — Spec Tracker (System of Record)

Master tracker for PHPWind engineering. Disk is truth (ADR-004): never reconstruct state from memory.

## Conventions
- `DONE` — merged into the living feature spec and verified.
- `NEXT` — the single next unit of work (only one at a time).
- `BLOCKED` — started but stalled; list the blocker in `state/project-state.md`.

## Tracker

| Status | Item | Spec |
|--------|------|------|
| DONE | Core domain (configuration, binary download/cache, compilation, asset manifest, dev middleware) | `features/core/spec.md` |
| DONE | Change 001 — Improve API, Binary Cache, Asset Manifest, Test Coverage | archived legacy `002` in git: `specs/archive/` |
| DONE | Change 002 — Core DX & Dev Middleware Improvements | migrated into `features/core/spec.md` |
| NEXT | *(none — awaiting Rule 0 contract)* | — |
| BLOCKED | none | — |

## Sources

- Legacy `specs/` tree recovered from git HEAD (deleted from worktree) as the initial Core feature context.
- ADRs: `.specs/decisions/ADR-*.md` (append-only).