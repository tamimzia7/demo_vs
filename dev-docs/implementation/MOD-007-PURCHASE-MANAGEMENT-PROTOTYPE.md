# MOD-007 — Purchase Management Implementation Record

## 1. Feature
- MOD-007
- Purchase Management
- **Status:** Prototype Implemented

## 2. Source Traceability
| Concern | Source ID / Location |
|---|---|
| MOD definition | MOD-007 in `docs/05-Product-Blueprint/080-module-definition-records.md` |
| Requirements | REQ-014 (record purchase + advance lifecycle) |
| Business processes | PROC-008 (Purchase Recording) |
| BDRs | BDR-005 (history never deleted), BDR-006 (one visitor, many journeys), BDR-016 (System-Generated events) |
| NFRs | NFR-001 (permanence), NFR-004 (auditability) |
| Data model | Purchase entity; `purchases` table; Offering (MOD-009) relation (deferred) |
| API | `docs/07-API/220-purchase-api.md` |
| UI/UX | `docs/06-UI-UX/130-purchase-screen.md` |

## 3. Implemented Scope
- Purchase model with tenant and visitor relationships
- PurchaseService: record purchase, build timeline event summary, advance visitor lifecycle (Interested→Purchased→Repeat Customer)
- PurchaseController: index (list), store (record), show (detail)
- RecordPurchaseRequest: validates purchased_at (required, date, not future), amount (nullable, numeric, min:0), offering_id (nullable, integer)
- PurchaseResource: API resource with id, visitor_vin, offering_id, amount, purchased_at, created_at
- PurchasePolicy: authenticated users can view; authenticated users can create
- Purchase panel view (`resources/views/purchases/_panel.blade.php`) displayed in Visitor Workspace
- Timeline integration: "Purchase" System-Generated event on record; "Lifecycle Changed" System-Generated event on advance
- 16 Pest tests covering: record, record with offering, timeline events, lifecycle advance, repeat customer, list, detail, 404, validation (missing date, future date), empty list, API resource shape, unauthenticated denial, ordering

## 4. Files Created/Modified
| File | Action |
|---|---|
| `database/migrations/000013_create_purchases_table.php` | Created |
| `app/Models/Purchase.php` | Created |
| `app/Purchase/Services/PurchaseService.php` | Created |
| `app/Http/Controllers/Purchase/PurchaseController.php` | Created |
| `app/Http/Requests/Purchase/RecordPurchaseRequest.php` | Created |
| `app/Http/Resources/PurchaseResource.php` | Created |
| `app/Policies/PurchasePolicy.php` | Created |
| `resources/views/purchases/_panel.blade.php` | Created |
| `routes/web.php` | Modified (added purchases routes) |
| `app/Http/Controllers/Visitor/VisitorController.php` | Modified (added PurchaseService dependency) |
| `resources/views/visitors/workspace.blade.php` | Modified (added purchase panel include) |
| `tests/Feature/Purchase/PurchaseTest.php` | Created (16 tests) |

## 5. Verification
- **Tests:** 79 tests, 166 assertions — all passing
- **Pint:** All PHP files formatted
- **Build:** Vite build successful

## 6. Open Questions / Deferrals
- **MOD-009 (Offerings) not implemented:** `offering_id` column exists but `offering()` relationship removed; `exists:offerings,id` validation removed. Will be added when MOD-009 is implemented.
- **Refund/cancellation:** Not implemented per MOD-007 scope (Open Question M-7).
- **Auto-advance thresholds:** First purchase → Purchased; second+ → Repeat Customer (simple threshold; advanced rules deferred).
- **Purchase amount/fields schema:** Minimal implementation (purchased_at, amount, offering_id). Full schema deferred to MOD-009 integration.
