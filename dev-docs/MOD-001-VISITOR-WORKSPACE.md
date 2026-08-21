# MOD-001 — Visitor Workspace

> **Type:** Module Implementation Record (development-facing)
> **Status:** NOT_STARTED · **Compiled:** 2026-08-18
> This file is a **implementation-oriented companion** to the source
> documentation in `docs/`. Source documents remain authoritative; nothing here
> invents business rules.

---

## A. Module Overview

| Attribute | Value |
|---|---|
| **Module ID** | MOD-001 |
| **Module name** | Visitor Workspace |
| **Purpose** | The primary workspace for a single visitor; the container for every visitor-scoped tab. |
| **Business objective** | Provide a single, focused home from which a marketer engages one visitor and performs every visitor-scoped action. |
| **Business meaning** | The Visitor is the center of the platform (BDR-002); the workspace is where a marketer "works the visitor." |
| **Product Map position** | `VisiCore → Visitors → Visitor Workspace` (primary workspace, one per visitor). Contains Timeline (home), Relationship, Communication, Knowledge, Visit, Purchase, Investment. See `docs/05-Product-Blueprint/010-product-map.md`. |
| **MVP/Post-MVP status** | **MVP (V1 Solo Edition).** Scoped in `docs/09-Development/implementation/020-mvp-definition.md` (Visitor + VIN = MOD-001, yes in MVP). |
| **Scope** | Container + composition; visitor intake, profile view/update, search, archive/restore, VIN display, workspace header, tab bar, dashboard/visitors navigation. |
| **Non-scope** | The Timeline content itself (MOD-002); any tab's business data (each tab is its own module); visitor lifecycle definition logic (MOD-007 advances lifecycle; lifecycle state is presented here). |

---

## B. Source Traceability

| Concern | Source ID / Location |
|---|---|
| MOD definition | MOD-001 in `docs/05-Product-Blueprint/080-module-definition-records.md` |
| Requirements | REQ-001 (provide Visitor Workspace), REQ-002 (Timeline as home tab) in `docs/02-Requirements/functional/req-mod-001-visitor-workspace.md` |
| Business process | PROC-001 (Visitor Intake & Creation), PROC-010 (Visitor Archiving), PROC-009 (Referral — creates visitors), PROC-011 (Participant Promotion — creates visitors) |
| BDRs | BDR-002 (visitor is center), BDR-005 (never deleted), BDR-011 (Timeline is home), BDR-018 (VIN) |
| NFRs | NFR-006 (efficient daily tasks), NFR-001 (history permanence) |
| Data model | `visitors` table in `docs/04-Architecture/persistence/080-table-catalog.md`, `070-entity-to-table-mapping.md`; Visitor entity in `020-entity-catalog.md`; readable lifecycle in `040-entity-lifecycle.md` |
| API | `docs/07-API/140-visitor-api.md` (Visitor API contract) |
| UI/UX | `docs/06-UI-UX/010-workspace-specification.md`, `020-navigation-flow.md`, `070-dashboard-specification.md`, `080-visitor-workspace.md`, `030-widget-library.md`, `050-empty-state-philosophy.md` |
| Access control | Permission matrix `040-permission-matrix.md`, visibility matrix `050-visibility-matrix.md`, VIN `030-visitor-identity-number.md` |
| Feature list | F-001…F-007 in `docs/FEATURE-LIST-WITH-USER-ROLES.md` |
| Reference | `docs/ULTIMATE-VISICORE-REFERENCE.md` §7 |

> No separate "API-XXX" identifier is defined in source docs for this module; the
> source API contract is referenced by filename (`docs/07-API/140-visitor-api.md`).

---

## C. Role-Based Access

Permission basis: `docs/04-Architecture/access-control/040-permission-matrix.md`
(capability) × `050-visibility-matrix.md` (scope). V1 active roles: **Super Admin
(SA)** and **Company Owner / Marketer (CO)**; Manager (MG), Sales Executive (SE),
Marketing Officer (MO) are target-state Team Edition roles (BDR-017, BDR-020).
Current (not future) roles per BDR-008: SA, CO, SE, MG.

