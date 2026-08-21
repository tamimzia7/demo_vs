# MOD-006 — Visit Management

> **Type:** Module Implementation Record (development-facing)
> **Status:** NOT_STARTED · **Compiled:** 2026-08-18
> Source documents remain authoritative; nothing here invents business rules.

---

## A. Module Overview

| Attribute | Value |
|---|---|
| **Module ID** | MOD-006 |
| **Module name** | Visit Management |
| **Purpose** | Schedule and log visits with participants. |
| **Business objective** | Capture on-the-ground engagement. |
| **Business meaning** | When and where did we meet, and who attended? Visits are logged with **participants** (family/accompanying parties) who stay distinct from the Visitor until promoted (BDR-007). |
| **Product Map position** | `VisiCore → Visitors → Visitor Workspace → Visit Management`. |
| **MVP/Post-MVP status** | **MVP.** Visit logging + participant promotion are in MVP scope (`020-mvp-definition.md`). |
| **Scope** | Record a visit (date, context, outcome); record participants; optionally **promote a Participant to a Visitor** (PROC-011); Produce "Visit" Timeline Event (User-Generated). |
| **Non-scope** | Scheduling/calendar and route planning (future enhancement); promotion criteria (Open Question M-13); scheduled-vs-occurred event distinction (Open Question). |

---

## B. Source Traceability

| Concern | Source ID / Location |
|---|---|
| MOD definition | MOD-006 in `docs/05-Product-Blueprint/080-module-definition-records.md` |
| Requirements | REQ-012 (visits + participants → event), REQ-013 (promote participant) in `req-mod-006-visit-management.md` |
| Business processes | PROC-004 (Visit Logging), PROC-011 (Participant Promotion) |
| BDRs | BDR-007 (family members are Visit Participants, optionally promotable), BDR-012 (every interaction → event) |
| NFRs | NFR-001 (permanence), NFR-004 (auditability) |
| Data model | Visit entity, Participant entity; `visits`, `visit_participants` (`020-entity-catalog.md`, `070-entity-to-table-mapping.md`, `080-table-catalog.md`) |
| API | `docs/07-API/160-visit-api.md` |
| UI/UX | `docs/06-UI-UX/100-visit-screen.md`, `010-workspace-specification.md` |
| Access control | Permission matrix, visibility matrix |
| Architecture | Application event model; timeline pattern |
| Feature list | MOD-006 rows in `docs/FEATURE-LIST-WITH-USER-ROLES.md` |
| Reference | `docs/ULTIMATE-VISICORE-REFERENCE.md` |

---

## C. Role-Based Access

| Action | SA | CO | MG (target) | SE (target) | MO (future) |
|---|---|---|---|---|---|
| Log a visit + participants | Global (platform) | Yes (company) | Yes (team) | Yes (own) | Planned |
| View visits | Global read | Yes | Yes | Own + transferred-read | Planned |
| Promote a participant | Global (platform) | Yes | Yes | Yes (own) | Planned |
| Delete/edit visits | **No** | **No** | **No** | **No** | **No** |

**Restrictions**
- Visit records are immutable on the timeline (BDR-005/012).
- Promotion actions limited to in-scope marketers.
- Tenant-scoped (BDR-021).

---

## D. Complete Feature Breakdown

### MVP (V1)

**F-022 — Log a visit**
- Behavior: Record an in-person or scheduled visit (date, context, outcome) and
  its participants (REQ-012, PROC-004).
- Rules: Participants must exist or be captured; a User-Generated **"Visit"**
  Timeline Event is created (PROC-004 step 3).
- Permissions: in-scope CO/MG/SE.

**F-023 — Participants**
- Behavior: Accompanying parties recorded as Participants, distinct from a
  Visitor (BDR-007, REQ-012).
