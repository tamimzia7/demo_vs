# VisiCore Foundation — Implementation Record

## 1. Feature
- Foundation (Platform Foundation: tenancy + authentication + access-control)
- Implementation status: Implemented and verified.

This establishes the documented Foundation before MOD-001 … MOD-013. It does
**not** implement any business module. The build order followed is the
authoritative order: Foundation → MOD-001 → MOD-003 → … (see
`VISICORE-DEVELOPMENT-WORKFLOW.md` §5 and `ULTIMATE-VISICORE-REFERENCE.md` §12).

## 2. Source Traceability

| Concern | Source identifier |
|---|---|
| Foundation = tenancy + auth + access-control | `ULTIMATE-VISICORE-REFERENCE.md` §12; `VISICORE-DEVELOPMENT-WORKFLOW.md` §5; `VISICORE-MODULE-INDEX.md` (MVP "platform foundation") |
| Multi-tenant SaaS, isolated workspace per subscriber, no cross-tenant access | BDR-021; `FEATURE-LIST-WITH-USER-ROLES.md` P-01 |
| Session login with email + password | `FEATURE-LIST-WITH-USER-ROLES.md` P-02 |
| Role-based authorization gated by role + scope | `FEATURE-LIST-WITH-USER-ROLES.md` P-04; BDR-020 |
| V1 access model = Super Admin + Company Owner/Marketer; ABAC future | BDR-020; `ULTIMATE-VISICORE-REFERENCE.md` §5.3 |
| Users/roles(tenant membership)/tenancy required for foundation | `MOD-012-ADMINISTRATION.md` §D, §K |
| Laravel layered architecture (thin controller → service → persistence) | `LARAVEL-DEVELOPMENT-STANDARD.md` §2 |
| Blade + Tailwind 4 + Alpine; design tokens in `app.css` | `VISICORE-UI-UX-STANDARD.md` §1–§3 |
| Tenant isolation at data layer; multi-tenancy middleware + data-layer enforcement | `LARAVEL-DEVELOPMENT-STANDARD.md` §2, §9 |
| Super Admin global visibility / Company Owner company visibility | `ULTIMATE-VISICORE-REFERENCE.md` §5.4–§5.5 |

## 3. Implemented Scope

- Authentication: session login (email + password), logout, protected routes,
  guest redirect to login, rate-limited login, "remember me".
- Tenancy: `Tenant` model + `tenants` table; user → tenant relationship; tenant
  resolution via `IdentifyTenant` middleware.
- Tenant isolation: `TenantScoped` global scope applied to `User`; Super Admin sees
  all tenants, other roles see only their own tenant.
- Authorization: V1 roles Super Admin (`super_admin`) and Company Owner
  (`company_owner`); `RequireRole` middleware; `admin` gate; `AdminPolicy`.
- Foundation UI: login screen; logout in sidebar; protected dashboard/admin.
- Tests: authentication, authorization, tenant isolation.

Out of scope for this task and intentionally NOT implemented: every business
module (MOD-001 … MOD-013), VIN generation, Timeline, and any API-key
infrastructure (see Open Questions).

## 4. Authentication

- Mechanism: Laravel session guard (`web`). Passwords stored hashed via the
  `password => 'hashed'` cast on `User` (no plaintext).
- Credential rule (documented P-02): email + password. Mobile login is NOT
  implemented (no `mobile` column is documented — see Open Questions).
- Login: `POST /login` (validate → `Auth::attempt` → regenerate session →
  redirect intended `dashboard`). Rate-limited (5 attempts) via `RateLimiter`.
- Logout: `POST /logout` (guard logout, invalidate session, regenerate token).
- Registration: NOT implemented (no public registration is documented for V1;
  users are provisioned by SA/CO via the existing admin area).
- Email verification: NOT implemented (not documented for V1).
- Protected routes: `/dashboard` and `/admin*` require `auth`; `/admin*` also
  requires `role:super_admin,company_owner`.
- Unauthenticated behavior: redirect to `login`.
- Files: `app/Http/Requests/Auth/LoginRequest.php`,
  `app/Http/Controllers/Auth/AuthenticatedSessionController.php`,
  `resources/views/auth/login.blade.php:1`.

## 5. Authorization

- Actors (V1 active): Super Admin (platform), Company Owner / Marketer (company).
  Sales Executive / Manager / Marketing Officer are NOT activated (Team/Enterprise
  future per BDR-020).
- Roles stored on `User.role` (enum: `super_admin`, `company_owner`,
  `sales_executive`, `marketing_officer`). Only the first two are granted admin
  access in this foundation.
