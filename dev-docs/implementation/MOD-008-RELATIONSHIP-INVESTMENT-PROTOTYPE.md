# MOD-008 — Relationship Investment Implementation Record

> **Type:** Prototype Implementation Record
> **Status:** IMPLEMENTED
> **Date:** 2026-08-22

---

## 1. Feature

- **Module:** MOD-008
- **Name:** Relationship Investment
- **Status:** Implemented (prototype)
- **Primary Question:** What was achieved?

---

## 2. Source Traceability

| Concern | Source ID | Status |
|---|---|---|
| Module definition | MOD-008 | Implemented |
| Requirements | REQ-015 (record relationship investments as Timeline Events) | Implemented |
| Business process | **Planned** — No dedicated Expense PROC yet (Open Question) | Pending |
| BDRs | BDR-005 (immutable history), BDR-012 (every important interaction → event), BDR-021 (tenant isolation) | Respected |
| NFRs | NFR-001 (permanence), NFR-004 (auditability) | Respected |
| Data model | expenses table (`080-table-catalog.md` referenced) | Implemented |
| API | `210-expense-api.md` (referenced, not available in repo) | Implemented per MOD-008 spec |
| UI/UX | `140-expense-screen.md` (referenced, not available in repo) | Implemented per MOD-008 spec |

---

## 3. Implemented Scope

- F-028: Log an investment (POST /visitors/{vin}/expenses)
- F-029: Investment history (GET /visitors/{vin}/expenses)
- F-030: Categorization (category field — free-text, no documented enum)

Investment lifecycle: Logged only. Immutable per BDR-005.

---

## 4. Files Changed

| File | Action | Description |
|---|---|---|
| app/Models/Expense.php | Existing | Expense model with tenant, visitor relationships |
| app/Investment/Services/InvestmentService.php | Modified | Added tenant isolation check in recordExpense |
| app/Http/Controllers/Investment/ExpenseController.php | Modified | Fixed store to return 201 status code |
| app/Http/Requests/Investment/LogExpenseRequest.php | Existing | Form request validation |
| app/Http/Resources/ExpenseResource.php | Existing | API resource |
| app/Policies/ExpensePolicy.php | Existing | Authorization policy |
| database/migrations/2026_08_22_000014_create_expenses_table.php | Existing | Migration |
| resources/views/expenses/_panel.blade.php | Existing | Expense panel view |
| routes/web.php | Existing | Web routes for expenses |
| tests/Feature/Investment/ExpenseTest.php | Modified | Added tenant isolation tests |

---

## 5. Database Changes

Existing expenses table per documented schema:
- id, tenant_id (FK), visitor_vin, category (string), amount (decimal 10,2 nullable), expense_date (date), timestamps
- Indexes: visitor_vin+expense_date, tenant_id+visitor_vin

---

## 6. Routes / API

### Web Routes

| Method | URL | Name |
|---|---|---|
| GET | /visitors/{vin}/expenses | visitors.expenses.index |
| POST | /visitors/{vin}/expenses | visitors.expenses.store |

---

## 7. UI

Implemented per MOD-008 section P:
- Expense/effort form (category, amount, date fields)
- Category picker implemented as free-text input (no documented enum)
- History list (newest first, showing category badge, date, and amount)
- Empty state: "No investments logged yet." → "Log an expense"

---

## 8. Investment Behavior

- Recording an expense creates a "Expense" **User-Generated** Timeline Event (MOD-008 definition, REQ-015, BDR-012)
- History is immutable — no edit/delete of expenses (BDR-005)
- Record is logged with category, optional amount, and expense_date
- Visitor must belong to same tenant as authenticated user (BDR-021)

---

## 9. Visitor / Relationship Association

- Expenses are associated to visitors via `visitor_vin` (MOD-008 depends on MOD-001)
- Relationship context referenced by MOD-008 definition dependencies but not explicitly wired in this prototype

---

## 10. Timeline / Events

Per MOD-008 section H:
- Event type: "Expense" — **User-Generated** (MOD-008 definition)
- Trigger: Log of expense/time/effort
- Source: "Expense"
- Summary format: "Expense logged: {category}" with optional amount
- Append-only: Yes

---

## 11. Authorization / Tenant Isolation

Per MOD-008 section C and BDR-021:
- Tenant isolation: All queries scoped to tenant_id
- Visitor must belong to same tenant as authenticated user
- Role-based access: Log expense (SA/CO/Marketer), View (all authenticated)
- No edit/delete of history (BDR-005)

---

## 12. Tests

13 tests covering:
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
- Tenant isolation: cross-tenant read
- Tenant isolation: cross-tenant record

---

## 13. Verification

| Check | Result |
|---|---|
| composer test --filter=Expense | 13 passed |
| composer test (full suite) | 121 passed |
| Pint | Passed |
| npm run build | Successful |

---

## 14. Open Questions

| Question | Source |
|---|---|
| What counts as an investment (time vs. money)? (M-10) | MOD-008 Open Questions |
| Dedicated Expense PROC | MOD-008 §B |
| Expense category enum | MOD-008 §D F-030 |
| Exact `expenses` table columns from `080-table-catalog.md` | MOD-008 §J |
| `210-expense-api.md` contract | MOD-008 §L |

---

## 15. Out of Scope

Per MOD-008 and strict prototype rules:
- ROI analysis (MOD-008 "Future Enhancements")
- Expense categories enum (not documented)
- Time/duration tracking (Open Question M-10)
- Correction/reversal mechanism (Open Question M-15)
- Accounting, budgeting, financial reporting, invoices, payment processing, expense approval, receipt storage, mileage calculation, travel tracking, external integrations