| Action | SA | CO | MG (target) | SE (target) | MO (future) |
|---|---|---|---|---|---|
| View workspace / visitors | Yes (global) | Yes (company) | Yes (team) | Yes (own) | Planned (aggregated) |
| Create visitor | Yes | Yes | Yes | Yes (own) | Planned |
| Update visitor profile | Yes | Yes | Yes | Yes (own) | Planned |
| Archive visitor | Yes | Yes | Yes | Yes (own) | No |
| Restore archived visitor | Yes | Yes | Yes | **No** | No |
| Delete visitor (non-history record) | Yes | Yes | Yes | **No** | No |
| Search visitors (name/VIN/tags) | Yes (global) | Yes (company) | Yes (team) | Yes (own) | Planned |
| Manage workspace tabs/actions | Role-scoped per tab | Role-scoped per tab | Role-scoped per tab | Role-scoped per tab (own) | N/A |

**Restrictions**
- **Ownership:** Relationships belong to marketers; **visitors are never owned**
  (BDR-003). "Own" scope applies to relationships, not to the visitor record.
- **Tenant:** All visitor data is `tenant_id`-scoped; no cross-tenant access
  (BDR-021, `multi-tenancy-persistence.md`).
- **Delete:** "Delete" applies to **non-history** records only (Permission Matrix
  note); visitor history is never deleted (BDR-005) — archiving is the terminal
  state for inactive visitors.

---

## D. Complete Feature Breakdown

### MVP (V1)

**F-001 — Visitor Workspace (container)**
- Behavior: Primary tabbed workspace for one visitor; opens on Timeline.
- Rules: Home tab is always Timeline (REQ-002, BDR-011); tabs map to approved
  modules only (`workspace-specification.md`); actions gated by permission matrix.
- Permissions: SA (global), CO (company), MG (team), SE (own).

**F-002 — Create a Visitor (intake)**
- Behavior: Create a visitor at first expression of interest via any channel
  (PROC-001); system assigns VIN and records `VisitorCreated` System event.
- Rules: De-duplication check before create (Open Question M-11 — exact merge
  rules not defined); VIN permanent, immutable, never reused (BDR-018).
- Permissions: SE (own), MG, CO, SA.

**F-003 — View / search visitors**
- Behavior: List and search by name, VIN, tags.
- Rules: Tenant-scoped; contains only allowed-scope rows (visibility matrix).
  VIN searchable (BDR-018).
- Permissions: SA (global), CO (company), MG (team), SE (own).

**F-004 — Update visitor profile**
- Behavior: Edit identity detail (not history). Profile mutable; changes audited
  (entity catalog → Visitor "Versioning: profile mutable (audited)").
- Rules: History immutable; VIN never changes (BDR-018).
- Permissions: SE (own), MG, CO, SA.

**F-005 — Open Timeline as home**
- Behavior: Workspace lands on Timeline tab by default (REQ-002, BDR-011).
- Permissions: all roles.

**F-006 — Archive a Visitor**
- Behavior: Move inactive visitor to Archived; history preserved and
  reactivatable (PROC-010). System event records change.
- Permissions: SE (own), MG, CO, SA.

**F-007 — Restore an archived Visitor**
- Behavior: Reactivate archived visitor (Permission Matrix "Restore").
- Permissions: MG, CO, SA (SE cannot).

### Cross-cutting (MOD-001 adjacent but defined in source modules)

- **F-053 — Create a Referral** (creates a visitor, PROC-009): SE (own), MG, CO, SA — V1.
- **F-054 — Tag visitors/relationships** (PROC-012): SE (custom, own), MG, CO, SA (system) — V1.
- **F-055 — Reminders/Follow-ups** (PROC-013 planned): SE, MG, CO, SA — V1/planned.
- **Visitor lifecycle advance** is driven by MOD-007 Purchase (F-032, system-driven).

