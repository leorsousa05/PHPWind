# ADR-003: Verifier & acceptance gate

Status: Accepted (human-confirmed)

## Decision
- Verifier: `composer install` once, then `vendor/bin/phpunit tests`.
- Acceptance gate: full suite passes with 0 failures/errors on PHP 8.1+.
- Each routed unit of work ends in a verifiable state (task-level verification via targeted phpunit file, suite-level via `vendor/bin/phpunit tests`).

## Consequences
Nothing is DONE until the verifier passes.