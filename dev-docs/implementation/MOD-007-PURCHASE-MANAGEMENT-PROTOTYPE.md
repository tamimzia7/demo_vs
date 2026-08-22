# MOD-007 — Purchase Management Implementation Record

> **Type:** Prototype Implementation Record
> **Status:** IMPLEMENTED
> **Date:** 2026-08-22

---

## 1. Feature

- **Module:** MOD-007
- **Name:** Purchase Management
- **Status:** Implemented (prototype)
- **Primary Question:** What was achieved?

---

## 2. Source Traceability

| Concern | Source ID | Status |
|---|---|---|
| Module definition | MOD-007 | Implemented |
| Requirements | REQ-014 (record purchase + advance lifecycle) | Implemented |
| Business process | PROC-008 (Purchase Recording) | Implemented |
| BDRs | BDR-005 (history never deleted), BDR-006 (one visitor many journeys), BDR-016 (Purchase is System event) | Respected |
| Data model | purchases table | Implemented |
| API | POST/GET /visitors/{vin}/purchases | Implemented |
| UI/UX | Purchase panel in visitor workspace | Implemented |
| Access control | Permission matrix | Implemented |
| Lifecycle | Interested -> Purchased -> Repeat Customer | Implemented |

---

## 3. Implemented Scope

- F-025: Record a purchase (POST /visitors/{vin}/purchases)
- F-026: Purchase history (GET /visitors/{vin}/purchases)
- F-027: Lifecycle advance (first -> Purchased; multiple -> Repeat Customer)

Purchase lifecycle: Recorded only. No edit/delete per BDR-005.

---

## 4. Files Changed

| File | Action | Description |
|---|---|---|
| app/Models/Purchase.php | Existing | Purchase model with tenant, offering relationships |
| app/Http/Controllers/Purchase/PurchaseController.php | Modified | Fixed store to return 201 status code |
| app/Purchase/Services/PurchaseService.php | Modified | Added tenant isolation check in recordPurchase |
| app/Http/Requests/Purchase/RecordPurchaseRequest.php | Existing | Form request validation |
| app/Http/Resources/PurchaseResource.php | Existing | API resource |
| app/Policies/PurchasePolicy.php | Existing | Authorization policy |
| app/Models/Visitor.php | Existing | purchases() relationship |
| app/Models/Offering.php | Existing | purchases() relationship |
| routes/web.php | Existing | Web routes for purchases |
| resources/views/purchases/_panel.blade.php | Existing | Purchase panel view |
| tests/Feature/Purchase/PurchaseTest.php | Modified | Added tenant isolation and lifecycle tests |

---

## 5. Database Changes

Existing purchases table per documented schema:
- id, tenant_id, visitor_vin, offering_id (nullable), amount (nullable), purchased_at, timestamps
- Indexes: visitor_vin+purchased_at, tenant_id+visitor_vin

---

## 6. Routes / API

### Web Routes

| Method | URL | Name |
|---|---|---|
| GET | /visitors/{vin}/purchases | visitors.purchases.index |
| POST | /visitors/{vin}/purchases | visitors.purchases.store |
| GET | /visitors/{vin}/purchases/{purchaseId} | visitors.purchases.show |

---

## 7. UI

Implemented per MOD-007 section P:
- Purchase recording form (offering picker, amount, date)
- Purchase history list (newest first)
- Empty state: "No purchases yet."
- Lifecycle badge in visitor workspace

---

## 8. Purchase Behavior

- Record a completed purchase with optional offering reference
- System-Generated "Purchase" Timeline Event created
- Visitor lifecycle advances automatically
- History immutable (no edit/delete)

---

## 9. Purchase Lifecycle

Per MOD-007 section G and 040-entity-lifecycle.md:
- First purchase: Interested/Negotiating -> Purchased
- Subsequent purchases: Purchased -> Repeat Customer
- Lifecycle advance is system-driven (not manual)

Open Question: Exact auto-advance thresholds (when Repeat Customer?)

---

## 10. Timeline / Events

Per MOD-007 section H:
- Event type: "Purchase" (System-Generated)
- Trigger: Completed purchase
- Actor: Marketer (records) / System (event + lifecycle advance)
- Append-only: Yes

---

## 11. Authorization / Tenant Isolation

Per MOD-007 section C and BDR-021:
- Tenant isolation: All queries scoped to tenant_id
- Visitor must belong to same tenant as authenticated user
- Role-based access: Record (SA/CO/Marketer), View (all authenticated)
- No edit/delete of history (BDR-005)

---

## 12. Tests

19 tests covering:
- Purchase recording (basic, with offering reference)
- Timeline event creation (Purchase, Lifecycle Changed)
- Lifecycle advancement (Purchased, Repeat Customer)
- Purchase listing and detail
- Validation (missing purchased_at, future date)
- API resource shape
- Authentication/authorization
- Ordering by purchased_at desc
- Tenant isolation (cross-tenant read, cross-tenant record)

---

## 13. Verification

| Check | Result |
|---|---|
| composer test --filter=Purchase | 19 passed |
| composer test (full suite) | 119 passed |
| Pint | Passed |
| npm run build | Successful |

---

## 14. Open Questions

| Question | Source |
|---|---|
| Refund/cancellation handling (M-7) | PROC-008 Open Questions |
| Auto-advance rules (when Repeat Customer?) | PROC-008 Open Questions |
| Purchase amount/fields schema | 220-purchase-api.md |

---

## 15. Out of Scope

Per MOD-007 and strict prototype rules:
- Refund/cancellation handling
- Pipeline/forecasting
- Payment capture/integration
- Shopping cart
- Checkout
- Invoices
- Subscriptions
- Discounts/coupons
- Inventory
- Taxation
- Accounting
- Order fulfillment
- Shipping
