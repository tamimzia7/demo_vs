# MOD-002 — Timeline

> **Type:** Module Implementation Record (development-facing)
> **Status:** NOT_STARTED · **Compiled:** 2026-08-18
> Source documents remain authoritative; nothing here invents business rules.

---

## A. Module Overview

| Attribute | Value |
|---|---|
| **Module ID** | MOD-002 |
| **Module name** | Timeline |
| **Purpose** | The home inside the Visitor Workspace; the single source of visitor history, newest first (BDR-011). |
| **Business objective** | Let a marketer instantly answer "what happened with this visitor?". |
| **Business meaning** | The Timeline is **not a standalone module** — it is the **Visitor Home** and a **read-only view**. It owns **no business data**; events are produced by all other modules. |
| **Product Map position** | `VisiCore → Visitors → Visitor Workspace → Timeline (home)`. |
| **MVP/Post-MVP status** | **MVP.** Timeline (append-only history) is in MVP scope (`020-mvp-definition.md`). |
| **Scope** | Append-only event storage, event classification (User/System), newest-first presentation, read-only filtering/search, correction **record-keeping** (append, never overwrite). |
| **Non-scope** | Creating business data; owning any module's records; deleting/editing events; synchronous user writes into other modules' data. |

---

## B. Source Traceability

| Concern | Source ID / Location |
|---|---|
| MOD definition | MOD-002 in `docs/05-Product-Blueprint/080-module-definition-records.md` |
| Requirements | REQ-003 (newest first), REQ-004 (never deleted), REQ-005 (classification) in `docs/02-Requirements/functional/req-mod-002-timeline.md` |
| Business process | PROC-005 (Timeline Event Creation) |
| BDRs | BDR-005 (history never deleted), BDR-011 (Timeline is home), BDR-012 (events), BDR-013 (User/System split), BDR-016 (System event catalogue) |
| NFRs | NFR-001 (permanence), NFR-004 (auditability), NFR-005 (responsive), NFR-008 (scale), NFR-009 (distinguish event types) |
| Data model | `timeline_events` table (`080-table-catalog.md`, `070-entity-to-table-mapping.md`); Timeline Event entity (`020-entity-catalog.md`) |
| API | `docs/07-API/170-timeline-api.md` |
| UI/UX | `docs/06-UI-UX/090-timeline-specification.md`, `010-workspace-specification.md`, `030-widget-library.md`, `050-empty-state-philosophy.md` |
| Access control | `100-audit-philosophy.md`, `110-timeline-correction-policy.md`, visibility matrix `050-visibility-matrix.md` |
| Architecture | `04-Architecture/application/060-application-event-model.md`, `100-event-architecture.md` (data-model), `18-timeline-pattern.md` (WWDF) |
| Feature list | F-008…F-011 in `docs/FEATURE-LIST-WITH-USER-ROLES.md` |
| Reference | `docs/ULTIMATE-VISICORE-REFERENCE.md` §7 |

---

## C. Role-Based Access

| Action | SA | CO | MG (target) | SE (target) | MO (future) |
|---|---|---|---|---|---|
| View Timeline (newest first) | Yes (global) | Yes (company) | Yes (team) | Yes (own + read of transferred) | Planned (aggregated) |
| Create/Update/Delete events | **No** (view-only) | **No** | **No** | **No** | **No** |

> **Timeline is view-only** for users — no Create/Update/Delete on history
> (FEATURE-LIST, MOD-002 note; permission matrix MOD-002 row). **System** is the
> only "writer" (events are projected from module actions).

**Restrictions**
- Transferred relationships: previous marketer has **read** access to history
  (influence recorded, BDR-003/004) but cannot act (visibility matrix).
- Scope per visibility matrix: Own / Team(Company) / Global by role.
- Tenant-scoped (BDR-021).

---

## D. Complete Feature Breakdown

### MVP (V1)

