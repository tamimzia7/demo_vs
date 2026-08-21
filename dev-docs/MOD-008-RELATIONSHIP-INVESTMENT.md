# MOD-008 — Relationship Investment

> **Type:** Module Implementation Record (development-facing)
> **Status:** PROTOTYPE_IMPLEMENTED · **Compiled:** 2026-08-18
> Source documents remain authoritative; nothing here invents business rules.
> **Caution:** No dedicated expense PROC exists yet — several expected behaviors
> below are Open Questions (see §F and §Open Questions).

---

## A. Module Overview

| Attribute | Value |
|---|---|
| **Module ID** | MOD-008 |
| **Module name** | Relationship Investment |
| **Purpose** | Track the effort and expense invested in a relationship. |
| **Business objective** | Quantify what was invested. |
| **Business meaning** | Reason: product philosophy — "Relationships are investments" (MOD-008 definition). Logging expenses/time/effort as **User-Generated** "Expense" Timeline events. |
| **Product Map position** | `VisiCore → Visitors → Visitor Workspace → Expense / Investment screen`. |
| **MVP/Post-MVP status** | **Post-MVP** (`020-mvp-definition.md` excludes MOD-008 from MVP). Build after MOD-007 per build order. |
| **Scope** | Record investments (expenses, time, effort) per visitor; categorize; produce "Expense" Timeline Event. |
| **Non-scope** | ROI analysis (future enhancement); definitive "what counts as an investment" rules (Open Question M-10); payment capture (see MOD-009/280). |

---

## B. Source Traceability

| Concern | Source ID / Location |
|---|---|
| MOD definition | MOD-008 in `docs/05-Product-Blueprint/080-module-definition-records.md` |
| Requirements | REQ-015 (record relationship investments as Timeline Events) in `req-mod-008-relationship-investment.md` |
| Business process | **Planned** — Expense logging is NOT yet a documented PROC (MOD-008, req note) |
| BDRs | BDR-012 (every important interaction → event); product philosophy "Relationships are investments" |
| NFRs | NFR-001 (permanence), NFR-004 (auditability) |
| Data model | Expense/Investment entity (`020-entity-catalog.md`); `expenses` table (`080-table-catalog.md`) |
| API | `docs/07-API/210-expense-api.md` |
| UI/UX | `docs/06-UI-UX/140-expense-screen.md` |
| Access control | Permission matrix, visibility matrix |
| Feature list | MOD-008 rows in `docs/FEATURE-LIST-WITH-USER-ROLES.md` |
| Reference | `docs/ULTIMATE-VISICORE-REFERENCE.md` |

---

## C. Role-Based Access

| Action | SA | CO | MG (target) | SE (target) | MO (future) |
|---|---|---|---|---|---|
| Log an expense/time/effort | Global (platform) | Yes (company) | Yes (team) | Yes (own) | Planned |
| View investments | Global read | Yes | Yes | Own + transferred-read | Planned |
| Delete/edit investments | **No** | **No** | **No** | **No** | **No** |

**Restrictions**
- History immutable (BDR-005); "Expense" is User-Generated (MOD-008 definition).
- Logging limited to in-scope marketers.
- Tenant-scoped (BDR-021).

---

## D. Complete Feature Breakdown

### MVP (V1)
**Not in MVP** (`020-mvp-definition.md`). MOD-008 lands post-MVP, after MOD-007.

### Planned (post-MVP)

**F-028 — Log an investment**
- Behavior: Record expenses, time, and effort invested in a relationship
  (REQ-015).
- Rules: Produces **"Expense"** Timeline Event — User-Generated (MOD-008
  definition). Details (categories, cost fields) per expense screen notes.
- Permissions: in-scope CO/MG/SE.

**F-029 — Investment history**
- Behavior: List/log investments per visitor (expense screen history).
- Rules: immutable; newest first.
- Permissions: scope read.

**F-030 — Categorization**
- Behavior: Category picker (expense/effort categories per expense screen).
- Rules: category set not enumerated in source — do not invent beyond screen
  notes/Open Questions.

### Post-MVP / Future
- ROI analysis (MOD-008 "Future Enhancements").
- What counts as an investment (time vs money) — Open Question M-10.

---

## E. Complete User Flow

```text
Marketer opens Expense/Investment screen for a visitor
↓
Adds expense/effort (cost/time, category)
↓
System validates permission + required fields
↓
System creates investment record
↓
System creates "Expense" Timeline Event (User-Generated)
↓
Display in history (newest first)
```

### Failure flows
- **Missing investment fields → 422** (exact required set is Open Question).
- **Out-of-scope → 403; visitor not found → 404.**

> No documented PROC yet — flow is derived from `140-expense-screen.md` and
> `210-expense-api.md`; treat as tentative until a PROC is written.

---

## F. Business Rules

1. **"Relationships are investments"** (product philosophy, MOD-008).
2. **Investments are recorded as events (REQ-015, BDR-012);** "Expense" is
   **User-Generated** (MOD-008 definition).
3. **History never deleted (BDR-005).**
4. **What counts as an investment (time vs. money) = Open Question M-10** — do
   not codify thresholds without a decision.
5. **No dedicated Expense PROC yet** — treat formal process as pending.

---

## G. States and Lifecycle

Expense/Investment record lifecycle (derived; no entity-catalog state diagram
documented for MOD-008):

```text
Logged -> immutable
```

| Attribute | Value |
|---|---|
| States | Logged → immutable |
| Allowed transitions | Log investment |
| Forbidden transitions | Edit/delete/hard-cancel |
| Trigger / Actor / Result | Marketer logs; event appended |
| Reversal/correction | Not documented — correction via TIMELINE policy (M-15) |