### Post-MVP / Future

- Customizable workspace layout per role (MOD-001 Future Enhancements).
- Role-adaptive workspace layout (`workspace-specification.md` Open Questions).
- Tab order customization (Open Question).
- De-duplication/merge across channels (Open Question M-11).
- QR/barcode VIN display (future, `030-visitor-identity-number.md`).

---

## E. Complete User Flow

### Success flow — Open a visitor's workspace
```text
User logs in
↓
Dashboard (see tasks/recent)   [Navigation philosophy: start at Dashboard]
↓
Visitors list (search by name/VIN/tags)
↓
System validates authorization (role + scope + tenant)
↓
User opens Visitor Workspace
↓
System renders workspace header (name, VIN, relationship owner, lifecycle state)
↓
Timeline tab is shown by default (REQ-002 / BDR-011)
↓
UI state update
```

### Success flow — Create a visitor (PROC-001)
```text
Interest expressed via any channel (call, WhatsApp, website, referral, broker, seminar, exhibition, …)
↓
Marketer/system checks for existing visitor (de-duplication)   [M-11 rules open]
↓
No match
↓
Create Visitor record (name, channel, contact, optional referrer_vin)
↓
Request validation (required fields; VIN format VC-YYYY-NNNNNN — BDR-018)
↓
Business rule validation (tenant scope; dedup)
↓
VIN assigned by system (permanent, never reused)
↓
Database insert (tenant-scoped)
↓
"Visitor Created" System Timeline Event recorded
↓
Timeline appears (newest first)
↓
Audit record (visitor create)
↓
Response 201 + visitor resource
↓
UI: workspace opens on Timeline (empty state → "Log a call"/"Send a message")
```

### Success flow — Archive a visitor (PROC-010)
```text
Visitor inactive
↓
User invokes Archive
↓
Authorization check (SE own / MG / CO / SA)
↓
lifecycle_state → Archived; archived_at set
↓
System Timeline Event records change
↓
History remains intact; UI back to Visitors list
```

### Success flow — Restore an archived visitor
```text
User invokes Restore (MG / CO / SA only; SE denied — Permission Matrix)
↓
Authorization check
↓
lifecycle_state → active state
↓
Audit record
↓
UI refresh
```

### Failure flows
- **Authorization failure:** 403 — caller allowed by role but outside scope/tenant.
- **Not-found flow:** 404 — visitor missing or outside scope (API treats
  out-of-scope as not found).
- **Validation failure:** 422/400 — missing required fields, invalid VIN format.
- **Duplicate:** 409 — existing visitor (dedup) — merge rules open (M-11).
- **Business-rule failure:** attempt to delete history → business error (BDR-005).
- **Edge case:** referral where referred person is already a visitor → link to
  existing, don't duplicate (PROC-009 exception).

---

## F. Business Rules

1. **Visitor is the center (BDR-002).** Projects/offerings organize visitors; the
   visitor — identity, history, journey — is permanent, central, never deleted.
2. **History never deleted (BDR-005).** Archived ≠ deleted; corrections append.
3. **Relationships belong to marketers, visitors do not (BDR-003).** The workspace
   presents a visitor a marketer is responsible for; the marketer does not own the
   visitor record.
4. **VIN rules (BDR-018).** `VC-YYYY-NNNNNN`; permanent, immutable, never reused,
   human-friendly, searchable, printable, QR-ready. Internal DB keys never exposed.
5. **De-duplication (PROC-001).** Match existing record before creating; exact
   merge rules are **Open Question M-11** (not implemented with invented rules).
6. **Archiving (PROC-010).** Terminal-inactive state; fully preserved; Reactivation
   = PROC-014 (Planned).
7. **Profile mutable, history immutable (entity catalog).** Profile edits audited;
   Timeline events never edited.
8. **Mandatory visitor profile fields (PROC-001).** Exact set is **Open Question**
   ("Mandatory fields for a new visitor record"). Do not invent.