**F-008 — View Timeline, newest first**
- Behavior: Chronological history of the visitor, newest first (REQ-003, BDR-011).
- Rules: Events immutable; single source of history distinguished by
  User-Generated vs System-Generated.
- Permissions: SA (global), CO (company), MG (team), SE (own + read of transferred).

**F-009 — Immutable history**
- Behavior: System (enforced); corrections append, never overwrite (REQ-004,
  BDR-005). A "deleted" event is archived, not physically removed
  (`110-timeline-correction-policy.md`).
- Rules: timeline events never edited/deleted; corrective events reference the
  original (exact correction format is Open Question M-15).
- Permissions: System-enforced; view by all roles within scope.

**F-010 — Event classification**
- Behavior: Each event shown as User-Generated or System-Generated (REQ-005,
  BDR-013; NFR-009).
- Rules: Classification never changes after creation.
- Permissions: All roles (view).

**F-011 — Filter / search events** *(future enhancement)*
- Behavior: Filter by type/channel/date; event detail expand.
- Rules: filtering/pagination per `070-pagination-filtering.md`.
- Permissions: All roles within scope.

### Post-MVP / Future
- Intelligence overlays (suggested next action); search within timeline
  (`090-timeline-specification.md`).
- Visual marking of corrected/superseded events (Open Question).
- Event payload schema (Open Question M-15).

---

## E. Complete User Flow

```text
User opens a visitor's workspace
↓
System renders Timeline as the home tab (REQ-002 / BDR-011)
↓
System validates permission (role + scope + tenant + transferred-read)
↓
System lists Timeline Events newest first (index visitor_vin + created_at desc)
↓
Each event card shows type (User/System), source, summary, timestamp
↓
User expands an event for detail (future: jumps to related module)
↓
User filters by type/channel/date (future enhancement)
```

### Failure flows
- **Authorization failure:** 403 for out-of-scope; 404 for visitor out of scope.
- **Read of out-of-scope event:** treated per visibility matrix; transferred read
  only (previous marketer cannot act).
- **Not-found flow:** 404 visitor/event not found.
- **Business-rule failure:** any attempt to update/delete history → business error
  (BDR-005); corrections must append.

---

## F. Business Rules

1. **Timeline is the home (BDR-011).** Not standalone; newest first; single
   source of visitor history.
2. **Every important interaction creates a Timeline Event (BDR-012).**
3. **History is never deleted (BDR-005/REQ-004/NFR-001).** Events append-only;
   archived markers, never hard delete.
4. **Corrections append (110-timeline-correction-policy).** Original event
   unchanged; corrective event references original; correctives are auditable
   System events.
5. **User/System separation (BDR-013; NFR-009).** User events: Call, Meeting,
   Visit, Note, Reminder, Expense, Discussion, Follow-up. System events catalogue:
   SMS Sent, Email Sent, Knowledge Shared, Purchase, Transfer, Subscription,
   Visitor Created, Notice Sent, and other automated actions (BDR-016).
6. **Timeline owns no business data (BDR-011; WWDF timeline-pattern).** Do not
   store module business data inside the Timeline.
7. **System event catalogue (BDR-016)** is authoritative; do not add event types
   without a BDR/decision.

---

## G. States and Lifecycle

Timeline Event lifecycle (from entity catalog):

```text
Created -> immutable (corrections append; "deleted" archived, never physically removed)
```

| Attribute | Value |
|---|---|
| States | Created → immutable (archived marker if superseded/removed). |
| Allowed transitions | Created → [archived marker]. Corrections append new events. |
| Forbidden transitions | Edit, overwrite, hard-delete, restore? (restoration of archived events not documented — do not invent). |
| Trigger / Actor / Result | Defined per producing module; see that module's §H. |

---

## H. Timeline Integration

> This module **is** the Timeline. Events are produced by other modules; MOD-002
> consumes and presents them.

