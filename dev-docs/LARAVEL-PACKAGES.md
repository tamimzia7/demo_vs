# Laravel Packages — VisiCore (Free / Open-Source)

> **Type:** Development note — package selection
> **Status:** DRAFT · **Compiled:** 2026-08-18
> **Constraint:** **Free and open-source packages only.** No paid/licensed
> add-ons. This list is an evaluation aid — verify availability and maintenance
> before adding anything. Nothing here is an implementation decision.

---

## 1. Already in the Skeleton

| Package | Version (skeleton) | Role |
|---|---|---|
| `laravel/framework` | ^13.17 | Framework |
| `laravel/tinker` | ^3.0 | REPL (dev) |
| `laravel/pail` | ^1.2.5 | Log tailing (dev) |
| `laravel/pint` | ^1.27 | Code style |
| `nunomaduro/collision` | ^8.6 | CLI error renderer (dev) |
| `phpunit/phpunit` | ^12.5.12 | Tests |
| `mockery/mockery` | ^1.6 | Test mocking |
| `fakerphp/faker` | ^1.23 | Factories |
| `laravel/pao` | ^1.0.6 | Dev tooling (present) |
| `tailwindcss` + `@tailwindcss/vite`, `vite`, `laravel-vite-plugin`, `alpinejs`, `@fontsource-variable/inter`, `concurrently` | ^4 / ^8 / ^3.1 / ^3.14 / ^5.1 / ^10 | Frontend build + Alpine + font |

> Keep the dependency footprint small. Add only what the modules require
> (USUAL CASE: none beyond the skeleton for MVP tenant + behavior).

## 2. Candidates Aligned with VisiCore Needs

| Need | Candidate (free/open-source) | Notes / caution |
|---|---|---|
| **Multi-tenancy** (BDR-021) | Build-in with a `tenant_id` column + middleware (WWDF `12-multi-tenancy-standard.md`, data-layer enforcement) | Do **not** adopt a heavyweight tenant library prematurely; V1 Solo Edition rarely needs tenants beyond a scoped column. |
| **RBAC / roles** (MOD-012) | Laravel built-in Gates/Policies + own `roles`/`permissions` tables | Access-control matrix is an Open Question (MOD-012) — do not buy into a permission package before the matrix is defined. |
| **SMS provider** (MOD-004) | Driver/adapter pattern over any HTTP SMS API (e.g., JotpotSMS/BulkSMS per `260-jotpotsms-integration.md`); `guzzlehttp/guzzle` (bundled) | Use own adapter pushing via HTTP — no SMS package required. |
| **Email** (MOD-004) | Laravel's built-in Mail (SMTP) | Built-in; no package. |
| **Async export** (MOD-010) | Laravel Queues + Jobs (database driver) | Built-in; no package. |
| **Excel/CSV export** (MOD-010, if needed) | `maatwebsite/excel` (MIT), or hand-rolled CSV | Only if export scope resolves (MOD-010 metrics still open). |
| **Rate limiting** | Laravel built-in `RateLimiter` | Built-in. |
| **Audit trail** | Build event-driven audit on Timeline Events (`100-audit-philosophy.md`) | Source design is event-based; a generic audit package is usually unnecessary. |
| **Versioning/Timeline corrections** | Own corrective-event pattern (M-15 pending) | Do not adopt an event-sourcing framework — the WWDF pattern is append-only records, not full ES. |
| **Testing** | PHPUnit (present); Pest optional (re-uses same PHPUnit engine) | Optional developer preference; the skeleton ships PHPUnit. |

## 3. Hard Constraints / Guardrails

- **Free/open-source only.** Anything paid → rejected.
- **No package invented behavior.** Packages must not own business rules or
  Timeline/event semantics (BDR-005/011/012) — business logic lives in Services.
- **Providers behind adapters** (WWDF `07-driver-adapter-pattern.md`) — a package
  choice must be swappable.
- **Mark clearly as DRAFT** — nothing is decided; each entry requires reading the
  relevant API/integration contract before Adding to `pint`-clean code.

## 4. Out of Scope (avoid)

- Payment providers (future `280-future-payment-integration.md`) — not MVP.
- SSO/Enterprise (future `300-future-sso.md`) — not MVP.
- CRM/ERP integrations (future `310-future-crm-erp.md`) — not MVP.
- Storage providers (future `290-future-storage-providers.md`) — not MVP.

---

*Verify current versions/maintenance before adopting. Prefer built-ins + thin
adapters for the MVP.*