---

## G. States and Lifecycle

Visitor lifecycle (from `docs/04-Architecture/data-model/040-entity-lifecycle.md`
and `docs/01-Business/040-visitor-lifecycle.md`):

```text
Interested -> Negotiating -> Purchased -> Repeat Customer -> VIP
     ^            |             |              |
     |            v             v              v
     +---- Referral <-----------+--------------+
   Any state --> Archived (history preserved, never deleted)
```

| State | Meaning |
|---|---|
| Interested | Expressed interest; relationship beginning. |
| Negotiating | Actively discussing a project/offering. |
| Purchased | Completed a purchase. |
| Referral | Has referred a new visitor. |
| Repeat Customer | Purchased more than once. |
| VIP | High-value, long-term relationship (criteria Open Question M-2). |
| Archived | Inactive, history fully preserved. |

- **Allowed transitions:** Source docs show a forward path; states are "not
  strictly one-directional" (Reference §2.2). **Restrictions on transitions and
  VIP criteria are Open Questions (M-2).** Do not invent transition restrictions.
- **Trigger/actor/result:** Only lifecycle advance via Purchase is documented as
  system-driven (MOD-007/PROC-008). Archive is user-initiated (PROC-010). Referral
  branch via PROC-009. Reactivation PROC-014 (Planned).
- **Timeline events:** lifecycle changes create System events (Reference §6).

---

## H. Timeline Integration

- **MOD-001 produces Timeline Events?** No — it is a container
  (`080-module-definition-records.md`: "Produces Timeline Events? No (it is a
  container)").
- **MOD-001 consumes Timeline Events?** Yes, via the Timeline tab.
- The **visitor creation** event (`VisitorCreated`, System) is produced by the
  platform during intake (PROC-001), presented here.

| Attribute | Value |
|---|---|
| Event types | `VisitorCreated` (System) on create; lifecycle/archive changes as System events (BDR-016 catalogue). |
| Trigger | Create (PROC-001); archive (PROC-010); referral (PROC-009); promotion (PROC-011). |
| User/System | System-generated. |
| Actor | System (origin recorded: channel / marketer who captured). |
| Visitor | The visitor whose timeline gets the event. |
| Timestamp | ISO-8601 UTC at creation. |
| Append-only | Yes (BDR-005). |
| Editable? | No. |
| Deletable? | No. |

> Referral creation is a cross-cutting capability producing events on **both**
> referrer and referred timelines (PROC-009).

---

## I. Audit Integration

From `docs/04-Architecture/access-control/100-audit-philosophy.md` and
`docs/04-Architecture/persistence/120-audit-persistence.md`.

| Attribute | Value |
|---|---|
| Audited actions | Visitor create; visitor profile change; archive; restore; referral create (link); promotion (in MOD-006). |
| Actor | Acting user (or System for automated actions). |
| Tenant | `tenant_id` of the actor. |
| Target | `visitors` row (entity id). |
| Action | create / update / archive / restore / referral / promote. |
| Metadata | actor, tenant, entity, action, timestamp (+ prior values where defined). |
| Append-only | Yes — audit rows never updated/hard-deleted; owned by System. |

---

## J. Data Model

Physical table: **`visitors`** (from `docs/04-Architecture/persistence/070-entity-to-table-mapping.md`)

| Element | Value |
|---|---|
| Primary key | `id` (internal, never exposed) |
| Business identifier | `vin` (`VC-YYYY-NNNNNN`) — unique per tenant |
| Foreign keys | `tenant_id`; `referrer_vin` (nullable) |
| Tenant ownership | Yes (`tenant_id`) |
| Soft delete | No (history never deleted) |
| Archive | `archived_at` + `lifecycle_state` |
| Versioning | Profile mutable (audited); history immutable |
| Audit | Profile changes |
| Indexes | `vin` (unique/tenant), `tenant_id`, `lifecycle_state`, name search |
| Search fields | name, vin, contact |
| Constraints | vin unique per tenant; vin format `VC-YYYY-NNNNNN` |
| Derived fields | `event_count` (derived), `lifecycle_state` |

