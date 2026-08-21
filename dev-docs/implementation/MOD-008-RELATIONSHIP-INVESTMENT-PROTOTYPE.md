# MOD-008 — Relationship Investment Implementation Record

## 1. Feature
- MOD-008
- Relationship Investment
- **Status:** Prototype Implemented

## 2. Source Traceability
| Concern | Source ID / Location |
|---|---|
| MOD definition | MOD-008 in `docs/05-Product-Blueprint/080-module-definition-records.md` |
| Requirements | REQ-015 (record relationship investments as Timeline Events) |
| BDRs | BDR-005 (immutable history), BDR-012 (every important interaction → event) |
| NFRs | NFR-001 (permanence), NFR-004 (auditability) |
| Data model | Expense entity; `expenses` table (`080-table-catalog.md` referenced) |
| API | `docs/07-API/210-expense-api.md` (referenced, not available in repo) |
| UI/UX | `docs/06-UI-UX/140-expense-screen.md` (referenced, not available in repo) |

## 3. Implemented Scope
- Expense model with tenant and visitor relationships
- InvestmentService: record expense, build timeline event summary ("Expense" User-Generated event)
- ExpenseController: index (list), store (log expense)
- LogExpenseRequest: validates category (required, string), amount (nullable, numeric, min:0), expense_date (required, date, not future)
- ExpenseResource: API resource with id, visitor_vin, category, amount, expense_date, created_at
- ExpensePolicy: authenticated users can view; authenticated users with SA/CO/marketer role can create
- Expense panel view (`resources/views/expenses/_panel.blade.php`) displayed in Visitor Workspace
- Timeline integration: "Expense" User-Generated event on record
- 11 Pest tests covering: log expense, log without amount, timeline event, list, empty list, API resource shape, validation (missing category, missing date, future date), unauthenticated denial, ordering

## 4. Files Created/Modified
| File | Action |
|---|---|
| `database/migrations/2026_08_22_000014_create_expenses_table.php` | Created |
| `app/Models/Expense.php` | Created |
| `app/Investment/Services/InvestmentService.php` | Created |
| `app/Http/Controllers/Investment/ExpenseController.php` | Created |
| `app/Http/Requests/Investment/LogExpenseRequest.php` | Created |
| `app/Http/Resources/ExpenseResource.php` | Created |
| `app/Policies/ExpensePolicy.php` | Created |
| `resources/views/expenses/_panel.blade.php` | Created |
| `routes/web.php` | Modified (added expense routes) |
| `app/Http/Controllers/Visitor/VisitorController.php` | Modified (added InvestmentService dependency) |
| `resources/views/visitors/workspace.blade.php` | Modified (added expense panel include) |
| `tests/Feature/Investment/ExpenseTest.php` | Created (11 tests) |

## 5. Database Changes
- New `expenses` table: `id`, `tenant_id` (FK), `visitor_vin`, `category` (string), `amount` (decimal 10,2 nullable), `expense_date` (date), `timestamps`
- Indexes: `visitor_vin` + `expense_date`; `tenant_id` + `visitor_vin`

## 6. Routes / API
| Method | URL | Purpose | Name |
|---|---|---|---|
| GET | `/visitors/{vin}/expenses` | List expenses for visitor | `visitors.expenses.index` |
| POST | `/visitors/{vin}/expenses` | Log a new expense | `visitors.expenses.store` |

## 7. UI
- Expense/effort form in Visitor Workspace panel (category, amount, date fields)
- Category picker implemented as free-text input (no documented enum)
- History list: newest first, showing category badge, date, and amount
- Empty state: "No investments logged yet." → "Log an expense"

## 8. Investment Behavior
- Recording an expense creates a "Expense" **User-Generated** Timeline Event (MOD-008 definition, REQ-015, BDR-012)
- History is immutable — no edit/delete of expenses (BDR-005)
- Record is logged with category, optional amount, and expense_date

## 9. Visitor / Relationship Association
- Expenses are associated to visitors via `visitor_vin` (MOD-008 depends on MOD-001)
- Relationship context referenced by MOD-008 definition dependencies but not explicitly wired in this prototype

## 10. Timeline / Events
- Event type: "Expense" — **User-Generated** (MOD-008 definition)
- Trigger: Log of expense
- Source: "Expense"
- Summary format: "Expense logged: {category}" with optional amount

## 11. Authorization / Tenant Isolation
- ExpensePolicy: create limited to `super_admin`, `company_owner`, `marketer` roles (per MOD-008 §C role access)
- All queries scoped to `tenant_id` (BDR-021)
- Unauthenticated access returns 403

## 12. Tests
- `tests/Feature/Investment/ExpenseTest.php` — 11 tests:
  - Log expense with all fields
  - Log expense without amount
  - Expense timeline event created
  - List expenses for visitor
  - Empty list
  - API resource shape
  - Validation: missing category (422)
  - Validation: missing expense_date (422)
  - Validation: future date (422)
  - Unauthenticated denial (403)
  - Ordering by expense_date desc

## 13. Verification
- **composer test:** 90 tests, 197 assertions — all passing
- **Pint:** formatted (import order fix in routes/web.php)
- **Build:** Vite build successful

## 14. Open Questions
| Question | Impact |
|---|---|
| What counts as an investment (time vs. money)? (M-10) | Determines if separate `amount` and `duration` fields are needed, or a single value field. Source: MOD-008 Open Questions |
| Expense category enum values | Category is implemented as free-text string. No documented enum exists. Source: MOD-008 §D F-030 says "do not invent beyond screen notes/Open Questions" |
| Dedicated Expense PROC | No formal process documented. Behavior is derived from MOD-008 §E and §P. Source: MOD-008 §B |
| Exact `expenses` table columns | Implementation uses documented indexes (`visitor_vin`, `expense_date`, `tenant_id`) and explicitly named fields (category, amount). Exact column spec from `080-table-catalog.md` not available in repo |
| `210-expense-api.md` contract | API contract referenced by MOD-008 §L but not available in repo. Implementation follows documented route pattern |

## 15. Out of Scope
- ROI analysis (MOD-008 "Future Enhancements")
- Expense categories enum (not documented)
- Time/duration tracking (Open Question M-10)
- Correction/reversal mechanism (Open Question M-15)
- Accounting, budgeting, financial reporting, invoices, payment processing, expense approval, receipt storage, mileage calculation, travel tracking, external integrations
