# MOD-007 — Purchase Management

> **Type:** Module Implementation Record (development-facing)
> **Status:** NOT_STARTED · **Compiled:** 2026-08-18
> Source documents remain authoritative; nothing here invents business rules.

---

## A. Module Overview

| Attribute | Value |
|---|---|
| **Module ID** | MOD-007 |
| **Module name** | Purchase Management |
| **Purpose** | Record purchases and track conversions. |
| **Business objective** | Capture what was achieved. |
| **Business meaning** | A completed purchase is a **System-Generated** Timeline Event (BDR-016) that **advances the visitor lifecycle** (PROC-008); one visitor may have **many purchases over many journeys** (BDR-006). |
| **Product Map position** | `VisiCore → Visitors → Visitor Workspace → Purchase Management` within the purchase screen. |
| **MVP/Post-MVP status** | **MVP.** Purchase recording is in MVP scope (`020-mvp-definition.md`). |
| **Scope** | Record a completed purchase (plus Product/Offering reference); project System-Generated "Purchase" event; advance lifecycle. |
| **Non-scope** | Refund/cancellation handling (Open Question M-7); pipeline/forecasting (future enhancement); payment capture (payment integration is future — `280-future-payment-integration.md`). |

---

## B. Source Traceability

| Concern | Source ID / Location |
|---|---|
| MOD definition | MOD-007 in `docs/05-Product-Blueprint/080-module-definition-records.md` |
| Requirements | REQ-014 (record purchase + advance lifecycle) in `req-mod-007-purchase-management.md` |
| Business process | PROC-008 (Purchase Recording) |
| BDRs | BDR-005 (history never deleted), BDR-006 (one visitor, many journeys), BDR-016 (Purchase is System event) |
| Lifecycle | `docs/01-Business/040-visitor-lifecycle.md`; `docs/04-Architecture/data-model/040-entity-lifecycle.md` (Interested → Negotiating → Purchased → Repeat Customer → VIP → Archived) |
| Data model | Purchase entity (`020-entity-catalog.md`); `purchases` table; Offering (MOD-009) relation |
| API | `docs/07-API/220-purchase-api.md` |
| UI/UX | `docs/06-UI-UX/130-purchase-screen.md` |
| Access control | Permission matrix, visibility matrix |
| Feature list | MOD-007 rows in `docs/FEATURE-LIST-WITH-USER-ROLES.md` |
| Reference | `docs/ULTIMATE-VISICORE-REFERENCE.md` |

---

## C. Role-Based Access

| Action | SA | CO | MG (target) | SE (target) | MO (future) |
|---|---|---|---|---|---|
| Record a purchase | Global (platform) | Yes | Yes | Yes (own) | Planned |
| View purchases | Global read | Yes (company) | Yes (team) | Own + transferred-read | Planned |
| Edit/cancel purchase | **No** | **No** | **No** | **No** | **No** |

**Restrictions**
- Purchase recording limited to in-scope marketers (permission matrix).
- Lifecycle advances are **system-driven**; no manual lifecycle edits documented.
- Tenant-scoped (BDR-021); history immutable (BDR-005).

---

## D. Complete Feature Breakdown

### MVP (V1)

**F-025 — Record a purchase**
- Behavior: Record a completed purchase for a Product/Offering (REQ-014,
  PROC-008 step 1–2).
- Rules: System-Generated **"Purchase"** Timeline Event created; lifecycle
  advances (Interested/Negotiating → Purchased; multiple → Repeat Customer).
- Permissions: in-scope marketer/manager (PROC-008 actors: Marketer or Manager).

**F-026 — Purchase history**
- Behavior: List purchases per visitor (`GET /visitors/{vin}/purchases`).
- Rules: immutable; newest first; one visitor ↔ many purchases (BDR-006).
- Permissions: scope read.

**F-027 — Lifecycle advance**
- Behavior: Automatically advance visitor lifecycle on purchase (REQ-014,
  PROC-008 step 3; entity lifecycle `040-entity-lifecycle.md`).
- Rules: Purchased (first), Repeat Customer (more than one); exact auto-advance
  thresholds are Open Question (PROC-008); VIP/Archived transitions governed by
  separate sources.

### Post-MVP / Future
- Refund/cancellation handling (Open Question); pipeline/forecasting (MOD-007
  "Future Enhancements"); payment integration (`280-`).

---

## E. Complete User Flow

