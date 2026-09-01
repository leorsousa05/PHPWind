# ADR-001: Project identity & package

## Status
Accepted (migrated from composer.json)

## Context
PHPWind is a composer library. Contract answers for routing.

## Decision
- Package: `phpwind/phpwind`, MIT, v1.6.0.
- Runtime: PHP >= 8.1 (compatible 8.1–8.4).
- Autoload: PSR-4 `PHPWind\` → `src/`; tests `PHPWind\Tests\` → `tests/`.
- Zero runtime dependencies; framework integrations are optional (Laravel, Symfony).

## Consequences
All routed work must stay PHP 8.1+ and add no new runtime dependencies.