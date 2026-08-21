# MOD-002 Timeline — Prototype Record

## 1. Feature

MOD-002 Timeline

## 2. Purpose

The home inside the Visitor Workspace; the single source of visitor history, newest first. Primary business question: "What happened?"

## 3. Source Traceability

- MOD-002: Timeline module definition
- BDR-005: Immutable history — history is never deleted
- BDR-011: Timeline is the home — primary workspace, newest first, single source of history
- BDR-012: Every important interaction creates a Timeline Event
- BDR-013: User-Generated vs System-Generated events kept separate
- BDR-016: System event catalogue (Visitor Created, Transfer, etc.)
- REQ-002: Timeline home tab
- REQ-003: Newest first
- REQ-004: History never deleted
- REQ-005: Event classification
- PROC-005: Timeline Event Creation
- NFR-001: History permanence
- NFR-004: Auditability
- NFR-005: Responsive
- NFR-008: Scale with visitors/history
- NFR-009: Distinguish event types

## 4. Implemented Scope

- TimelineEvent model with type enum (user/system), immutable semantics
- TimelineService: append/project events, present newest-first, read-only operations
- TimelineController: index (list events), show (event detail) — read-only API
- Timeline tab in visitor workspace with real event display
- Feature tests covering display, listing, ordering, filtering, EVN generation, 404 handling

## 5. Timeline Rules

- **Newest-first**: Events displayed in descending created_at order (REQ-003)
- **Immutable history**: No edit/delete functionality; events append-only (BDR-005)
- **Event classification**: User-Generated vs System-Generated (BDR-013, REQ-005)
- **Visitor scope**: Events associated with specific visitor VIN
- **Tenant isolation**: Events scoped to tenant (BDR-021)
- **Archived markers**: Events can be archived (archived_at) but never hard-deleted

## 6. Files Changed

- database/migrations/2026_08_22_000007_create_timeline_events_table.php
- app/Models/TimelineEvent.php
- app/Timeline/Services/TimelineService.php
- app/Http/Controllers/Timeline/TimelineController.php
- app/Http/Controllers/Visitor/VisitorController.php (added TimelineService dependency, loaded timeline events)
- resources/views/visitors/workspace.blade.php (replaced placeholder with real timeline)
- routes/web.php (added timeline routes)
- tests/Feature/Timeline/TimelineTest.php

## 7. Database

- timeline_events table: id, evn (unique), tenant_id (FK), visitor_vin, type (enum: user/system), source, summary, archived_at (nullable), timestamps
- Indexes: (visitor_vin, created_at), (tenant_id, visitor_vin), type

## 8. Backend

- TimelineService handles append/project events, generateEvn, read-only operations
- TimelineController provides read-only API endpoints (index, show)
- VisitorController updated to load timeline events for workspace view
- TimelineEvent model with type state methods (isUserGenerated, isSystemGenerated, isArchived)

## 9. UI

- Timeline tab in visitor workspace (replaced placeholder)
- Empty state: "No activity yet" with message about future events
- Event cards with source, type badge (System/User), summary, timestamp
- User events shown with person icon (green), System events with lightning icon (accent)
- Events displayed in newest-first order

## 10. API

- GET /visitors/{vin}/timeline — List events (newest first, optional type filter)
- GET /visitors/{vin}/timeline/{evn} — Event detail
- Response format: { "data": [...] }
- Read-only: no create/update/delete endpoints (history immutable)

## 11. Tests

- can display timeline for a visitor (empty state)
- can list timeline events via API
- displays events newest first
- can get event detail via API
- can filter events by type
- generates EVN in correct format
- returns 404 for non-existent event

## 12. Verification

- composer test: PASS (29 tests, 59 assertions)
- php artisan test --filter=TimelineTest: PASS (7 tests, 14 assertions)
- vendor/bin/pint: PASS
- npm run build: PASS (built in 1.68s)

## 13. Open Questions

- Event correction-policy details + payload schema (M-15): correction format not implemented in prototype
- Mandatory event fields (M-4): minimal fields used (evn, type, source, summary)
- Intra-module async vs sync projection (M-19): synchronous projection used in prototype
- Visual marking of corrected/superseded events: not implemented in prototype
- Future event producers (Communication, Knowledge, Visits, Purchases, etc.): deferred to respective modules

## 14. Out of Scope

- Timeline event producers for future modules (MOD-004 Communication, MOD-005 Knowledge, MOD-006 Visits, MOD-007 Purchases, etc.)
- Correction mechanism (Open Question M-15)
- Event payload schema (Open Question M-15)
- Filter by channel/date (future enhancement F-011)
- Pagination/infinite loading
- Intelligence overlays
- Event detail expansion to related modules
- Undocumented event types

## 15. Status

Prototype Implemented — core timeline display, ordering, classification, and read-only API working, tests passing, verification complete.