```text
Marketer opens a Visitor's Purchase screen
↓
Selects "Record purchase"; chooses Product/Offering
↓
System validates permission + product/offering + lifecycle context
↓
System records Purchase
↓
System creates System-Generated "Purchase" Timeline Event (BDR-016)
↓
System advances visitor lifecycle (Purchased / Repeat Customer)
(Future: refund/cancel flow per Open Question)
```

### Failure flows
- **Invalid Product/Offering → 422** (MOD-009 dependency).
- **Out-of-scope → 403; visitor not found → 404.**
- **Duplicate/unknown payload → 422; business-rule failure reported.**

---

## F. Business Rules

1. **"Purchase" is a System-Generated event (BDR-016 / REQ-014).**
2. **Lifecycle advances on purchase (PROC-008 step 3).** State flow is not
   strictly one-directional (`040-visitor-lifecycle.md`); auto-advance rules
   (e.g., Repeat Customer threshold) are Open Question.
3. **History never deleted (BDR-005)** — purchases immutable.
4. **One visitor may purchase multiple products over time (BDR-006)** — one
   Visitor ↔ one Timeline across many journeys.
5. **Purchase references a Product/Offering (MOD-009).** Lifecycle "Purchased"
   state reflects a completed purchase.

---

## G. States and Lifecycle

Purchase lifecycle per `040-entity-lifecycle.md`:

```text
Purchase: Recorded -> (lifecycle advance: Purchased → Repeat Customer)
Visitor: Interested -> Negotiating -> Purchased -> Referral -> Repeat Customer -> VIP -> Archived
(Any state → Archived, history preserved — BDR-005)
```

| Attribute | Value |
|---|---|
| Purchase states | Recorded → (advance on next purchase / repeat) |
| Visitor lifecycle | Interested → Negotiating → Purchased → Repeat Customer → VIP → Archived (not strictly one-directional) |
| Allowed transitions | Record purchase advances visitor lifecycle |
| Forbidden transitions | Manual/edit/delete of purchase; unwinding lifecycle via delete |
| Trigger / Actor / Result | Purchase completion (recorded by Marketer/Manager; System advances) |
| Refund/cancel | Not defined (Open Question M-7) — do not invent |

---

## H. Timeline Integration

| Attribute | Value |
|---|---|
| Event type produced | **"Purchase"** — System-Generated (BDR-016; PROC-008 step 2) |
| Trigger | Completed purchase (PROC-008) |
| User/System | System |
| Actor | Marketer (records) / System (event + lifecycle advance) |
| Visitor | The purchasing visitor |
| Timestamp | ISO-8601 UTC |
| Append-only | Yes |
| Lifecycle | Advanced as side effect (new System event could record it; exact format per M-15) |

---

## I. Audit Integration

- Purchase recording is an auditable action (NFR-004; `100-audit-philosophy.md`).
- Lifecycle advance is a System action; track as Timeline/Audit trail.
- Refund/cancel (future) will need its own audit trail (Open Question).

---

## J. Data Model

Physical table: **`purchases`** (from `080-table-catalog.md`)

| Element | Value |
|---|---|
| Primary key | `id` |
| Business identifier | Purchase (`pur`? per `060-identifier-strategy.md` prefix style) |
| Foreign keys | `tenant_id`; `visitor_vin`; `product_id`/`offering_id` (MOD-009) |
| Tenant ownership | Yes |
| Soft delete | No (immutable history, BDR-005) |
| Archive | N/A |
| Versioning | N/A |
| Audit | Yes |
| Indexes | `visitor_vin`, `purchased_at`, `tenant_id` |
| Constraints | Reference to a Product/Offering exists |
| Derived fields | none |

> Confirm `purchases` schema in `080-table-catalog.md` + `070-entity-to-table-mapping.md`.
> Do not add refund/cancel fields until that behavior is decided (M-7).

---

## K. Laravel Implementation

| Component | Expected |
|---|---|
| Controllers | `Http/Controllers/Purchase/PurchaseController` (record/list). |
| Form Requests | RecordPurchaseRequest (product/offering reference). |
| Resources | `PurchaseResource` (id, product/offering, amount? per schema, purchased_at). |
| Services | `Purchase\\Services\\PurchaseService` (record, project Purchase event, advance lifecycle). |
| Models | `Purchase` (+ relation to Offering model). |
| Policies | `PurchasePolicy` (record/view). |
| Middleware | Tenant scope. |
| Events | `PurchaseRecorded` → Timeline; lifecycle advance handled in service. |
| Routes | `POST /visitors/{vin}/purchases`, `GET /visitors/{vin}/purchases`. |
| Views/components | Purchase recording form (product/offering picker), purchase list, lifecycle badge. |
| Tests | See §Q. |