Related cross-cutting tables: `tenants`, `users`, `audit_logs`, `system_tags`,
`custom_tags` (see `080-table-catalog.md`).
**Do NOT redesign the database — this is the documented model.**

---

## K. Laravel Implementation

Follow `docs/09-Development/040-laravel-architecture-guide.md`, WWDF backend
standards, and existing WWDF reference implementations. Controllers stay thin;
business logic lives in services.

| Component | Expected |
|---|---|
| Controllers | `Http/Controllers/Visitor/VisitorController` (resource); thin — validate, call service, format response. |
| Form Requests | `StoreVisitorRequest`, `UpdateVisitorRequest`, `ArchiveVisitorRequest` (or action-based). |
| Resources | `VisitorResource` (exposes VIN, no internal keys). |
| Services | `Visitor\\Services\\VisitorService` (intake/create, update, archive, restore, search). |
| Models | `Visitor` (cast/validate VIN; tenant scoped). |
| Policies | `VisitorPolicy` (view, create, update, archive, restore). |
| Middleware | Tenant-scope middleware (cross-cutting; `tenant_id`). |
| Events | `VisitorCreated` (Application Event → projected `VisitorCreated` Timeline Event, System). |
| Jobs | Possibly de-duplication/notification as queue (see Open Question M-11). |
| Notifications | Referral / archive confirmation (per notification philosophy). |
| Routes | `/api/v1/visitors` resource; web routes to workspace. |
| Views/components | Workspace layout: header, VIN badge, tab bar, action bar; Timeline tab. |
| Tests | See §Q. |

Repositories/DTOs only when justified (WWDF `01-backend-standards.md`).

---

## L. API Specification

From `docs/07-API/140-visitor-api.md` (logical contract only — no routes
finalized in source). Prefix: `/api/v1`.

| Method | URL | Purpose | Auth | Authz |
|---|---|---|---|---|
| POST | `/visitors` | Create a visitor | Session or API key | Tenant-scoped (CO/Marketer create) |
| GET | `/visitors/{vin}` | Read a visitor | Session or API key | Tenant-scoped |
| GET | `/visitors` | List/search visitors | Session or API key | Tenant-scoped |
| PATCH | `/visitors/{vin}` | Update profile (not history) | Session or API key | Tenant-scoped |
| POST | `/visitors/{vin}/archive` | Archive (BDR-005) | Session or API key | Tenant-scoped |

**Request fields:** `name`, `channel` (origin), `contact`, optional `referrer_vin`.
**Response fields:** `vin` (`VC-YYYY-NNNNNN`), `name`, `lifecycle_state`,
`channel`, `created_at`.
**Envelope:** `{ "data": ..., "meta": ... }` (`050-request-response-standard.md`).
**Idempotency:** mutating calls accept idempotency keys (`080-idempotency.md`).
**Pagination/filter/search:** `?page/&per_page`, `?sort=-created_at`, name/VIN
search (`070-pagination-filtering.md`).

**Errors:** 409 duplicate visitor; 422 invalid VIN / missing fields; 403 outside
tenant; 404 not found or outside scope; 401 unauthenticated; 429 rate-limited;
5xx server.
**Caveat:** the API doc is a Draft logical contract. Final route/wire shape is an
implementation concern, but must not contradict the documented contract.

---

## M. Validation

| Concern | Value |
|---|---|
| Required fields | Per PROC-001 (exact mandatory set is an Open Question — do not invent). Documented request fields: `name`, `channel`; `contact` optional. |
| Format | VIN `VC-YYYY-NNNNNN` (BDR-018). |
| Length | Not specified in source. **PROPOSED DESIGN DECISION** at implementation time. |
| Unique | `vin` unique per tenant. |
| Business validation | De-dup before create (PROC-001). |
| Authorization validation | Caller within tenant and scope; create allowed per role matrix. |
| Tenant validation | `tenant_id` scoping enforced at data layer. |

