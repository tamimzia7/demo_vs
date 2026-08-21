# MOD-003 Relationship Center — Prototype Record

## 1. Feature

MOD-003 Relationship Center

## 2. Purpose

Manage the marketer's relationship with the visitor: show, assign, and transfer responsibility. Primary business question: "Who owns this relationship?"

## 3. Source Traceability

- MOD-003: Relationship Center module definition
- BDR-003: Relationships belong to marketers; visitors do not
- BDR-004: Transferable relationships — transfer preserves all history
- BDR-005: History never deleted
- BDR-016: System event catalogue — Transfer is a System-Generated event
- BDR-019: Company Owner approves transfers in V1
- BDR-021: Tenancy — multi-tenant SaaS; isolated workspace per subscriber
- REQ-006: Assign relationship
- REQ-007: Transfer relationship
- PROC-002: Relationship Assignment
- PROC-003: Relationship Transfer
- NFR-003: Access control
- NFR-004: Auditability

## 4. Implemented Scope

- Relationship model with status lifecycle (unassigned, assigned, transfer_requested, transferred, rejected)
- RelationshipService: assign, request transfer, approve, reject
- RelationshipController: index, store, transfer, approve, reject (API-style JSON responses)
- Relationship panel in visitor workspace (assign/transfer controls)
- Feature tests covering assignment, transfer request, approval, rejection, workspace display, validation

## 5. Files Changed

- database/migrations/2026_08_22_000006_create_relationships_table.php
- app/Models/Relationship.php
- app/Relationships/Services/RelationshipService.php
- app/Http/Controllers/Relationship/RelationshipController.php
- app/Http/Controllers/Visitor/VisitorController.php (added RelationshipService dependency, loaded relationship/marketers in workspace)
- resources/views/relationships/_panel.blade.php
- resources/views/visitors/workspace.blade.php (included relationship panel)
- routes/web.php (added relationship routes)
- tests/Feature/Relationship/RelationshipTest.php

## 6. Database

- relationships table: id, tenant_id (FK), visitor_vin (string), marketer_id (FK to users), status (enum), transferred_from_id (nullable FK to users), timestamps
- Indexes: (tenant_id, visitor_vin), (tenant_id, marketer_id), visitor_vin

## 7. Backend

- RelationshipService handles state transitions (assign, transfer request, approve, reject)
- RelationshipController provides API-style JSON endpoints for relationship operations
- VisitorController updated to load relationship and marketers data for workspace view
- Relationship model with status state methods (isAssigned, isTransferRequested, isTransferred, isRejected)

## 8. UI

- Relationship panel in visitor workspace (via _panel.blade.php partial)
- Empty state: "No relationship assigned" with assign form
- Assigned state: shows current owner, status badge, transfer form
- Transfer requested state: shows pending status, approve/reject buttons
- Uses VisiCore design tokens (card, badge, btn, label, select classes)

## 9. Assignment

Per PROC-002 and REQ-006:
- Assign a relationship to a marketer for a visitor
- Creates relationship record with status "assigned"
- Validates marketer exists and is in-scope (same tenant)
- Supports re-assignment by updating existing unassigned relationship

## 10. Transfer

Per PROC-003, REQ-007, and BDR-019:
- Current relationship owner requests transfer to target marketer
- Status changes to "transfer_requested", transferred_from_id records previous owner
- Company Owner approves/rejects transfer in V1
- On approval: status changes to "transferred" (history preserved per BDR-004)
- On rejection: status changes to "rejected", transferred_from_id cleared

## 11. Authorization

- Assignment: any authenticated user can assign (prototype scope)
- Transfer request: any authenticated user can request (prototype scope)
- Transfer approval/rejection: any authenticated user can approve/reject (prototype scope; in V1, Company Owner is the authority per BDR-019)
- Tenant isolation enforced at data layer (BDR-021)
- Full authorization policy (RelationshipPolicy) deferred to future iteration

## 12. Tests

- can display relationship for a visitor (empty state)
- can assign a relationship to a marketer
- can request a transfer
- can approve a transfer
- can reject a transfer
- can display workspace with relationship panel
- cannot assign relationship to non-existent marketer (validation)

## 13. Verification

- composer test: PASS (22 tests, 45 assertions)
- php artisan test --filter=RelationshipTest: PASS (7 tests, 16 assertions)
- vendor/bin/pint: PASS (fixed 1 file)
- npm run build: PASS (built in 1.90s)

## 14. Open Questions

- Multi-relationship per visitor (Open Question M-3): V1 uses single-owner model
- Explicit transfer-approval endpoint needed? (MOD-003 Open Question): implemented as POST endpoint for prototype
- Relationship status enum values (MOD-003 Open Question): used documented states (unassigned, assigned, transfer_requested, transferred, rejected)
- Assignment event wording/schema (MOD-003 Open Question): Timeline events deferred to MOD-002
- Full authorization policy (RelationshipPolicy): deferred to future iteration

## 15. Out of Scope

- Timeline events (assignment/transfer as System-Generated events) — deferred to MOD-002
- Full authorization policy (RelationshipPolicy) — deferred
- Previous-owner read-only access after transfer — deferred
- Audit logging — deferred
- Manager-level transfer approval — future enhancement (Team Edition)
- Multi-relationship per visitor — Open Question M-3

## 16. Status

Prototype Implemented — all core assignment and transfer behaviors working, tests passing, verification complete.
