# MOD-011 — Subscription

> **Type:** Module Implementation Record (development-facing)
> **Status:** NOT_STARTED · **Compiled:** 2026-08-18
> Source documents remain authoritative; nothing here invents business rules.
> **Caution:** Subscription business model/rules are **not approved** (REQ-018
> note, MOD-011 Open Question). Do not invent billing/renewal rules.

---

## A. Module Overview

| Attribute | Value |
|---|---|
| **Module ID** | MOD-011 |
| **Module name** | Subscription |
| **Purpose** | Manage recurring services and commitments. |
| **Business objective** | Track subscriptions. |
| **Business meaning** | Answer "what recurring commitments exist?"; a Subscription is recorded as a **System-Generated** Timeline Event (BDR-016). |
| **Product Map position** | `VisiCore → Visitors → Subscription` (subscription screen). |
| **MVP/Post-MVP status** | **Post-MVP** (`020-mvp-definition.md` excludes MOD-011). Build after MOD-009 per build order. |
| **Scope** | Create/list subscriptions; renew/cancel via PATCH; record "Subscription" System event. |
| **Non-scope** | Billing and renewal (future enhancement); subscription business model/rules (Open Question M-9); payment capture (future `280-`). |

---

## B. Source Traceability

| Concern | Source ID / Location |
|---|---|
| MOD definition | MOD-011 in `docs/05-Product-Blueprint/080-module-definition-records.md` |
| Requirements | REQ-018 (subscriptions as System-Generated Timeline Events) in `req-mod-011-subscription.md` |
| Business process | **None yet** — subscription process not documented (Planned) |
| BDRs | BDR-016 (Subscription is a System event) |
| NFRs | NFR-004 (auditability), NFR-001 (permanence) |
| Data model | Subscription entity; `subscriptions` table (`020-entity-catalog.md`, `080-table-catalog.md`) |
| API | `docs/07-API/230-subscription-api.md` (Draft) |
| UI/UX | `docs/06-UI-UX/170-subscription-screen.md` |
| Access control | Permission matrix, visibility matrix |
| Feature list | MOD-011 rows in `docs/FEATURE-LIST-WITH-USER-ROLES.md` |
| Reference | `docs/ULTIMATE-VISICORE-REFERENCE.md` |

---

## C. Role-Based Access

| Action | SA | CO | MG (target) | SE (target) | MO (future) |
|---|---|---|---|---|---|
| Create subscription | Global (platform) | Yes | Yes | Yes (own) | Planned |
| View subscriptions | Global read | Yes | Yes | Own + transferred-read | Planned |
| Renew/cancel (PATCH) | Global (platform) | Yes | Yes | Per matrix | Planned |
| Delete subscription | **No** (immutable history, BDR-005) | **No** | **No** | **No** | **No** |

**Restrictions**
- Subscription actions per permission matrix; tenant-scoped (BDR-021).
- History immutable (BDR-005).
- Business-model rules pending (M-9).

---

## D. Complete Feature Breakdown

### MVP (V1)
**Not in MVP** (`020-mvp-definition.md`). Post-MVP.

### Planned (post-MVP)

**F-037 — Create subscription**
- Behavior: Create a subscription (REQ-018).
- Rules: records terms; produces **"Subscription"** System-Generated Timeline
  Event (BDR-016).
- Permissions: in-scope.

**F-038 — Subscription list**
- Behavior: List subscriptions (`GET /subscriptions`).
- Rules: immutable records; newest first.
- Permissions: scope read.

**F-039 — Renew/cancel**
- Behavior: Renew or cancel a subscription (`PATCH /subscriptions/{sub}`).
- Rules: lifecycle transitions (renew/cancel) per pending business model; record
  events (exact event types for renewal/cancel pending).
- Permissions: in-scope.

### Post-MVP / Future
- Billing and renewal (MOD-011 "Future Enhancements").
- Subscription business model approved (M-9).

---

## E. Complete User Flow

```text
Marketer opens Subscription screen
↓
Creates a subscription (terms/offering reference)
↓
System validates permission + terms
↓
System records Subscription
↓
System creates "Subscription" Timeline Event (System-Generated)
↓
List/renew/cancel as needed (PATCH)
```

### Failure flows
- **Invalid terms/offering reference → 422.**
- **Out-of-scope → 403; subscription not found → 404.**
- **Renew/cancel on invalid state → business error (rules pending M-9).**

---

## F. Business Rules

1. **"Subscription" is a System-Generated event (BDR-016 / REQ-018).**
2. **History never deleted (BDR-005).**
3. **Subscription business model/rules not approved (Open Question M-9)** — do
   not implement billing/renewal rules without a decision.
4. **Subscription depends on an Offering (MOD-009)** — subscriptions attach to
   offerings/products (MOD-011 dependencies).

---

## G. States and Lifecycle

Subscription lifecycle **not documented** (no PROC; business model pending):

```text
Active -> Renewed / Cancelled   (proposed; NOT authoritative — pending M-9)
```

| Attribute | Value |
|---|---|
| States | To be defined (M-9) |
| Allowed transitions | To be defined — do not invent |
| Forbidden transitions | To be defined |
| Trigger / Actor / Result | To be defined |

> Do NOT hard-code subscription statuses/renewal rules until the business model
> is approved (M-9). Implement CRUD + event recording only.

---

## H. Timeline Integration