---

## H. Timeline Integration

| Attribute | Value |
|---|---|
| Event type produced | **"Expense"** — User-Generated (MOD-008 definition) |
| Trigger | Log of expense/time/effort |
| User/System | User |
| Actor | Marketer |
| Visitor | The invested-in visitor |
| Timestamp | ISO-8601 UTC |
| Append-only | Yes |
| Fields | Cost/time/effort per `210-expense-api.md` / expense-screen notes |

---

## I. Audit Integration

- Investment logging is auditable (NFR-004; `100-audit-philosophy.md`).
- Expense records visible in Timeline as User events (audit-relevant).

---

## J. Data Model

Physical table: **`expenses`** (from `080-table-catalog.md`)

| Element | Value |
|---|---|
| Primary key | `id` |
| Business identifier | Expense (`exp`? per `060-identifier-strategy.md` prefix style) |
| Foreign keys | `tenant_id`; `visitor_vin` |
| Tenant ownership | Yes |
| Soft delete | No (BDR-005) |
| Archive | N/A |
| Versioning | N/A |
| Audit | Yes |
| Indexes | `visitor_vin`, `expense_date`, `tenant_id` |
| Constraints | category/value fields per `080-table-catalog.md` (do not invent) |

> Confirm exact `expenses` columns in `080-table-catalog.md` / entity
> catalog before implementing.

---

## K. Laravel Implementation

| Component | Expected |
|---|---|
| Controllers | `Http/Controllers/Investment/ExpenseController` (log/list). |
| Form Requests | LogExpenseRequest (category, amount/time, date). |
| Resources | `ExpenseResource`. |
| Services | `Investment\\Services\\InvestmentService` (record + project "Expense" event). |
| Models | `Expense`. |
| Policies | `ExpensePolicy` (log/view). |
| Middleware | Tenant scope. |
| Events | `ExpenseLogged` → Timeline. |
| Routes | `POST /visitors/{vin}/expenses`, `GET /visitors/{vin}/expenses`. |
| Views/components | Expense/effort form, category picker, history list, empty state. |
| Tests | See §Q. |

---

## L. API Specification

From `docs/07-API/210-expense-api.md` (Draft).

| Method | URL | Purpose | Auth | Authz |
|---|---|---|---|---|
| POST | `/visitors/{vin}/expenses` | Log expense | Session/API key | In-scope marketer |
| GET | `/visitors/{vin}/expenses` | List | Session/API key | Scope read |

**Response:** 201 envelope `{data, meta}`.
**Errors:** 401, 403, 404, 422, 429, 5xx per standards.

---

## M. Validation

- Category/value/date validated per `210-expense-api.md`.
- VIN validated; scope authorized.
- Exact required-field set may change once the Expense PROC is written.

---

## N. Error Handling

401/403/404/422/429/5xx per `060-error-handling.md`.

---

## O. Security

- Auth: session or API key.
- Authorization: scope per visibility matrix; tenant isolation (BDR-021).
- No history deletion (BDR-005); amounts treated per NFR-003.

---

## P. UI/UX

Per `docs/06-UI-UX/140-expense-screen.md`:
- Expense/effort form; category picker; history.
- Add expense/effort; categorize.
- Saving creates an **Expense** event (User-Generated) on the Timeline.
- Empty state: "No investments logged yet." → "Log an expense".
- Responsive/accessible/loading per standard.

---

## Q. Testing

- Unit: investment record → Expense event projection.
- Feature: log expense/effort; category; history.
- API: POST/GET per `210-expense-api.md`.
- Authorization: out-of-scope 403.
- Timeline: User-Generated Expense event present.
- Audit: logging trail.
- Edge: empty history; missing category (422 per schema); transferred-read.

---

## R. Acceptance Criteria

- [ ] Investments (expenses, time, effort) recorded as **User-Generated**
      "Expense" Timeline Events (REQ-015, MOD-008 definition).
- [ ] History immutable (BDR-005); categorized per schema.
- [ ] Scope + tenant enforcement.

---

## S. Developer Checklist

- **Backend:** ExpenseController; InvestmentService; Expense model; policy.
- **API:** POST/GET per `210-expense-api.md`.
- **Database:** `expenses` per `080-table-catalog.md`.
- **Authorization:** scope + tenant middleware.
- **Timeline:** project User-Generated "Expense".
- **Frontend:** form, category picker, history, empty state.
- **Testing:** §Q.
- **Documentation:** update VISICORE-MODULE-INDEX.md (post-MVP module).

---

## Module Dependencies

- **Depends on:** MOD-001 (visitor/VIN), MOD-003 (relationship context —
  investments belong to a relationship, per MOD-008 definition dependencies).
- **Used by:** MOD-001 (expense screen), MOD-002 (events), MOD-010 (aggregates/
  ROI in future).
- **Produces:** Timeline Events ("Expense" — User).
- **Consumes:** Visitor VIN; optional relationship reference.

> No dependency cycles; post-MVP build.

---

## Open Questions

| Question | Why it matters | Source documents checked | Possible impact |
|---|---|---|---|
| What counts as an investment (time vs. money)? (M-10) | Schema + form | MOD-008 def Open Questions; expense screen | Field set |
| Dedicated Expense PROC | Formal process | REQ-015 note; expense screen; Open Questions | Behavior spec |
| Expense category enum | Category picker | `140-expense-screen.md` | Enum + UI |

---

*Source documents remain authoritative. Follow the BDR registry and the frozen
architecture (v1.0) when implementing.*