- Enforcement:
  - `RequireRole` middleware (`app/Http/Middleware/RequireRole.php:1`) — route-level
    role gate; 403 for disallowed roles; redirect guests to login.
  - `admin` Gate defined in `app/Providers/AppServiceProvider.php:25` via
    `AdminPolicy::viewAny`.
  - `AdminPolicy` (`app/Policies/AdminPolicy.php:1`) — `viewAny/view/manage`
    return `User::isAdmin()` (SA or CO).
- Visibility: Super Admin global; Company Owner scoped to own tenant (see §6).
- No granular permission matrix invented (Open Question per MOD-012).

## 6. Tenant Isolation

- Tenant entity: `App\Models\Tenant` (`database/migrations/2026_08_22_000004_create_tenants_table.php:1`),
  columns `id`, `name`, `timestamps`.
- User → tenant: `users.tenant_id` foreign key
  (`database/migrations/2026_08_22_000003_add_tenant_id_and_role_to_users_table.php:1`);
  `User::tenant()` belongsTo.
- Tenant resolution: `IdentifyTenant` middleware
  (`app/Http/Middleware/IdentifyTenant.php:1`) resolves the active tenant from the
  authenticated user (Solo Edition = one tenant per user) and binds it to the
  service container / request.
- Isolation at persistence: `TenantScoped` trait
  (`app/Models/Concerns/TenantScoped.php:1`) adds a global scope to `User`. Super
  Admin → no constraint (global). Other authenticated users → `where tenant_id = user->tenant_id`.
  No authenticated user → no constraint (safe for seeders/console/guest fallback;
  web requests are gated by `auth` before any scoped query).
- Verified by tests: a Company Owner sees only their tenant's users; a Super Admin
  sees all; the admin user-listing service is tenant-scoped.
- Note: module tables (`visitors`, `relationships`, …) already carry `tenant_id`
  but are not yet scoped in this foundation pass — see Open Questions.

## 7. Database Changes

No new migrations were added; the Foundation schema already existed in the
repository (pre-existing migrations). This task used the existing documented
schema:

- `tenants` — created by `2026_08_22_000004_create_tenants_table.php`.
- `users.tenant_id` + `users.role` — added by
  `2026_08_22_000003_add_tenant_id_and_role_to_users_table.php`.
- `roles` and `system_tags` tables — pre-existing (`2026_08_22_000001`,
  `2026_08_22_000002`). `roles` table is NOT used by the V1 authorization model
  (see Open Questions).

No undocumented columns, indexes, or relationships were added.

## 8. Routes / API

Web routes (all foundation-scoped):

- `GET /login` → `AuthenticatedSessionController@create` (guest).
- `POST /login` → `AuthenticatedSessionController@store` (guest).
- `POST /logout` → `AuthenticatedSessionController@destroy` (auth).
- `GET /dashboard` → view (auth).
- `GET /admin` and `admin/*` (users, system-tags) → auth + `role:super_admin,company_owner`.

No API endpoints were created. The documented Foundation API surface is limited to
P-03 (API keys), which is deferred (Open Questions); no `/api/v1` foundation
endpoint was invented.

`routes/web.php:17` (login routes), `routes/web.php:21` (dashboard),
`routes/web.php:37` (`/admin`), `routes/web.php:133` (admin group).

## 9. UI

- `resources/views/auth/login.blade.php:1` — login screen using the committed
  design tokens (`.card`, `.field-input`, `.label`, `.btn-primary`, `.alert-danger`);
  accessible labels, CSRF, server-side `@error` messages, "Remember me".
- `resources/views/components/sidebar.blade.php` — added a logout form/button at
  the bottom of the navigation (POST `/logout`).
- Reused the existing base layout (`layouts/app.blade.php`) and navigation shell
  from `PROTO-001-FOUNDATION-PROTOTYPE.md`.
- No module UI was added; no tenant-switch UI, role-management UI, or speculative
  screens were created.

## 10. Files Changed

Created:
- `app/Http/Requests/Auth/LoginRequest.php`
- `app/Http/Controllers/Auth/AuthenticatedSessionController.php`
- `app/Http/Middleware/IdentifyTenant.php`
- `app/Http/Middleware/RequireRole.php`
- `app/Policies/AdminPolicy.php`
- `app/Models/Concerns/TenantScoped.php`
- `resources/views/auth/login.blade.php`
- `tests/Feature/Foundation/AuthenticationTest.php`
- `tests/Feature/Foundation/AuthorizationTest.php`
- `tests/Feature/Foundation/TenantIsolationTest.php`
- `FOUNDATION-IMPLEMENTATION-RECORD.md`

