# Laravel Development Standard — VisiCore

> **Type:** Implementation standard (development-facing)
> **Status:** NOT_STARTED → USE WHEN BUILDING · **Compiled:** 2026-08-18
> Combines the WWDF standards (`00-WWDF/`, frozen v1.0) with the sound source
> docs (`docs/09-Development/`). Source remains authoritative.

---

## 1. Stack & Skeleton (already present)

- **Runtime:** PHP ^8.3 (installed PHP 8.4.1), Composer 2.x.
- **Framework:** Laravel 13 (`laravel/framework ^13.17`).
- **Frontend:** Vite 8 + Tailwind 4 (`@tailwindcss/vite`), Alpine 3,
  `@fontsource-variable/inter`, Blade (no SPA framework).
- **Tests:** PHPUnit `^12.5.12` (in-memory SQLite — `phpunit.xml`); Pint `^1.27`.
- **Dev DB:** MySQL (`DB_CONNECTION=mysql`, db `laravel_visicore_app` in `.env`).
- **Commands always run from `laravel-visicore-app/`** (never the repo root).

## 2. Architecture (authoritative layering)

Reference: `docs/09-Development/040-laravel-architecture-guide.md`,
`00-WWDF/04-Architecture/10-layered-architecture.md`.

```text
Http (Controllers/Requests)   -> thin: validate → call service → return response
   |
Services (business use cases) -> orchestration; emit Application Events;
                                 NEVER call external systems directly
   |
Domain (entities, rules)      -> stable core; enums hold statuses
   |
Repositories / adapters       -> persistence (Eloquent) + integrations (drivers)
```

**Hard rules**
- Controllers are **thin** (validate, call service, return).
- Services **own use cases**; they do not query the DB directly when a
  repository slot exists, and never call external systems directly (adapters do).
- **Repositories/DTOs only when justified** (WWDF rule; don't bolt on ceremony).
- **Adapters/drivers** isolate JotpotSMS, email, storage, payments (`07-driver-adapter-pattern.md`).
- **Multi-tenancy enforced at the data layer** (BDR-021; `12-multi-tenancy-standard.md`).
- **History immutable** — no hard deletes of business history (BDR-005).
- **Boundaries:** Http must not touch Domain or Integrations directly; Services
  depend on Domain + interfaces.

## 3. Project Structure (Laravel skeleton)

Follow the WWDF reference (`00-WWDF/11-Reference-Implementations/13-laravel-project-structure.md`):
organize by **layer + domain**, not by type.

```text
app/
  Http/Controllers/{Module}/    # thin controllers (MOD-XXX mapping)
  Http/Requests/{Module}/       # Form Requests (validation)
  Http/Middleware/              # auth, tenant scope, rate limit
  Policies/                     # authorization policies
  {Domain}/                     # per module: e.g. Visitors/, Timeline/, Communication/
    Services/
    Events/                     # application/domain events
    Listeners/
    DTOs/                       # only when justified
    Repositories/               # only when justified
    Enums/
  Models/
config/{module}.php
database/migrations/            # versioned, reversible
database/factories/
database/seeders/               # roles, system tags, lookups
tests/                          # Unit, Feature, Pest/PHPUnit
```

Module name mapping: MOD-001 → `Visitors`, MOD-002 → `Timeline`, MOD-003 →
`Relationships`, MOD-004 → `Communication`, MOD-005 → `Knowledge`, MOD-006 →
`Visits`, MOD-007 → `Purchases`, MOD-008 → `Investment`, MOD-009 → `Offerings`,
MOD-010 → `Reports`, MOD-011 → `Subscriptions`, MOD-012 → `Admin`, MOD-013 →
`Settings`.

## 4. Module Building Blocks (per module, see each MOD-XXX doc)

Each module record defines: controllers, form requests, resources, services,
models, policies, middleware, events, routes, views/components, tests (§K/§Q).

## 5. Persistence & Data Model

- Tables per `docs/04-Architecture/persistence/080-table-catalog.md` +
  `070-entity-to-table-mapping.md`. **Do not redesign schemas.**
- Identifiers per `060-identifier-strategy.md`: VIN `VC-YYYY-NNNNNN`; records
  `PREFIX-NNNNNN`.
- **Soft delete / archiving:** business history never hard-deleted (BDR-005);
  archived markers only where documented.
- Multi-tenancy: tenant column on every tenant-owned table; isolation enforced
  (BDR-021).
- Migrations: versioned, reversible; factories for tests; seeders for roles/
  system tags.

## 6. Events & Queues

- Every change of note emits an Application Event (WWDF `08-event-driven-architecture.md`),
  projected into the Timeline as Timeline Events (BDR-012/014/016).
- Only the **producing service** projects history (MOD-002 consumes; never
  writes business data).
- Queues reuse the same services (WWDF `14-queue-architecture.md`;
  `120-event-guidelines.md`, `130-queue-guidelines.md`).

## 7. API Contract

- Envelope `{ "data": ..., "meta": ... }`; `snake_case`; ISO-8601 UTC;
  `/api/v1` URI versioning (`docs/07-API/`).
- Errors: 401 / 403 / 404 / 409 / 422 / 429 / 5xx per `060-error-handling.md`.
- Pagination/filtering per `070-pagination-filtering.md`; idempotency
  `080-idempotency.md`; rate limiting `090-rate-limiting.md`.

## 8. Validation & Exceptions

- Form Requests own input validation (`00-WWDF/05-Development-Standards/06-validation-standards.md`, `docs/09-Development/090-validation-guidelines.md`).
- Business rules live in Services/enums; report via documented statuses
  (`100-exception-handling.md`).
- Logging per `110-logging-guidelines.md`.

## 9. Authorization

- Policies (`app/Policies/`) per module; V1 roles SA + CO (BDR-020) with
  visibility scope: SA global / CO company; future MG (team), SE (own +
  transferred-read).
- Permissions per `docs/04-Architecture/access-control/040-permission-matrix.md`;
  the full matrix is an Open Question (MOD-012) — V1 stays to BDR-020.
- Multi-tenancy middleware + data-layer enforcement.

## 10. Code Style & Quality Gates

- **Pint** (`./vendor/bin/pint`), 4-space indentation (`.editorconfig`).
- **Tests:** `composer test` (in-memory SQLite); focus with
  `php artisan test --filter=...`.
- No CI pipeline — verify manually **(tests + Pint)** before finishing.
- No comments unless the surrounding codebase style demands them.

## 11. Build Order & MVP

- MVP = Solo Edition (BDR-017/020/021). MVP modules: platform foundation,
  MOD-001, MOD-003, MOD-002, MOD-006, MOD-004, MOD-005 (basic), MOD-007.
- Build order priority: Foundation (1) → MOD-001 (2) → MOD-003 (3) → MOD-002
  (4) → MOD-006 (5) → MOD-004 (6) → MOD-005 (7) → MOD-007 (8) → MOD-008 (9) →
  MOD-009 (10) → MOD-011 (11) → MOD-010 (12) → MOD-012/013 (13).
  (`docs/09-Development/implementation/030-module-build-order.md`)
- **Timeline (MOD-002) must exist before event-producing modules ship.**
- Never build a dependent module before its prerequisite.

---

*Source documents remain authoritative: WWDF `00-WWDF/` and `docs/09-Development/`.*