---

## L. API Specification

From `docs/07-API/220-purchase-api.md`.

| Method | URL | Purpose | Auth | Authz |
|---|---|---|---|---|
| POST | `/visitors/{vin}/purchases` | Record purchase | Session/API key | In-scope marketer/manager |
| GET | `/visitors/{vin}/purchases` | List purchases | Session/API key | Scope read |

**Payload (POST):** product/offering reference (per MOD-009), purchase date,
details per schema.
**Response:** 201 envelope `{data, meta}`; includes lifecycle after advance.
**Errors:** 401, 403, 404, 422 (invalid product/offering/date), 429, 5xx.

---

## M. Validation

- Product/Offering reference validated (MOD-009 dependency) — 422 on missing.
- Purchase date valid; no future non-sense dates beyond documented rules.
- Duplicate submissions handled via idempotency (`080-idempotency.md`).
- Envelope/errors per standards.

---

## N. Error Handling

401/403/404/422/429/5xx per `060-error-handling.md`. Lifecycle advance failures
reported as business errors; do not silently drop.

---

## O. Security

- Auth: session or API key.
- Authorization: record/view per scope; tenant isolation (BDR-021).
- No deletion/edit of history (BDR-005).
- Purchase amount/PII handled under NFR-003.

---

## P. UI/UX

Per `docs/06-UI-UX/130-purchase-screen.md`:
- Purchase form (product/offering picker, date), purchase history list.
- Lifecycle indicator (where the visitor stands in the lifecycle).
- Empty state: "No purchases yet" → record first purchase.
- Responsive/accessible/loading per standard.

---

## Q. Testing

- Unit: purchase record → Purchase event; lifecycle advance (Purchased /
  Repeat Customer thresholds).
- Feature: record purchase; list; lifecycle badge update.
- API: POST/GET per `220-purchase-api.md`.
- Authorization: out-of-scope 403.
- Timeline: System-Generated Purchase event present, newest first.
- Audit: recording trail.
- Edge: missing product/offering (422); second purchase (Repeat Customer);
  lifecycle edge (Referral state interactions).

---

## R. Acceptance Criteria

- [ ] Purchase recorded as System-Generated Timeline Event (REQ-014, BDR-016).
- [ ] Visitor lifecycle advances on purchase (first → Purchased; multiple →
      Repeat Customer).
- [ ] Purchase references a valid Product/Offering (MOD-009).
- [ ] History immutable (BDR-005); tenant isolation (BDR-021).

---

## S. Developer Checklist

- **Backend:** PurchaseController; PurchaseService; Purchase model; policy.
- **API:** record/list per `220-purchase-api.md`.
- **Database:** `purchases` (product/offering ref, tenant, visitor_vin).
- **Lifecycle:** advance logic in service (Purchased/Repeat Customer), per
  `040-entity-lifecycle.md` + `040-visitor-lifecycle.md`.
- **Timeline:** project System "Purchase".
- **Frontend:** purchase form, list, lifecycle badge, empty state.
- **Testing:** §Q.
- **Documentation:** update VISICORE-MODULE-INDEX.md.

---

## Module Dependencies

- **Depends on:** MOD-001 (visitor/VIN), **MOD-009 (Product/Offering)** — a
  purchase references an offering.
- **Used by:** MOD-001 (purchase screen), MOD-002 (events), MOD-010 (aggregates).
- **Produces:** Timeline Events ("Purchase" — System).
- **Consumes:** Offering/Product reference; Visitor VIN.

> No dependency cycles; purchase events flow into Timeline one-way.

---

## Open Questions

| Question | Why it matters | Source documents checked | Possible impact |
|---|---|---|---|
| Refund/cancellation handling (M-7) | Data model + events | PROC-008 Open Questions; MOD-007 def | Schema + event types |
| Auto-advance rules (when Repeat Customer?) | Lifecycle logic | PROC-008 Open Questions; `040-visitor-lifecycle.md` | Threshold rules |
| Purchase amount/fields schema | API + form | `220-purchase-api.md`; `080-table-catalog.md` | Payload |

---

*Source documents remain authoritative. Follow the BDR registry and the frozen
architecture (v1.0) when implementing.*