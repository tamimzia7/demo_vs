# MOD-003 — Relationship Center

> **Type:** Module Implementation Record (development-facing)
> **Status:** NOT_STARTED · **Compiled:** 2026-08-18
> Source documents remain authoritative; nothing here invents business rules.

---

## A. Module Overview

| Attribute | Value |
|---|---|
| **Module ID** | MOD-003 |
| **Module name** | Relationship Center |
| **Purpose** | Manage the marketer's relationship with the visitor: show, assign, and transfer responsibility. |
| **Business objective** | Answer "who owns this relationship, and what is its status?" and keep that answer current. |
| **Business meaning** | Every visitor contact belongs to **exactly one marketer at a time**; responsibilities can be assigned and transferred **without losing any history** (BDR-003, BDR-004). |
| **Product Map position** | `VisiCore → Relationships` (Relationship Center) within the Visitor Workspace (MOD-001). |
| **MVP/Post-MVP status** | **MVP.** Assignment and transfer are in MVP scope (`020-mvp-definition.md`). |
| **Scope** | View current relationship; assign a relationship to a marketer; request and act on a transfer (authorized by Company Owner in V1); full history preserved across transfer. |
| **Non-scope** | Deleting relationships or history; editing history; multi-relationship per visitor (Open Question M-3); relationship health score (future enhancement). |

---

## B. Source Traceability

| Concern | Source ID / Location |
|---|---|
| MOD definition | MOD-003 in `docs/05-Product-Blueprint/080-module-definition-records.md` |
| Requirements | REQ-006 (assign), REQ-007 (transfer) in `docs/02-Requirements/functional/req-mod-003-relationship-center.md` |
| Business processes | PROC-002 (Relationship Assignment), PROC-003 (Relationship Transfer) |
| BDRs | BDR-003 (relationships belong to marketers), BDR-004 (transfer preserves history), BDR-016 (Transfer event), BDR-019 (Company Owner approves transfer in V1) |
| NFRs | NFR-003 (data protection), NFR-004 (auditability), NFR-008 (scale) |
| Data model | Relationship entity (`020-entity-catalog.md`); `relationships` table (`080-table-catalog.md`); ownership model (`050-ownership-model.md`) |
| API | `docs/07-API/150-relationship-api.md` |
| UI/UX | `docs/06-UI-UX/080-visitor-workspace.md`, `010-workspace-specification.md`, `020-navigation-flow.md` |
| Access control | Permission matrix `040-permission-matrix.md`; visibility matrix `050-visibility-matrix.md`; ownership matrix `060-ownership-matrix.md`; sharing model `070-sharing-model.md`; authorization philosophy `090-authorization-philosophy.md`; identity model `010-identity-model.md`; role definitions `020-role-definitions.md` |
| Architecture | Ownership transfer pattern (`00-WWDF/04-Architecture/18-ownership-transfer-pattern.md`), timeline pattern, dependency rules |
| Feature list | MOD-003 rows in `docs/FEATURE-LIST-WITH-USER-ROLES.md` |
| Reference | `docs/ULTIMATE-VISICORE-REFERENCE.md` |

---

## C. Role-Based Access

| Action | SA | CO | MG (target) | SE (target) | MO (future) |
|---|---|---|---|---|---|
| View relationship/ownership | Global read | Company read | Team read | Own + read of transferred | Planned |
| Assign a relationship | Yes (assign to self/company) | Yes | Yes | Yes | Planned |
| Request a transfer | No (platform role) | Yes (authorizes) | Yes (requests) | Yes (requests) | Planned |
| Approve a transfer | No | **Yes — V1 (BDR-019)** | No (future Manager) | No | No |
| Delete relationship/history | **No** | **No** | **No** | **No** | **No** |

**Restrictions**
- **Transfer authorization:** In V1 the **Company Owner** authorizes transfers
  (BDR-019); a future Manager approval flow is anticipated (PROC-003 open
  question, resolved to CO-in-V1).
- Only the current owner can act on a relationship; previous owners keep **read**
  access to preserved history after transfer (BDR-004, visibility matrix).
- Relationship status must match request; unauthorized transfer blocked.
- Tenant-scoped (BDR-021).

---

## D. Complete Feature Breakdown

### MVP (V1)

