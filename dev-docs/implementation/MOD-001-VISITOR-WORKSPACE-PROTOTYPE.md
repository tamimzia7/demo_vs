# MOD-001 — Visitor Workspace Implementation Record

## 1. Feature
- Module ID: MOD-001
- Module name: Visitor Workspace
- Implementation status: Implemented (corrected prototype, Foundation-integrated)

This establishes the documented Visitor Workspace (container) per MOD-001, reusing
the completed Foundation (authentication, authorization, tenant isolation). It does
**not** implement MOD-002 … MOD-013.

## 2. Source Traceability
- BDR-002 — Visitor is the center of the platform
- BDR-003 — Relationships belong to marketers, visitors do not
- BDR-005 — History never deleted (no soft delete on visitors)
- BDR-018 — VIN format `VC-YYYY-NNNNNN`, permanent/immutable
- BDR-020 — V1 access model: Super Admin + Company Owner/Marketer
- BDR-021 — Multi-tenant SaaS; tenant isolation
- REQ-001 — Provide Visitor Workspace
- REQ-002 — Timeline as home tab
- PROC-001 — Visitor Intake & Creation
- PROC-010 — Visitor Archiving
- MOD-001-VISITOR-WORKSPACE.md (module implementation record)
- LARAVEL-DEVELOPMENT-STANDARD.md (thin controller / Form Request / service)
- VISICORE-UI-UX-STANDARD.md (design tokens)
- FOUNDATION-IMPLEMENTATION-RECORD.md (auth / authz / tenant reuse)

## 3. Implemented Scope
- Visitor CRUD within the workspace: list/search (F-003), create/intake (F-002),
  view workspace (F-001/F-005), update profile (F-004), archive (F-006),
  restore (F-007).
- VIN generation `VC-YYYY-NNNNNN` (BDR-018) assigned on create; immutable.
- `VisitorCreated` System Timeline Event recorded on create (MOD-001 §H, §K).
- Visitor Workspace container composes the approved module tabs/panels
  (Relationship, Visit, Communication, Knowledge, Purchase, Expense) as read-only
  presentation; the Timeline tab renders the visitor's events (newest first).
- Authorization enforced server-side via `VisitorPolicy` (V1 = SA/CO, BDR-020).
- Tenant isolation enforced at the persistence boundary via the `TenantScoped`
  global scope (reused from Foundation) plus service-level tenant filtering.

## 4. Files Changed
Created:
- `app/Policies/VisitorPolicy.php`
- `app/Http/Requests/Visitor/StoreVisitorRequest.php`
- `app/Http/Requests/Visitor/UpdateVisitorRequest.php`
- `tests/Feature/Visitor/VisitorAuthorizationTest.php`
- `tests/Feature/Visitor/VisitorTenantIsolationTest.php`

Modified:
- `app/Models/Visitor.php` — removed `SoftDeletes`; added `TenantScoped` trait
- `app/Models/Concerns/TenantScoped.php` (Foundation, reused)
- `app/Visitors/Services/VisitorService.php` — records `VisitorCreated` event
- `app/Http/Controllers/Visitor/VisitorController.php` — `authorize()` calls,
  tenant-scoped fetch, timeline events; thin controller + Form Requests
- `app/Http/Controllers/Controller.php` — added `AuthorizesRequests` /
  `ValidatesRequests` traits (standard Laravel base)
- `app/Providers/AppServiceProvider.php` — registered `VisitorPolicy`
- `database/migrations/2026_08_22_000005_create_visitors_table.php` — `vin`
  unique per tenant (composite `tenant_id,vin`); removed `softDeletes()`
- `routes/web.php` — visitors resource + workspace/archive/restore protected by
  `auth`; `destroy` (delete) route excluded (visitors are never deleted)
- `resources/views/visitors/workspace.blade.php` — header (VIN/lifecycle), tab
  bar, Timeline events, composed module panels
- `dev-docs/implementation/MOD-001-VISITOR-WORKSPACE-PROTOTYPE.md` — this record

Test files also exercised: `tests/Feature/Visitor/VisitorTest.php`.

## 5. Database Changes
- `visitors` table (existing migration corrected): `vin` is now unique **per
  tenant** (`$table->unique(['tenant_id','vin'])`) per MOD-001 §J and BDR-021;
  removed `softDeletes()` because visitors must never be deleted (BDR-005 / MOD-001 §J).
- No new tables were added. The documented `audit_logs` table is **not present**
  in the schema, so audit-row persistence is deferred (see Open Questions).
- No undocumented columns, indexes, or relationships were added.

## 6. Routes / API
Web routes (all behind `auth` middleware):
- `GET /visitors` — list/search (`visitors.index`)
- `GET /visitors/create` — create form (`visitors.create`)
- `POST /visitors` — store (`visitors.store`)
- `GET /visitors/{vin}` — workspace (`visitors.workspace`)
- `GET /visitors/{vin}/edit` — edit form (`visitors.edit`)
- `PUT /visitors/{vin}` — update (`visitors.update`)
- `POST /visitors/{vin}/archive` — archive (`visitors.archive`)
- `POST /visitors/{vin}/restore` — restore (`visitors.restore`)

