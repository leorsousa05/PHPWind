# ADR-002: Conventions for routed work

## Status
Accepted (human-confirmed, "standard suite as shipped")

## Decision
- Match existing code style and structure.
- Preserve public API backward compatibility (see Core spec §Backward Compatibility).
- No new runtime dependencies.
- Keep framework-agnostic core + thin adapters (Laravel/Symfony) architecture.
- No code comments unless asked.

## Consequences
Routed changes must not break existing public signatures.