**F-012 — View current relationship**
- Behavior: Show who owns the relationship with a visitor and its status
  (MOD-003 purpose: answer "who owns this?").
- Rules: Exactly one active owner per relationship.
- Permissions: Read per visibility matrix (SA global, CO company, MG team,
  SE own + transferred-read).

**F-013 — Assign a relationship**
- Behavior: Assign a relationship to a marketer for a visitor (REQ-006, PROC-002).
- Rules: Assignment records marketer + visitor; change is captured as a
  System-Generated Timeline Event (MOD-003 definition row "Produces Timeline
  Events?"). (Exact assignment event wording not specified in source.)
- Permissions: CO, MG, SE (SA is platform; see matrix).

**F-014 — Transfer a relationship (preserve history)**
- Behavior: Transfer between marketers while preserving all history (REQ-007,
  PROC-003, BDR-004).
- Rules: Transfer is requested and authorized (CO in V1); complete Timeline
  preserved; **no history is lost or altered**; "Transfer" recorded as a
  System-Generated event (BDR-016).
- Permissions: Request — current team members; Authorize — CO (V1). Unauthorized
  transfer blocked (PROC-003 failure flow).

### Post-MVP / Future
- Relationship health score (MOD-003 definition "Future Enhancements").
- Manager-level transfer approval (PROC-003 open question; resolved in V1 to CO).
- Multi-relationship per visitor (Open Question M-3).

---

## E. Complete User Flow

**Assignment flow (PROC-002):**
```text
Marketer opens a visitor's workspace
↓
System shows current/no relationship (empty state)
↓
Marketer selects "Assign relationship" (assign to self or colleague)
↓
System validates permission (tenant + role scope) and target marketer
↓
System creates/updates Relationship ownership
↓
System writes System-Generated assignment Timeline Event
↓
New owner sees full relationship in their workspace
```

**Transfer flow (PROC-003):**
```text
Current owner (or manager within scope) requests a transfer
↓
System records the request (status = pending)
↓
Authorizer — Company Owner in V1 (BDR-019) — approves (or rejects)
↓
System applies transfer: new owner gains full history read+active
↓
System writes System-Generated "Transfer" Timeline Event (BDR-016)
↓
Complete timeline preserved; no data altered (BDR-004, REQ-007)
```

### Failure flows
- **Unauthorized transfer → blocked** (PROC-003 failure).
- **Out-of-scope read write/action → 403** (permission matrix).
- **Visitor/relationship not found → 404.**
- **Transfer in invalid state (already transferred/rejected)** → business error.

---

## F. Business Rules

1. **Relationships belong to marketers (BDR-003).** A visitor's contact belongs
   to a marketer/team; tenure drives timelines.
2. **Transfer preserves history (BDR-004 / REQ-007).** No history lost or altered
   on transfer; new owner sees full history; previous owner retains read access.
3. **Company Owner authorizes transfers in V1 (BDR-019).** Future Manager
   approval anticipated; no guaranteed auto-approval.
4. **History is never deleted (BDR-005)** — applies to relationships too.
5. **"Transfer" is a System-Generated event (BDR-016).**
6. **Assignment/transfer are Timeline-producing actions** (MOD-003 definition).
7. **One active owner per relationship (V1)**, per MOD-003 inputs
   (marketer, visitor, context). Multi-relationship per visitor is Open
   Question M-3.

---

## G. States and Lifecycle

Relationship lifecycle per ownership model / PROC-002 / PROC-003:

```text
Unassigned -> Assigned (owner) ->   transfer requested -> transferred
                                        |→ rejected
```

States observed from source:
- **Unassigned** — no owner yet (visitor has no relationship).
- **Assigned** — a marketer owns the relationship (active).
- **Transfer requested** — pending authorization (PROC-003 step 1).
- **Transferred** — new owner active; previous owner demo read.
- **(Rejected)** — transfer request not authorized.

| Attribute | Value |
|---|---|
| States | Unassigned, Assigned, Transfer requested, Transferred (rejected is a terminal request state) |
| Allowed transitions | Unassigned→Assigned; Assigned→Transfer requested; Transfer requested→Transferred (on approval); Transfer requested→Rejected |
| Forbidden transitions | Direct Unassigned→Transferred; deletion of any state; silent re-assignment without event |
| Trigger / Actor / Result | Per PROC-002/003; events recorded for each recorded change. |

> Source does not give an explicit lifecycle diagram; this table is derived
> from PROC-002/003 and BDR-003/004/019. Exact status enum values are not
> specified in source — do not invent beyond the states above.

---

## H. Timeline Integration

> Assigned event naming/format not fully specified in source (timeline schema
> Open Question M-15).

| Attribute | Value |
|---|---|
| Event type(s) produced | Assignment; **Transfer** (System-Generated, BDR-016); past events: assignment is System-generated per MOD-003 definition. |
| Trigger | Assign action (PROC-002); transfer approved (PROC-003). |
| User/System | Both assignment and transfer are System-Generated (MOD-003 "Produces Timeline Events? Yes — assignment/transfer as System-Generated events"). |
| Actor | System (authored by approver). |
| Visitor | The subject visitor. |
| Timestamp | ISO-8601 UTC. |
| History preservation | Full history preserved; append-only (BDR-004/005). |

---

## I. Audit Integration

- Transfer requests and authorizations are auditable actions (PROC-003, NFR-004);
  recorded as Timeline events and auditable via audit trail.
- Ownership changes are trail records; previous-owner read access remains for
  history (BDR-004).
- Corrections/audit philosophy per `100-audit-philosophy.md`.

---

## J. Data Model

Physical table: **`relationships`** (from `080-table-catalog.md`, ownership model)

| Element | Value |
|---|---|
| Primary key | `id` |
| Business identifier | Relationship (`REL`? — identifier strategy `060-identifier-strategy.md`: `REL-NNNNNN`) |
| Foreign keys | `tenant_id`; `visitor_vin` (VIN `VC-YYYY-NNNNNN`, BDR-018); `marketer_id`; `transfer_request*` as applicable |
| Tenant ownership | Yes |
| Soft delete | Not documented for relationships (history never deleted — BDR-005) |
| Archive | Per BDR-005, historical relationships retained |
| Versioning | Ownership history retained (previous owner read) |
| Audit | Transfer events |
| Indexes | `visitor_vin`; `marketer_id`; `tenant_id` |

> Do NOT redesign. Transfer-request state fields are not enumerated in source —
> model as columns needed to support PROC-003 states (request, approval).

---

## K. Laravel Implementation

| Component | Expected |
|---|---|
| Controllers | `Http/Controllers/Relationship/RelationshipController` (index, assign, transfer-request, transfer-approve). |
| Form Requests | AssignRelationshipRequest; TransferRequestRequest; TransferApproveRequest (authorizer). |
| Resources | `RelationshipResource` (rel, visitor_vin, marketer, status, transferred_from). |
| Services | `Relationship\\Services\\RelationshipService` (assign/transfer, state transitions, history preservation, event projection). |
| Models | `Relationship` (status enum), `RelationshipTransferRequest` (if separate). |
| Policies | `RelationshipPolicy` (view/assign/request/approve). |
| Middleware | Tenant scope. |
| Events | `RelationshipAssigned`, `RelationshipTransferred` → projected to Timeline. |
| Routes | `POST /visitors/{vin}/relationships`, `GET /visitors/{vin}/relationships`, `POST /relationships/{rel}/transfer` (+approval). |
| Views/components | Relationship drawer/panel in Visitor Workspace; transfer dialog. |
| Tests | See §Q. |

---

## L. API Specification

From `docs/07-API/150-relationship-api.md`.

| Method | URL | Purpose | Auth | Authz |
|---|---|---|---|---|
| POST | `/visitors/{vin}/relationships` | Assign relationship | Session/API key | In-scope marketer |
| GET | `/visitors/{vin}/relationships` | List relationships | Session/API key | Scope read |
| POST | `/relationships/{rel}/transfer` | Request transfer | Session/API key | Current owner / manager scope |

**Response (assign):** 201 schema per `050-request-response-standard.md`
(envelope `{data, meta}`), status change projected as System event.
**Errors:** 401, 403 (unauthorized transfer blocked; out-of-scope), 404 (vin/rel),
422 (invalid payload: target marketer, state), 429, 5xx.

> Approval endpoint not explicitly enumerated in `150-relationship-api.md`
> (only request is listed); if an explicit approve call is needed, follow
> `030-authorization.md` + permission matrix (CO in V1, BDR-019). Do not invent
> URL if not needed (UI action may be sufficient).

---

## M. Validation

- Validate `vin` and target marketer exist and are in-scope.
- Transfer validations: relationship active; request target marketer valid;
  authorizer has CO role in V1 (BDR-019).
- State machine: no transition allowed out of order (PROC-003).
- Payload rules per `050-request-response-standard.md`.

---

## N. Error Handling

401/403/404/409 (state conflict: transfer on non-active relationship)/
422/429/5xx per `060-error-handling.md`. Unauthorized transfer is blocked
(PROC-003 failure flow) → 403.

---

## O. Security

- Auth: session or API key.
- Authorization: view per visibility matrix; actions limited to in-scope users;
  transfer approval limited to CO in V1 (BDR-019).
- Tenant isolation at data layer (BDR-021).
- Previous-owner read-only after transfer (no action).
- Audit train preserved; no history deletion (BDR-004/005).

---

## P. UI/UX

Per `docs/06-UI-UX/080-visitor-workspace.md`, `010-workspace-specification.md`:
- Relationship panel/drawer: current owner, status, assign/transfer controls.
- Status clarity: who owns now + who can view read-only history.
- Empty state: no relationship assigned → prompt to assign (PROC-002 path).
- Transfer dialog: choose target marketer; confirmation of history preservation;
  approval required in V1.
- Notifications/confirmation on transfer as per notification philosophy.
- Responsive/accessible/loading states per standard.

---

## Q. Testing

- Unit: assignment state transition; transfer approval (BDR-019 CO-only);
  history preservation invariant.
- Feature: assign from empty state; request transfer; approve/reject; role checks.
- API: POST/GET per `150-relationship-api.md`.
- Authorization: CO-only approve (403 for others); out-of-scope.
- Validation: invalid target/state.
- Timeline: assignment/transfer events projected (System events).
- Audit: transfer authorization trail.
- Edge cases: unassigned visitor; reject then re-transfer; previous-owner read.

---

## R. Acceptance Criteria

- [ ] A relationship can be assigned to a marketer (REQ-006) via API/UI.
- [ ] Transfer preserves full history (REQ-007, BDR-004) — no data loss.
- [ ] Transfer requires authorization (BDR-019: CO in V1); unauthorized blocked.
- [ ] Assignment and Transfer appear as System-Generated Timeline Events.
- [ ] Previous owner retains read-only access to history after transfer.
- [ ] Tenant isolation holds (BDR-021).

---

## S. Developer Checklist

- **Backend:** RelationshipController; RelationshipService; Relationship model
  (+ transfer request/token if needed for PROC-003 states).
- **API:** assign/list/transfer endpoints per `150-relationship-api.md`.
- **Database:** `relationships` table (tenant, visitor_vin, marketer, status,
  transfer events).
- **Authorization:** in-scope view/action; CO-only approval (V1); transferred-read.
- **Timeline:** project System-Generated assignment/transfer events.
- **Audit:** transfer authorization trail.
- **Frontend:** relationship panel/drawer, transfer dialog, empty state.
- **Testing:** §Q.
- **Documentation:** update VISICORE-MODULE-INDEX.md.

---

## Module Dependencies

- **Depends on:** Modern visitor (MOD-001 Visitor Workspace provides context);
  Identity model (Marketer), Company Owner role (BDR-019).
- **Used by:** MOD-001 (relationship context), MOD-010 (aggregates), MOD-012
  (administration of roles? — via role definitions).
- **Produces:** Timeline Events (assignment, transfer — System events).
- **Consumes:** Visitor context (VIN); no other module events.

> No dependency cycles; relationship changes flow into Timeline one-way.

---

## Open Questions

| Question | Why it matters | Source documents checked | Possible impact |
|---|---|---|---|
| Multi-relationship per visitor? (M-3) | V1 single-owner model | MOD-003 definition Open Questions | Schema + UI |
| Explicit transfer-approval endpoint needed? | API surface | `150-relationship-api.md` only lists request | API design |
| Relationship status enum values | Data model | `080-table-catalog.md`, ownership model | Migration |
| Assignment event wording/schema | Timeline projection | MOD-003; M-15 | Timeline format |

---

*Source documents remain authoritative. Follow the BDR registry and the frozen
architecture (v1.0) when implementing.*