Modified:
- `app/Models/User.php` — `use TenantScoped;`
- `app/Providers/AppServiceProvider.php` — `admin` gate + AdminPolicy
- `bootstrap/app.php` — registered `tenant` and `role` middleware aliases;
  appended `IdentifyTenant` to the `web` group
- `routes/web.php` — auth routes; `auth` on `/dashboard`; `auth`+`role` on
  `/admin` and the `admin` group
- `resources/views/components/sidebar.blade.php` — logout button

## 11. Tests

- `tests/Feature/Foundation/AuthenticationTest.php` — login page (guest), guest
  redirect from protected route, successful login + redirect, invalid credentials
  rejected, logout.
- `tests/Feature/Foundation/AuthorizationTest.php` — guest redirect from admin,
  Company Owner allowed, Super Admin allowed, non-admin role forbidden (403).
- `tests/Feature/Foundation/TenantIsolationTest.php` — Company Owner cannot see
  other-tenant users; Super Admin global visibility; admin user-listing service
  tenant-scoped.

## 12. Verification

- `composer test` (full suite): **passed — 133 tests, 300 assertions**.
- Targeted Foundation tests: **passed — 19 tests (filter scope), 39 assertions**.
- `./vendor/bin/pint`: **passed (formatted 3 files, then clean)**.
- `npm run build`: **succeeded** (a non-fatal `fontaine` optional-package
  warning is emitted by the pre-existing scaffold; it is unrelated to this change
  and the manifest was produced).

## 13. Open Questions

1. **Foundation definition conflict (documentation).** `PROTO-001-FOUNDATION-PROTOTYPE.md`
   defines Foundation as a navigation shell only (auth/tenancy/DB explicitly "Out of
   Scope"), whereas `ULTIMATE-VISICORE-REFERENCE.md` §12 and
   `VISICORE-DEVELOPMENT-WORKFLOW.md` §5 define Foundation = tenancy + auth +
   access-control. This task implemented the architecture definition. This conflict
   should be resolved by the Architect.
2. **Pre-existing module code.** The repository already contains prototype
   implementations of MOD-001 … MOD-010 and MOD-012 plus a navigation shell, which
   contradicts the "first implementation task / Foundation only" framing. Those
   modules were treated as existing work and left intact; this task added only
   Foundation capabilities.
3. **Dual role representation.** `User.role` is the active V1 role (string enum),
   but a separate `roles` table + `Role` model also exists and is unused by the
   authorization flow. The V1 model uses `User.role`; the `roles` table should be
   either removed or formally adopted once the access-control matrix is defined.
4. **API keys (P-03).** `FEATURE-LIST-WITH-USER-ROLES.md` P-03 documents API keys,
   but no token storage table or API contract is specified. Not implemented; defer
   until storage/contract is documented.
5. **Login via mobile (P-02).** P-02 says "email/mobile + password" but no `mobile`
   column is documented. Only email login implemented.
6. **Module routes not yet auth-protected.** Only Foundation routes
   (`/dashboard`, `/admin*`) are protected. Module routes (visitors, offerings, …)
   remain unprotected in this pass; protecting them is module/Foundation-hardening
   work pending module completion.
7. **Module models not yet tenant-scoped.** `visitors`, `relationships`, etc. carry
   `tenant_id` but are not yet covered by the `TenantScoped` global scope. Full
   cross-tenant isolation for module data requires applying the same scope to those
   models (module work).
8. **Access-control matrix.** The detailed role/permission matrix is an Open
   Question (MOD-012). V1 authorization is limited to BDR-020 (SA + CO); no
   granular permissions were invented.
9. **Audit of system actions (P-06).** Documented as Timeline Events, but Timeline
   (MOD-002) is not built in this Foundation task; deferred.

## 14. Out of Scope

Intentionally NOT implemented per "FOUNDATION ONLY":
- MOD-001 Visitor Workspace (and VIN generation, BDR-018)
- MOD-002 Timeline (immutable history, events)
- MOD-003 Relationship Center (assign/transfer)
- MOD-004 Communication Center
- MOD-005 Knowledge Center
- MOD-006 Visit Management
- MOD-007 Purchase Management
- MOD-008 Relationship Investment
- MOD-009 Offering Management
- MOD-010 Reports & Intelligence
- MOD-011 Subscription
- MOD-012 Administration functionality (beyond V1 users/roles/tenancy foundation)
- MOD-013 Settings
- Social login / OAuth / Google / GitHub login
- MFA, magic links, biometric auth
- Email verification flow
- Public registration
- Team Edition roles (Manager, Sales Executive), Enterprise (ABAC, SSO, ERP/CRM)
- Tenant switching UI, tenant invitations, tenant billing
- Any undocumented database column, route, API endpoint, permission, or role