---

## N. Error Handling

Per `docs/07-API/060-error-handling.md`:

| Status | Usage for MOD-001 |
|---|---|
| 401 | Missing/invalid authentication. |
| 403 | Authenticated but not allowed (scope/role/tenant). |
| 404 | Visitor not found or outside scope. |
| 409 | Duplicate visitor (de-dup) / state conflict. |
| 422 | Semantic validation failure (e.g., invalid VIN). |
| 429 | Rate limited. |
| 500 | Server error (transient). |
| Provider failures | N/A for this module (no external provider). |

Error format: `{ "error": { "code", "message", "fields"? , "bdr"? } }`. History
deletion attempts return a business error referencing BDR-005.

---

## O. Security

- **Authentication:** V1 session (portal, email/mobile + password) or API keys
  (App ID/Secret) (`07-API/020-authentication.md`); future JWT/OAuth2/SSO/OTP (not V1).
- **Authorization:** Role-based + ownership-based + (future) sharing/transfer
  bases; every endpoint authorized independently (`030-authorization.md`).
- **Policies:** Laravel `VisitorPolicy`; Super Admin platform-wide, CO company-wide.
- **Tenant isolation:** non-negotiable; `tenant_id` at data layer (BDR-021).
- **Ownership:** relationships owned by marketers; visitors owned by platform/company.
- **Audit:** create/update/archive/restore audited (see §I).
- **Sensitive data:** VIN is a business identifier, not an internal key; internal
  DB keys never exposed (BDR-018).
- **External provider security:** N/A here (no external provider).

---

## P. UI/UX

Per `docs/06-UI-UX/`, especially `010-workspace-specification.md`,
`020-navigation-flow.md`, `070-dashboard-specification.md`, `080-visitor-workspace.md`,
`030-widget-library.md`, `050-empty-state-philosophy.md`.

- **Page:** Visitor Workspace (primary tabbed workspace, one per visitor).
- **Navigation:** Login → Dashboard → Visitors → Visitor Workspace. Back to
  Visitors; deep-links into each tab.
- **Workspace header:** identity (name), **VIN badge**, relationship owner,
  lifecycle state.
- **Tabs:** Timeline (home) | Relationship | Communication | Knowledge | Visit |
  Purchase | Expense.
- **Action bar:** Log visit; send communication; share knowledge; transfer;
  record purchase (permission-gated).
- **Summary cards:** lifecycle state; relationship owner; last interaction;
  purchase count.
- **Widgets:** Workspace Header, VIN Badge, Summary Card, Tab Bar, Action Button,
  Tag Chip, Empty State.
- **Empty states:** Visitors list "No visitors match." → Clear filters / "Add a
  visitor". Timeline empty → "No activity yet." → "Log a call" / "Send a message".
- **Notifications:** Inline when a transfer or share affects this visitor; bell
  for transfers/reminders/approvals.
- **Loading/errors:** standard pending/skeleton and error surfaces (see
  VISICORE-UI-UX-STANDARD.md).
- **Responsive:** NFR-006 efficient daily tasks; responsive behavior per standard.
- **Accessibility & design tokens:** see VISICORE-UI-UX-STANDARD.md.

---

## Q. Testing

- **Unit:** VIN generation/validation format; lifecycle-field derivation;
  visitor service create/update/archive/restore logic.
- **Feature:** create visitor via API/web; workspace renders with header + VIN;
  search/filter; archive + restore.
- **API tests:** POST/GET/PATCH/archive endpoints; envelope; pagination;
  idempotency.
- **Authorization tests:** 403 for out-of-scope; Super Admin global vs CO company;
  de-dup 409.
- **Validation tests:** invalid VIN → 422; missing required fields → 422.
- **Business rule tests:** de-dup path; archiving preserves history; VIN
  immutability.
- **Timeline tests:** `VisitorCreated` System event appended on create; archive
  event.