| Attribute | Value |
|---|---|
| Event type produced | **"Subscription"** — System-Generated (BDR-016; MOD-011 definition) |
| Trigger | Subscription create (and future renew/cancel events) |
| User/System | System |
| Actor | Marketer (creates) / System (records) |
| Visitor | Subscribing visitor |
| Timestamp | ISO-8601 UTC |
| Append-only | Yes |

---

## I. Audit Integration

- Subscription creation/renewal/cancellation are auditable (NFR-004) —
  Timeline events + audit trail (`100-audit-philosophy.md`).

---

## J. Data Model

Physical table: **`subscriptions`** (from `080-table-catalog.md`)

| Element | Value |
|---|---|
| Primary key | `id` |
| Business identifier | `sub` (SUB-NNNNNN per `060-identifier-strategy.md` / `230-subscription-api.md`) |
| Foreign keys | `tenant_id`; `visitor_vin`; `offering_id` (MOD-009) |
| Tenant ownership | Yes |
| Soft delete | No (BDR-005) |
| Archive | N/A |
| Versioning | N/A |
| Audit | Yes |
| Indexes | `visitor_vin`, `subscribed_at`, `tenant_id` |
| Constraints | offering reference; terms fields per schema (no business rules until M-9) |

> Confirm exact columns in `080-table-catalog.md`; do not add renewal/billing
> columns until M-9.

---

## K. Laravel Implementation

| Component | Expected |
|---|---|
| Controllers | `Http/Controllers/Subscription/SubscriptionController` (create/list/renew/cancel). |
| Form Requests | StoreSubscriptionRequest; UpdateSubscriptionRequest (renew/cancel). |
| Resources | `SubscriptionResource`. |
| Services | `Subscription\\Services\\SubscriptionService` (record, project System event). |
| Models | `Subscription`. |
| Policies | `SubscriptionPolicy`. |
| Middleware | Tenant scope. |
| Events | `SubscriptionCreated` → Timeline (future: renewed/cancelled per M-9). |
| Routes | `POST /subscriptions`, `GET /subscriptions`, `PATCH /subscriptions/{sub}`. |
| Views/components | Subscription form, list, renew/cancel actions. |
| Tests | See §Q. |

---

## L. API Specification

From `docs/07-API/230-subscription-api.md` (Draft).

| Method | URL | Purpose | Auth | Authz |
|---|---|---|---|---|
| POST | `/subscriptions` | Create | Session/API key | In-scope |
| GET | `/subscriptions` | List | Session/API key | Scope read |
| PATCH | `/subscriptions/{sub}` | Renew/cancel | Session/API key | In-scope |

**Response:** envelope `{data, meta}`; 201 on create.
**Errors:** 401, 403, 404 (sub), 422, 429, 5xx.

---

## M. Validation

- Terms/offering reference validated (MOD-009 dependency).
- No business-model validation until M-9 (do not invent).

---

## N. Error Handling

401/403/404/422/429/5xx per `060-error-handling.md`.

---

## O. Security

- Auth: session or API key.
- Authorization: per scope + matrix; tenant isolation (BDR-021).
- No history deletion (BDR-005).

---

## P. UI/UX

Per `docs/06-UI-UX/170-subscription-screen.md`:
- Subscription form (terms, offering reference), subscription list.
- Renew/cancel actions (pending business model).
- Empty state: "No subscriptions yet."
- Responsive/accessible/loading per standard.

---

## Q. Testing

- Unit: subscription create → System event; renew/cancel transitions (once
  defined).
- Feature: create/list; renew/cancel.
- API: POST/GET/PATCH per `230-subscription-api.md`.
- Authorization: out-of-scope 403.
- Timeline: "Subscription" System event.
- Audit: trail.
- Edge: invalid offering reference 422; empty list.

---

## R. Acceptance Criteria

- [ ] Subscriptions recorded as System-Generated Timeline Events (REQ-018,
      BDR-016).
- [ ] Create/list/renew/cancel endpoints work per API.
- [ ] History immutable; tenant isolation.
- [ ] No invented billing/renewal business rules (M-9 pending).

---

## S. Developer Checklist

- **Backend:** SubscriptionController; SubscriptionService; model; policy.
- **API:** POST/GET/PATCH per `230-subscription-api.md`.
- **Database:** `subscriptions` per `080-table-catalog.md`.
- **Authorization:** scope + tenant middleware.
- **Timeline:** project System "Subscription".
- **Frontend:** form, list, renew/cancel, empty state.
- **Testing:** §Q.
- **Documentation:** update VISICORE-MODULE-INDEX.md (post-MVP).

---

## Module Dependencies

- **Depends on:** MOD-001 (visitor/VIN), MOD-009 (offering reference).
- **Used by:** MOD-001 (subscription screen), MOD-002 (events), MOD-010
  (aggregates).
- **Produces:** Timeline Events ("Subscription" — System).
- **Consumes:** Offering reference; Visitor VIN.

> No dependency cycles; post-MVP. Business model pending (M-9).

---

## Open Questions

| Question | Why it matters | Source documents checked | Possible impact |
|---|---|---|---|
| Subscription business model/rules (M-9) | Renewal/cancel/billing | REQ-018 note; MOD-011 def Open Questions; PROC catalog (Planned) | Status model |
| Subscription process (Planned) | Formal workflow | `020-process-catalog.md` | Behavior spec |
| Terms schema (price/period) | Form + schema | `230-subscription-api.md`; `080-table-catalog.md` | Payload |

---

*Source documents remain authoritative. Follow the BDR registry and the frozen
architecture (v1.0) when implementing.*