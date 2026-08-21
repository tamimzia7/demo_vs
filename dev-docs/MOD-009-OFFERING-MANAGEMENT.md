# MOD-009 — Offering Management

> **Type:** Module Implementation Record (development-facing)
> **Status:** NOT_STARTED · **Compiled:** 2026-08-18
> Source documents remain authoritative; nothing here invents business rules.

---

## A. Module Overview

| Attribute | Value |
|---|---|
| **Module ID** | MOD-009 |
| **Module name** | Offering Management |
| **Purpose** | Manage the offerings and products visitors engage with. |
| **Business objective** | Organize what is being marketed and sold. |
| **Business meaning** | Answer "what are we offering this visitor?"; offerings organize visitors (BDR-002) and are **referenced by purchases** (MOD-007, PROC-008). |
| **Product Map position** | `VisiCore → Offerings` (catalog); referenced from Visitor Workspace / purchase screen. Placement (top-level vs. inside Administration) is an Open Question (offering screen). |
| **MVP/Post-MVP status** | **Post-MVP** per `020-mvp-definition.md` (OFFERING excluded from MVP; purchases reference a product/offering — a minimal catalog may be required; treat dependency carefully). |
| **Scope** | Define offerings/products; maintain catalog (create/list/update); associate with visitor interest and purchases (REQ-016). |
| **Non-scope** | Catalog & pricing enhancements (future); Offering lifecycle + pricing rules (Open Question M-21); direct Timeline writes (indirect via purchases — offering screen note). |

---

## B. Source Traceability

| Concern | Source ID / Location |
|---|---|
| MOD definition | MOD-009 in `docs/05-Product-Blueprint/080-module-definition-records.md` |
| Requirements | REQ-016 (define offerings/products; associate with interest + purchases) in `req-mod-009-offering-management.md` |
| Business process | PROC-008 (Purchase references a product/offering) |
| BDRs | BDR-002 (projects/offerings organize visitors), BDR-006 (many journeys) |
| NFRs | NFR-008 (scale), NFR-004 (auditability) |
| Data model | Offering entity (`020-entity-catalog.md`); `offerings` table (`080-table-catalog.md`); Offering lifecycle (`040-entity-lifecycle.md` — lifecycle out of scope here) |
| API | `docs/07-API/200-offering-api.md` |
| UI/UX | `docs/06-UI-UX/150-offering-screen.md` |
| Access control | Permission matrix, visibility matrix; Administration (MOD-012) dependency |
| Feature list | MOD-009 rows in `docs/FEATURE-LIST-WITH-USER-ROLES.md` |
| Reference | `docs/ULTIMATE-VISICORE-REFERENCE.md` |

---

## C. Role-Based Access

| Action | SA | CO | MG (target) | SE (target) | MO (future) |
|---|---|---|---|---|---|
| Create offering/product | Yes (platform catalog) | Yes (company) | Yes | Read/link only? (per matrix) | Planned |
| Update catalog (PATCH) | Yes | Yes | Yes | Per matrix | Planned |
| List offerings | Global read | Yes | Yes | Yes | Planned |
| Associate with visitor interest | Global (platform) | Yes | Yes | Yes (own) | Planned |
| Delete offering | **Per matrix only** | | | | |

**Restrictions**
- Catalog management governed by permission matrix (MOD-009 row) + MOD-012
  dependency; exact per-role edit rights per matrix.
- Associating with visitor interest limited to in-scope marketers.
- Tenant-scoped (BDR-021).

---

## D. Complete Feature Breakdown