No `/api/v1` endpoints were created. The MOD-001 API contract
(`docs/07-API/140-visitor-api.md`) is documented as a **Draft** logical contract;
exposing it as wire routes is deferred (not required for this prototype phase).

## 7. UI
- `visitors/index.blade.php` — search by name/VIN, list with VIN badges +
  lifecycle badges, archive/restore actions, empty state ("No visitors match.").
- `visitors/create.blade.php` — name, channel (optional), referrer VIN (optional);
  notes VIN is auto-assigned.
- `visitors/workspace.blade.php` — workspace header (name, VIN badge, lifecycle
  badge, channel, created date), Edit/Archive/Restore actions, documented tab bar
  (Timeline active; other tabs present as disabled placeholders since those
  modules are not in this task), Timeline tab rendering `VisitorCreated` + other
  events newest-first, and the composed approved-module panels.
- `visitors/edit.blade.php` — name/channel edit; VIN shown as immutable.
- All views use the committed design tokens (`.card`, `.btn*`, `.badge*`,
  `.field-input`, `.tab-btn`, `bg-accent-*`, `text-ink-*`). No ad-hoc hex colors
  were introduced (see Open Questions re: the 70/30 color instruction).

## 8. Business Behavior
- Create assigns a permanent, immutable VIN (`VC-YYYY-NNNNNN`).
- Archive sets `lifecycle_state = Archived` + `archived_at`; history preserved
  (BDR-005). Restore returns the visitor to `Interested` (reactivation).
- VIN is never changed on update (edit form shows it as immutable).
- De-duplication before create (PROC-001) is **not implemented** — merge rules
  are Open Question M-11 (not invented).

## 9. Timeline / Events
- On create, a `VisitorCreated` System Timeline Event is recorded in
  `timeline_events`: `type = system`, `source = VisitorCreated`,
  `summary = "Visitor created"`, linked to `visitor_vin` and the tenant.
- The Timeline tab consumes these events (newest first). Timeline *content
  logic* (MOD-002) is not implemented in this task.

## 10. Authentication
- Reuses the Foundation session authentication (`AuthenticatedSessionController`,
  `auth` middleware). All visitor routes require an authenticated user; guests are
  redirected to `/login`.

## 11. Authorization / Tenant Isolation
- `VisitorPolicy` (registered) gates `viewAny/view/create/update/archive/restore`
  on `User::isAdmin()` — V1 active roles Super Admin + Company Owner (BDR-020).
  Team Edition roles (Manager, Sales Executive) are not activated; a
  `sales_executive` user is denied (403).
- `TenantScoped` global scope applied to `Visitor` (reused from Foundation):
  Super Admin sees all tenants; other roles see only their own tenant. Service
  methods also filter by `tenant_id`. Cross-tenant workspace access returns 404.

## 12. Tests
- `tests/Feature/Visitor/VisitorTest.php` — list, create, VIN format, workspace,
  archive, restore, search (existing, still green).
- `tests/Feature/Visitor/VisitorAuthorizationTest.php` — guest redirect; CO
  allowed; sales_executive denied for index/create/archive (403).
- `tests/Feature/Visitor/VisitorTenantIsolationTest.php` — cross-tenant workspace
  404; other-tenant visitors excluded from index; `VisitorCreated` event recorded;
  same VIN number allowed across tenants (per-tenant uniqueness).

## 13. Verification
- `composer test`: **passed — 142 tests, 314 assertions** (full suite, incl.
  Foundation + all modules).
- `./vendor/bin/pint`: **passed** (auto-formatted 2 files, then clean).
- `npm run build`: **succeeded**.

## 14. Open Questions
1. **Audit logging** — MOD-001 §I documents audit rows, but no `audit_logs` table
   exists in the schema; deferred (not invented).
2. **De-duplication / merge** (Open Question M-11) — not implemented.
3. **Mandatory visitor fields** — exact required set is an Open Question; only
   `name` is required (PROC-001 minimal).
4. **Lifecycle transition restrictions + VIP criteria** (M-2) — not enforced.
5. **MOD-001 API** (`140-visitor-api.md`) — Draft; web-only for this prototype.
6. **UI color instruction vs authoritative tokens** — the task supplied a 70/30
   hex palette (`#F8FAFC` / `#1E3A5F`), but `VISICORE-UI-UX-STANDARD.md` mandates
   the design tokens in `app.css` ("No ad-hoc hex colors"). The authoritative
   UI/UX standard was followed; the hex instruction is reported as a conflict.
7. **Tab order / role-adaptive layout** — future enhancement.

## 15. Out of Scope
- MOD-002 (Timeline content logic), MOD-003 (Relationship Center logic),
  MOD-004…MOD-013.
- Referral / participant-promotion event production (cross-cutting; deferred).
- QR/barcode VIN display (future).
- API key authentication (P-03) — deferred.
- Visitor deletion (history never deleted; only archive).
- Any undocumented field, relationship, permission, role, or screen.
