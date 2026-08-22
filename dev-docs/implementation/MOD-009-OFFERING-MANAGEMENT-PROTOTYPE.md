# MOD-009 — Offering Management Implementation Record

> **Type:** Prototype Implementation Record
> **Status:** IMPLEMENTED
> **Date:** 2026-08-22

---

## 1. Feature

- **Module:** MOD-009
- **Name:** Offering Management
- **Status:** Implemented (prototype)
- **Primary Question:** What are we offering?

---

## 2. Source Traceability

| Concern | Source ID | Status |
|---|---|---|
| Module definition | MOD-009 | Implemented |
| Requirements | REQ-016 | Implemented |
| Business process | PROC-008 | Integrated |
| BDRs | BDR-002, BDR-006 | Respected |
| NFRs | NFR-008, NFR-004 | Partially addressed |
| Data model | offerings table | Implemented |
| API | POST/GET/PATCH /offerings | Implemented |
| UI/UX | Offering catalog, form | Implemented |
| Access control | Permission matrix | Partially addressed |
| Lifecycle | M-21 (open question) | Deferred |

---

## 3. Implemented Scope

- F-031: Define offerings/products (create, list, update)
- F-032: Catalog management (GET /offerings, PATCH /offerings/{off})
- F-033: Associate with visitor interest/purchases (through Purchase integration)

Offering lifecycle (status transitions, pricing rules) is NOT implemented per Open Question M-21.

---

## 4. Files Changed

| File | Action | Description |
|---|---|---|
| app/Models/Offering.php | Modified | Added visitors() HasManyThrough relationship |
| app/Models/Purchase.php | Modified | Added offering() BelongsTo relationship |
| app/Models/Visitor.php | Modified | Added purchases() HasMany relationship |
| app/Offerings/Services/OfferingService.php | Modified | Added visitor-interest association methods |
| app/Http/Controllers/Offering/OfferingController.php | Modified | Added API methods (apiIndex, apiStore, apiUpdate) |
| app/Http/Resources/Offering/OfferingResource.php | Modified | Added timestamps to resource |
| app/Policies/OfferingPolicy.php | Modified | Fixed role checks to use in_array() pattern |
| routes/api.php | Created | API routes for offerings (v1) |
| bootstrap/app.php | Modified | Registered API routes |
| resources/views/offering/index.blade.php | Modified | Refactored to design system tokens |
| resources/views/offering/form.blade.php | Modified | Refactored to design system tokens |
| resources/views/components/sidebar.blade.php | Modified | Fixed route name reference |
| database/factories/OfferingFactory.php | Created | Factory for testing |
| tests/Feature/Offering/OfferingTest.php | Modified | Expanded to 26 tests |

---

## 5. Database Changes

No new migrations. Existing offerings table per documented schema.

---

## 6. Routes / API

### Web Routes

| Method | URL | Name |
|---|---|---|
| GET | /offerings | offerings.index |
| GET | /offerings/create | offerings.create |
| POST | /offerings | offerings.store |
| GET | /offerings/{off}/edit | offerings.edit |
| PUT | /offerings/{off} | offerings.update |
| DELETE | /offerings/{off} | offerings.destroy |

### API Routes (v1)

| Method | URL | Name |
|---|---|---|
| GET | /api/v1/offerings | api.offerings.index |
| POST | /api/v1/offerings | api.offerings.store |
| PATCH | /api/v1/offerings/{off} | api.offerings.update |

---

## 7. UI

- Offering catalog list with OFF, Name, Metadata, Status, Actions columns
- Offering form (create/edit) with Name, Metadata (JSON), Active toggle
- Empty state with "Add an offering" button
- Success notifications on create/update/delete
- Design system tokens used (card, btn, badge, etc.)

---

## 8. Offering Definition

- Fields: off (OFF-YYYY-NNNNNN), name, metadata (JSON), active, tenant_id
- Business identifier: off format
- No lifecycle/status transitions (Open Question M-21)
- No pricing fields (Open Question M-21)

---

## 9. Offering Association

- Offerings referenced by purchases (MOD-007, PROC-008)
- Visitor-interest association indirect through purchases
- No direct Timeline write from Offering Management

---

## 10. Timeline / Events

- Event type produced: Indirect (via purchases)
- Trigger: Purchase referencing the offering (MOD-007)
- No direct Offering events created

---

## 11. Authorization / Tenant Isolation

- Tenant isolation: All queries scoped to tenant_id
- Role-based access: View (SA/CO/MG/SE), Create (SA/CO/MG), Update (SA/CO/MG), Delete (SA/CO)
- Cross-tenant access returns 404

---

## 12. Tests

26 tests covering:
- Offering CRUD (create, read, update, soft delete)
- Search and filter functionality
- OFF identifier format validation
- Tenant isolation (list, update, API)
- Authorization (unauthenticated access denied)
- API contract (POST/GET/PATCH, response shape, 201 on create)
- Visitor-interest association through purchases
- 404 for non-existent offerings

---

## 13. Verification

| Check | Result |
|---|---|
| composer test --filter=Offering | 26 passed |
| Pint | Fixed (import ordering, parentheses) |
| npm run build | Successful |

---

## 14. Open Questions

| Question | Source |
|---|---|
| Offering lifecycle/placement (M-21) | MOD-009 open questions |
| Pricing rules | MOD-009 open questions |
| Minimal offering catalog for MVP purchases | MVP scope |
| Exact per-role edit rights per permission matrix | MOD-012 |

---

## 15. Out of Scope

Per MOD-009 and strict prototype rules:
- Full product catalog management
- Inventory / stock management
- Ecommerce / shopping cart / checkout
- Payment processing / pricing engines
- Discount engines / coupon systems
- Tax calculation / subscription billing
- Product reviews / undocumented statuses
- Undocumented approval workflows
- External marketplace integrations
- Offering lifecycle status transitions (M-21 pending)
- Pricing fields (M-21 pending)