- **Audit tests:** create/profile-change/archive/restore audit rows; append-only.
- **Edge cases:** referral where referred already exists; restore by SE forbidden.

---

## R. Acceptance Criteria

- [ ] Authorized user can open a visitor workspace which lands on the Timeline.
- [ ] Authorized user can create a visitor; system returns `vin` `VC-YYYY-NNNNNN`.
- [ ] Unauthorized/out-of-scope caller receives 403 (or 404 for out-of-scope read).
- [ ] Invalid request (bad VIN, missing field) receives 422.
- [ ] Duplicate visitor triggers de-dup and returns 409 (merge rules open — M-11).
- [ ] Archive preserves all history; visitor reactivatable by MG/CO/SA.
- [ ] Sales Executive cannot restore an archived visitor (403).
- [ ] `VisitorCreated` System Timeline Event is generated on create.
- [ ] Audit record is created for create/update/archive/restore and is append-only.
- [ ] Internal DB keys are never exposed; only VIN is shown.
- [ ] Tenant isolation: no cross-tenant visitor access.

---

## S. Developer Checklist

- **Backend:** VisitorController (thin), VisitorService, Visitor model, VIN
  handling.
- **API:** visitor endpoints per `140-visitor-api.md`; envelope + errors.
- **Database:** `visitors` table per mapping (tenant_id, vin, lifecycle_state,
  archived_at).
- **Authorization:** VisitorPolicy + tenant middleware + role matrix enforced.
- **Timeline:** `VisitorCreated` System event on create; archive event.
- **Audit:** audit rows on create/update/archive/restore.
- **Frontend:** workspace header, VIN badge, tab bar, action bar, empty states.
- **Testing:** §Q suite; invariants as regression tests.
- **Documentation:** reference this file + source docs; update
  VISICORE-MODULE-INDEX.md.

---

## Module Dependencies

- **Depends on:** MOD-002 (Timeline presents history here), MOD-003, MOD-004,
  MOD-005, MOD-006, MOD-007, MOD-008 (all rendered as tabs); Platform Foundation
  (tenancy, auth, access-control, VIN generation).
- **Used by:** Dashboard (navigation to workspace); MOD-009/MOD-010/MOD-011
  (drill-through to visitor workspaces from cross-visitor screens).
- **Produces:** Visitor records + VIN; lifecycle state changes; `VisitorCreated`
  (and lifecycle/archive) Timeline Events; audit rows.
- **Consumes:** `VisitorCreated`/`Referral`/`Promotion`/`Purchase` events (via
  Timeline tab); user navigation; no external providers.

> **No dependency cycles.** MOD-001 is the container; nothing depends on it for
> its business data. WWDF dependency rules respected (`00-WWDF/04-Architecture/05-dependency-rules.md`).

---

## Open Questions

| Question | Why it matters | Source documents checked | Possible impact |
|---|---|---|---|
| Mandatory fields for a new visitor record | Required request validation | PROC-001; `140-visitor-api.md` | 422 set for create (M-4/M-11 area) |
| Visitor de-duplication/merge across channels | Prevents duplicates | PROC-001; `140-visitor-api.md`; Open Question M-11 | 409/merge flows |
| Visitor lifecycle transition restrictions + VIP criteria | State transitions | M-2; `040-entity-lifecycle.md` | Restricting forbidden transitions |
| Whether the Dashboard is role-specific | Navigation | `020-navigation-flow.md`; `070-dashboard-specification.md` | Role-based dashboard variants |
| Tab order fixed vs customizable; role-adaptive layout | UI composition | `080-visitor-workspace.md`; `workspace-specification.md` | Layout switch (future) |
| Whether archived visitors are excluded from default views | Search/list defaults | PROC-010 Open Questions | Default filters |

---

*Source documents remain authoritative. When in doubt, follow the BDR registry
(`docs/01-Business/100-business-decision-records.md`) and the frozen architecture
(`docs/04-Architecture/review/070-architecture-freeze-certificate.md`).*