- Rules: Participants do NOT become Visitors automatically ("one visitor ↔ one
  Timeline", no premature identity creation).
- Permissions: in-scope.

**F-024 — Promote a participant**
- Behavior: Optional promotion of a Participant to a Visitor when they express
  independent interest (REQ-013, PROC-011).
- Rules: New Visitor created, **linked to prior visit history**; System-Generated
  event records the promotion (PROC-011 step 3). Decline → remains Participant.
- Permissions: in-scope marketer.

### Post-MVP / Future
- Scheduling/calendar, route planning (MOD-006 "Future Enhancements").
- Distinct scheduled-vs-occurred event types; mandatory-field rules (Open
  Questions); promotion criteria (Open Question M-13).

---

## E. Complete User Flow

**Visit logging (PROC-004):**
```text
Marketer opens a Visitor's workspace → New Visit
↓
Logs date, context, outcome
↓
Adds accompanying parties as Participants
↓
System validates permission + required fields
↓
System creates Visit record + Participants
↓
System creates "Visit" Timeline Event (User-Generated)
```

**Participant promotion (PROC-011):**
```text
Participant later expresses independent interest
↓
Marketer promotes (participants/{par}/promote)
↓
System creates a new Visitor
↓
System links prior visit history (participant → visitor)
↓
System creates System-Generated promotion event
(If declined → party remains a Participant)
```

### Failure flows
- **Promotion declined** → stays Participant (no exception).
- **Out-of-scope → 403; visitor/visit/participant not found → 404.**
- **Missing mandatory visit fields** → 422 (exact mandatory set is Open Question).

---

## F. Business Rules

1. **Family members are Visit Participants (BDR-007).** Not forced into Visitor
   records until/unless promoted.
2. **Every interaction → event (BDR-012); "Visit" is User-Generated (PROC-004).**
3. **Promotion is optional** and creates a Visitor linked to prior visit history
   (PROC-011); decline leaves the party a Participant.
4. **History retained;** visits immutable (BDR-005).
5. **One Visitor ↔ one Timeline across many journeys (BDR-007 implications).**

---

## G. States and Lifecycle

Visit / Participant lifecycle (derived from PROC-004, PROC-011; no explicit
state diagram in source):

```text
Visit: Logged (occurred or scheduled) -> immutable
Participant: Captured on visit -> (promoted → Visitor)  | (declined → stays Participant)
```

| Attribute | Value |
|---|---|
| States | Visit: Scheduled (if distinguished) → Logged → immutable; Participant: Captured → Promoted / Retained |
| Allowed transitions | Log visit; promote (participant→visitor) |
| Forbidden transitions | Delete/edit of logged visits; automatic promotion |
| Trigger / Actor / Result | Marketer logs; Participant promotion → new Visitor + event |

> Scheduled vs occurred statuses are Open Questions (PROC-004) — do not invent
> a status enum for "Scheduled" without decision.

---

## H. Timeline Integration

| Attribute | Value |
|---|---|
| Event type produced | **"Visit"** — User-Generated (PROC-004, BDR-012) |
| Trigger | Logging a visit (PROC-004) |
| User/System | User |
| Actor | Marketer |
| Visitor | The visited visitor |
| Timestamp | ISO-8601 UTC |
| Participants | Linked on Visit record (not on the event payload unless specified) |
| Append-only | Yes |
| Promotion event | System-Generated (PROC-011) |

---

## I. Audit Integration

- Visit creation + participant promotion are auditable actions (NFR-004)
  (`100-audit-philosophy.md`).
- Promotion linkage (participant → visitor) should be traceable.

---

## J. Data Model

Physical tables: **`visits`**, **`visit_participants`** (per
`070-entity-to-table-mapping.md`, `080-table-catalog.md`)

| Element | Value |
|---|---|
| Primary key | `id` |
| Business identifiers | Visit (`vis`? per `060-identifier-strategy.md` sample prefix style); Participant (`par`? — `POST /participants/{par}/promote` uses `par`) |
| Foreign keys | `tenant_id`; `visitor_vin` (visit); `visit_id` + participant fields |
| Tenant ownership | Yes |
| Soft delete | No (immutable history, BDR-005) |
| Archive | N/A |
| Versioning | N/A |
| Audit | Visit logging + promotion |
| Indexes | `visitor_vin`, `visit_date`, `tenant_id` |
| Constraints | Participants linked to a visit; promotion creates Visitor + link |

> Confirm exact column names in `080-table-catalog.md`; do not redesign.

---

## K. Laravel Implementation

| Component | Expected |
|---|---|
| Controllers | `Http/Controllers/Visit/VisitController` (log/list); `Http/Controllers/Visit/ParticipantController` (promote). |
| Form Requests | LogVisitRequest (date, context, outcome, participants[]); PromoteParticipantRequest. |
| Resources | `VisitResource`; `ParticipantResource`. |
| Services | `Visit\\Services\\VisitService` (create visit+participants, project event); `Visit\\Services\\ParticipantPromotionService` (create Visitor, link history). |
| Models | `Visit`, `VisitParticipant`. |
| Policies | `VisitPolicy` (log/view/promote). |
| Middleware | Tenant scope. |
| Events | `VisitLogged` → Timeline; `ParticipantPromoted` → Visitor + Timeline. |
| Routes | `POST /visitors/{vin}/visits`, `GET /visitors/{vin}/visits`, `POST /participants/{par}/promote`. |
| Views/components | Visit form (date/context/outcome + participants), visit list, participant chip + promote action. |
| Tests | See §Q. |

---

## L. API Specification

From `docs/07-API/160-visit-api.md`.

| Method | URL | Purpose | Auth | Authz |
|---|---|---|---|---|
| POST | `/visitors/{vin}/visits` | Log a visit | Session/API key | In-scope marketer |
| GET | `/visitors/{vin}/visits` | List visits | Session/API key | Scope read |
| POST | `/participants/{par}/promote` | Promote to visitor | Session/API key | In-scope marketer |

**Payload (log):** date, context, outcome, participants[].
**Response:** 201 envelope `{data, meta}`; promotion returns created Visitor.
**Errors:** 401, 403, 404 (vin/par), 422 (field/participant validation), 429, 5xx.

---

## M. Validation

- Required visit fields (date, context, outcome) — exact mandatory set is Open
  Question (PROC-004); apply documented rules only.
- Participants validated as existing or capturable; promotion target valid.
- Envelope/errors per standards.

---

## N. Error Handling

401/403/404/422/429/5xx per `060-error-handling.md`. Promotion decline is a
benign branch (no error). Report business-rule failures as business errors.

---

## O. Security

- Auth: session or API key.
- Authorization: log/view/promote per scope; tenant isolation (BDR-021).
- Participant PII handled under NFR-003 (data protection).
- No edit/delete of history (BDR-005).

---

## P. UI/UX

Per `docs/06-UI-UX/100-visit-screen.md`, `010-workspace-specification.md`:
- Visit form: date, context, outcome; participant entry (family/accompanying).
- Visit list with expandable participant chips.
- Promotion affordance on participant (with confirm dialog).
- Empty state: "Log the first visit".
- Responsive/accessible/loading states per standard.

---

## Q. Testing

- Unit: visit creation; participant→visitor promotion (history link); event types.
- Feature: log visit with participants; promote; decline path.
- API: POST/GET per `160-visit-api.md`.
- Authorization: out-of-scope 403.
- Timeline: "Visit" User event; promotion System event.
- Audit: promotion traceability.
- Edge: participant promotion on deleted/archived visit; visit without
  participants.

---

## R. Acceptance Criteria

- [ ] Visit + participants recorded as a Timeline Event (REQ-012, PROC-004).
- [ ] Visit event is User-Generated; promotion event System-Generated.
- [ ] Participant can be promoted to a Visitor linked to prior visit history
      (REQ-013, PROC-011).
- [ ] Decline keeps participant as-is.
- [ ] History immutable; tenant isolation.

---

## S. Developer Checklist

- **Backend:** VisitController; ParticipantController; VisitService;
  ParticipantPromotionService; Visit/VisitParticipant models.
- **API:** log/list/promote per `160-visit-api.md`.
- **Database:** `visits`, `visit_participants` per entity-to-table mapping.
- **Authorization:** scope checks; tenant middleware.
- **Timeline:** project "Visit" (User) + promotion (System).
- **Frontend:** visit form, participants, promote dialog, list, empty state.
- **Testing:** §Q.
- **Documentation:** update VISICORE-MODULE-INDEX.md.

---

## Module Dependencies

- **Depends on:** MOD-001 (visitor context/VIN), Participants (own entities).
- **Used by:** MOD-001 (Visit Management link), MOD-002 (events), MOD-010
  (aggregates), MOD-005 (no; separate).
- **Produces:** Timeline Events ("Visit" User; promotion System).
- **Consumes:** Visitor VIN; Visit/Participant data.

> No dependency cycles — one-way event flow.

---

## Open Questions

| Question | Why it matters | Source documents checked | Possible impact |
|---|---|---|---|
| Mandatory minimum visit fields | Form + validation | PROC-004 Open Questions; REQ-012 | Schema + UX |
| Scheduled vs occurred event types | Timeline semantics | PROC-004 Open Questions; MOD-006 def | Event enum |
| Promotion criteria/triggers (M-13) | When promotion allowed | PROC-011 Open Questions | Business rule |
| Promotion reversible? | Data safety | PROC-011 Open Questions | State machine |

---

*Source documents remain authoritative. Follow the BDR registry and the frozen
architecture (v1.0) when implementing.*