| Attribute | Value |
|---|---|
| Event types | All documented events: User (Call, Meeting, Visit, Note, Reminder, Expense, Discussion, Follow-up) and System (VisitorCreated, SMSSent, EmailSent, NoticeSent, KnowledgeShared, Purchase, Transfer, Subscription) (BDR-013/016). |
| Trigger | Any producing-module action (PROC-005). |
| User/System | Classified at creation (BDR-013). |
| Actor | Marketer (User events) or System (System events). |
| Visitor | The subject visitor. |
| Timestamp | ISO-8601 UTC. |
| Append-only | Yes. |
| Editable? | No. |
| Deletable? | No (archived marker only). |

**Correction flow** (Open Question M-15 for exact format):
```text
Original event (preserved) ── referenced by ──> Corrective event (appended)
```

---

## I. Audit Integration

- Timeline corrections are themselves auditable System events
  (`110-timeline-correction-policy.md`).
- **Reads are not audited** (`170-timeline-api.md`: "reads are not auditable
  actions").
- Every event projection is itself part of the NFR-004 audit trail (System
  actions recorded as Timeline Events).

---

## J. Data Model

Physical table: **`timeline_events`** (from `070-entity-to-table-mapping.md`)

| Element | Value |
|---|---|
| Primary key | `id` |
| Business identifier | `evn` (EVN-NNNNNN) |
| Foreign keys | `tenant_id`; `visitor_vin`; optional `source_*_id` |
| Tenant ownership | Yes |
| Soft delete | archived marker only (never hard) |
| Archive | archived marker |
| Versioning | append-only; corrective rows reference original |
| Audit | corrections |
| Indexes | `visitor_vin` + `created_at` desc, `type`, `tenant_id` |
| Search fields | summary, type |
| Constraints | `type` ∈ {user, system} |
| Derived fields | none (it is the source) |

> Do NOT redesign. Event payload schema is Open Question M-15.

---

## K. Laravel Implementation

| Component | Expected |
|---|---|
| Controllers | `Http/Controllers/Timeline/TimelineController` (index/detail) — read-only. |
| Form Requests | Filter/query request (pagination, type, channel, date). |
| Resources | `TimelineEventResource` (evn, type, source, summary, created_at). |
| Services | `Timeline\\Services\\TimelineService` (append/project events; present/newest-first). |
| Models | `TimelineEvent` (enum type, immutable semantics). |
| Policies | `TimelineEventPolicy` (view scope incl. transferred read). |
| Middleware | Tenant scope. |
| Events | Application Events from other modules are consumed; projection happens **in the producing service**, not here. |
| Routes | `/api/v1/visitors/{vin}/timeline`, `/{evn}`; web tab route. |
| Views/components | Timeline tab, Timeline Event Card, Filter Bar. |
| Tests | See §Q. |

---

## L. API Specification

From `docs/07-API/170-timeline-api.md` (read-only contract).

| Method | URL | Purpose | Auth | Authz |
|---|---|---|---|---|
| GET | `/visitors/{vin}/timeline` | List events (newest first) | Session or API key | Owner/team-scoped read |
| GET | `/visitors/{vin}/timeline/{evn}` | Event detail | Session or API key | Scope-checked read |

**Request filters:** `type` (user/system), `channel`, date range; pagination/sort.
**Response:** `{ "data": [ { "evn", "type", "source", "summary", "created_at" } ], "meta": {...} }`.
**Errors:** 401, 403 (outside scope), 404 (visitor/event not found), 429, 5xx.
No writes exist — history immutable (any write attempt returns business error).

---

## M. Validation

- Read-only: validate filter params (type enum, channel, date range, per_page cap).
- Pagination/sorting per `070-pagination-filtering.md`.
- No create/update payloads (view-only).
- Scope authorization per visibility matrix.

---

## N. Error Handling

401/403/404/429/5xx as documented; plus business error on any non-read action.
Performance: NFR-005/NFR-008 — indexes `visitor_vin`+`created_at desc` per design.

---

## O. Security

- Auth: session or API key.
- Authorization: read per visibility matrix; transferred-read only (no action).
- Tenant isolation enforced at data layer.
- Events immutable; audit trail preserved.
- Never expose internal keys — use `evn`; VIN where applicable.

---

## P. UI/UX

Per `docs/06-UI-UX/090-timeline-specification.md`:
- **Widgets:** Timeline Event Cards; Filter Bar; Quick Actions (add note; log
  call/meeting; quick-communicate).
- **Summary cards:** event count; last interaction; open follow-ups.
- **Navigation:** expand event detail; jump to related module.
- **Empty state:** "No activity yet." → "Log a call" / "Send a message".
- **Notifications:** highlights new events since last visit.
- User vs System events clearly distinguished (BDR-013, NFR-009) — e.g., distinct
  badge/visual (VISICORE-UI-UX-STANDARD.md).
- Loading/error/responsive/accessibility per standard.

---

## Q. Testing

- Unit: event classification; newest-first ordering; correction-append logic.
- Feature: timeline renders newest first; empty state; transferred-read view.
- API: list/detail; filters; pagination.
- Authorization: 403 outside scope; transferred-read (no action).
- Validation: invalid filter params.
- Business rule: immutable history — no update/delete endpoint works.
- Timeline tests: events from each producing module appear (integration).
- Audit tests: corrections auditable.
- Edge cases: empty timeline; archived/superseded event marker (if implemented).

---

## R. Acceptance Criteria

- [ ] Timeline is the home tab of the Visitor Workspace and shows newest first.
- [ ] Authorized user can view timeline within scope; out-of-scope → 403/404.
- [ ] User-generated vs System-generated events are distinguishable.
- [ ] No endpoint allows editing or deleting a Timeline Event.
- [ ] Correction can only be added as an appended corrective event (format per
      M-15), never an overwrite.
- [ ] Previous owner of a transferred relationship has read access but no actions.
- [ ] Tenant isolation maintained.

---

## S. Developer Checklist

- **Backend:** TimelineController (read-only), TimelineService, TimelineEvent.
- **API:** GET list/detail + filters.
- **Database:** `timeline_events` table (evn, tenant, visitor_vin, type enum,
  source, summary, archive marker).
- **Authorization:** read scope + transferred-read policy + tenant middleware.
- **Timeline:** this IS the timeline — ensure producers append via service only.
- **Audit:** corrections audit.
- **Frontend:** Timeline tab, Event Cards, Filter Bar, empty state.
- **Testing:** §Q.
- **Documentation:** update VISICORE-MODULE-INDEX.md.

---

## Module Dependencies

- **Depends on:** All event-producing modules (MOD-003, MOD-004, MOD-005,
  MOD-006, MOD-007, MOD-008, MOD-011, and platform events). Does **not** depend on
  their internals — consumes projected events.
- **Used by:** MOD-001 (presents timeline), MOD-010 (aggregates), notifications.
- **Produces:** Nothing (presents only).
- **Consumes:** Timeline Events from all modules (append-only).

> No dependency cycles — events flow one way into the Timeline (BDR-011, WWDF
> dependency rules, WWDF timeline-pattern).

---

## Open Questions

| Question | Why it matters | Source documents checked | Possible impact |
|---|---|---|---|
| Event correction-policy details + payload schema (M-15) | Corrective event format | `110-timeline-correction-policy.md`; PROC-005; `170-timeline-api.md` | Correction UX + schema |
| Mandatory event fields (M-4) | Event cards/validation | PROC-005 Open Questions | Required fields |
| Intra-module async vs sync projection (M-19) | Write-path design | `060-application-event-model.md`; Open Questions | Event transport |
| Visual marking of corrected/superseded events | UI | `110-timeline-correction-policy.md`; `090-timeline-specification.md` | Card rendering |

---

*Source documents remain authoritative. Follow the BDR registry and the frozen
architecture (v1.0) when implementing.*