### MVP (V1)
**Not in MVP scope per `020-mvp-definition.md`** — however, MOD-007 purchases
reference a Product/Offering (PROC-008 precondition "A Product/Offering is
involved"), so a **minimal offering reference** may be required for MVP
purchases. Verify MVP definition for the minimal catalog requirement — do not
assume full MOD-009 in MVP.

### Planned (post-MVP)

**F-031 — Define offerings/products**
- Behavior: Create offerings and products (REQ-016).
- Rules: catalog-based; offerings organize visitors (BDR-002).
- Permissions: per matrix + MOD-012.

**F-032 — Catalog management**
- Behavior: List/update offerings (`GET /offerings`, `PATCH /offerings/{off}`).
- Rules: catalog data; versioning/pricing per Open Questions (M-21).
- Permissions: per matrix.

**F-033 — Associate with visitor interest/purchases**
- Behavior: Associate offerings with visitor interest and purchases (REQ-016).
- Rules: referenced by purchases (PROC-008); no direct Timeline write (offering
  screen note — indirect via purchases).
- Permissions: in-scope.

### Post-MVP / Future
- Catalog and pricing (MOD-009 "Future Enhancements").
- Offering lifecycle/placement + pricing rules (Open Questions).

---

## E. Complete User Flow

```text
Marketer (or admin per MOD-012) opens Offerings
↓
Creates offering/product (name, metadata, price? per schema)
↓
System validates permission + fields
↓
System stores in catalog
↓
(At purchase time) purchase references the offering (MOD-007, PROC-008)
↓
Offerings list shows linked visitors / conversions
```

### Failure flows
- **Invalid offering data → 422.**
- **Out-of-scope → 403; offering not found → 404.**
- **Referenced offering missing at purchase → 422 (MOD-007 dependency).**

---

## F. Business Rules

1. **Offerings/products organize visitors (BDR-002).**
2. **Purchases reference an offering/product (PROC-008; MOD-007 dependency).**
3. **No direct Timeline write from Offering Management** (offering screen note) —
   offering events flow only through purchases.
4. **One visitor may engage multiple offerings over time (BDR-006).**
5. **Offering lifecycle/pricing rules = Open Question M-21** — do not invent.

---

## G. States and Lifecycle

Offering lifecycle is **explicitly out of scope / open** (`020-entity-catalog.md`
open question "Offering lifecycle/placement; pricing rules"; M-21):

| Attribute | Value |
|---|---|
| States | Draft → Active → (Archived?) — **not defined; Open Question M-21** |
| Allowed transitions | To be decided (M-21) |
| Forbidden transitions | To be decided — do not invent |
| Trigger / Actor / Result | To be decided |

> Do NOT model offering status without a decision (M-21). Catalog CRUD only.

---

## H. Timeline Integration

| Attribute | Value |
|---|---|
| Event type produced | **Indirect** — via purchases (offering screen note) |
| Trigger | Purchase referencing the offering (MOD-007) |
| User/System | Purchase events are System (BDR-016); offering itself produces no event |
| Append-only | Yes |

---

## I. Audit Integration

- Catalog changes (create/update) are auditable (NFR-004).
- Association with visitor interest tracked.

---

## J. Data Model

Physical table: **`offerings`** (from `080-table-catalog.md`)

| Element | Value |
|---|---|
| Primary key | `id` |
| Business identifier | `off` (OFF-NNNNNN per `060-identifier-strategy.md` / `200-offering-api.md`) |
| Foreign keys | `tenant_id` |
| Tenant ownership | Yes |
| Soft delete | per entity catalog (do not invent beyond source) |
| Archive | N/A |
| Versioning | Catalog versioning per M-21 (not yet) |
| Audit | Yes |
| Indexes | `tenant_id`, `active`, `name` |
| Constraints | name/metadata required per schema |
| Relations | referenced by `purchases.product_id/offering_id` |

> Confirm columns in `080-table-catalog.md`; do not add lifecycle/pricing
> columns until M-21 resolved.

---

## K. Laravel Implementation

| Component | Expected |
|---|---|
| Controllers | `Http/Controllers/Offering/OfferingController` (index/store/update). |
| Form Requests | StoreOfferingRequest; UpdateOfferingRequest. |
| Resources | `OfferingResource` (off, name, metadata). |
| Services | `Offering\\Services\\OfferingService` (catalog CRUD + visitor-interest association). |
| Models | `Offering`. |
| Policies | `OfferingPolicy` (per matrix + MOD-012). |
| Middleware | Tenant scope. |
| Events | none direct; purchase references offering (MOD-007). |
| Routes | `POST /offerings`, `GET /offerings`, `PATCH /offerings/{off}`; interest link endpoints per API (verify). |
| Views/components | Offering catalog list, offering form, visitor-interest link. |
| Tests | See §Q. |

---

## L. API Specification

From `docs/07-API/200-offering-api.md`.

| Method | URL | Purpose | Auth | Authz |
|---|---|---|---|---|
| POST | `/offerings` | Create offering/product | Session/API key | Per matrix |
| GET | `/offerings` | List | Session/API key | Scope read |
| PATCH | `/offerings/{off}` | Update catalog | Session/API key | Per matrix |

**Response:** envelope `{data, meta}`; 201 on create.
**Errors:** 401, 403, 404 (off), 422, 429, 5xx.

---

## M. Validation

- Required catalog fields per `200-offering-api.md`.
- No lifecycle/pricing fields until M-21.
- Association with visitor interest validates VIN + offering existence.

---

## N. Error Handling

401/403/404/422/429/5xx per `060-error-handling.md`. Referenced-offering
failures surfaced from MOD-007 as business errors.

---

## O. Security

- Auth: session or API key.
- Authorization: catalog per matrix; tenant isolation (BDR-021).
- Pricing metadata (if added later) treated per NFR-003.

---

## P. UI/UX

Per `docs/06-UI-UX/150-offering-screen.md`:
- Offering/product catalog; visitor-interest links.
- Active offerings; linked visitors; conversions per offering.
- Define offering/product; associate with visitor interest.
- Offerings referenced by purchases; no direct Timeline write.
- Empty state: "No offerings defined yet." → "Add an offering".
- Placement (top-level vs Administration) is an Open Question.

---

## Q. Testing

- Unit: offering CRUD; visitor-interest association.
- Feature: create/list/update; link to visitor interest.
- API: POST/GET/PATCH per `200-offering-api.md`.
- Authorization: per matrix (incl. MOD-012 admin paths).
- Integration: purchase references offering (MOD-007).
- Audit: catalog changes.
- Edge: duplicate name? (per schema); unknown offering reference 422.

---

## R. Acceptance Criteria

- [ ] Offerings/products can be defined and listed/updated (REQ-016).
- [ ] Offerings associate with visitor interest and are referenced by purchases
      (REQ-016, PROC-008).
- [ ] No direct Timeline write from Offering Management.
- [ ] Tenant isolation; no lifecycle/pricing invention (M-21 pending).

---

## S. Developer Checklist

- **Backend:** OfferingController; OfferingService; Offering model; policy.
- **API:** POST/GET/PATCH per `200-offering-api.md`.
- **Database:** `offerings` per `080-table-catalog.md`.
- **Authorization:** matrix + MOD-012; tenant middleware.
- **Integration:** purchases reference offerings (MOD-007).
- **Frontend:** catalog list, form, interest link, empty state.
- **Testing:** §Q.
- **Documentation:** update VISICORE-MODULE-INDEX.md (post-MVP).

---

## Module Dependencies

- **Depends on:** MOD-012 (Administration — catalog administration),
  Visitor context (interest links).
- **Used by:** MOD-007 (purchases reference offerings), MOD-001 (offering
  screen), MOD-010 (aggregates).
- **Produces:** none directly (indirect via purchases).
- **Consumes:** none (catalog CRUD); referenced by purchases.

> No dependency cycles; post-MVP.

---

## Open Questions

| Question | Why it matters | Source documents checked | Possible impact |
|---|---|---|---|
| Offering lifecycle/placement (M-21) | Status model + placement | `020-entity-catalog.md`; offering screen | Schema + UX |
| Pricing rules | Catalog fields | MOD-009 def Open Questions; `200-offering-api.md` | Field set |
| Minimal offering catalog for MVP purchases | MVP scope | `020-mvp-definition.md`; PROC-008 | Build order |

---

*Source documents remain authoritative. Follow the BDR registry and the frozen
architecture (v1.0) when implementing.*