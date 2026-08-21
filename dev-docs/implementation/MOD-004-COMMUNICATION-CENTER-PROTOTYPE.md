# MOD-004 — Communication Center Implementation Record

## 1. Feature

- **Module:** MOD-004
- **Name:** Communication Center
- **Purpose:** Send and track communications across channels
- **Status:** Prototype (implemented and verified)

## 2. Source Traceability

| Concern | Source ID |
|---|---|
| Module definition | MOD-004 |
| Requirements | REQ-008 (record every outbound communication), REQ-009 (channels) |
| Business process | PROC-006 (Outbound Communication) |
| BDRs | BDR-014 (communications → Timeline Events), BDR-016 (System event catalogue) |
| Data model | `communications` table (080-table-catalog.md) |
| API | 180-communication-api.md |
| Access control | Permission matrix, visibility matrix |

## 3. Implemented Scope

### What was implemented:
- **F-015:** Send/log a communication via SMS, Email, Notice, Call, Meeting channels
- **F-016:** Communication history (newest first, immutable)
- **F-017:** Channel support with extensible enum pattern
- **Timeline integration:** System events for SMS/Email/Notice; User events for Call/Meeting
- **Authorization:** CommunicationPolicy for view/create/delete
- **Validation:** SendCommunicationRequest with channel-specific rules
- **API Resource:** CommunicationResource with standardized response shape
- **Channel Enum:** PHP 8.1+ backed enum for type safety

### What was NOT implemented (out of scope per documentation):
- Communication providers/adapters (JotpotSMS, email) — future enhancement
- Notice picker UI — no `notices` table exists yet (open question)
- Message templates — future enhancement
- Scheduling — future enhancement
- Inbound communication capture — future enhancement
- Send-failure recording — open question in PROC-006

## 4. Files Changed

### Created
- `app/Communication/Enums/Channel.php` — PHP enum for communication channels
- `app/Communication/Services/CommunicationService.php` — Business logic for communications
- `app/Http/Controllers/Communication/CommunicationController.php` — Thin controller
- `app/Http/Requests/Communication/SendCommunicationRequest.php` — Form request validation
- `app/Http/Resources/CommunicationResource.php` — API resource response
- `app/Models/Communication.php` — Eloquent model with Channel enum cast
- `app/Policies/CommunicationPolicy.php` — Authorization policy
- `database/migrations/2026_08_22_000010_create_communications_table.php` — Database schema
- `resources/views/communications/_panel.blade.php` — Communication Center UI panel
- `tests/Feature/Communication/CommunicationTest.php` — 19 Pest tests

### Modified
- `app/Http/Controllers/Visitor/VisitorController.php` — Added CommunicationService dependency
- `resources/views/visitors/workspace.blade.php` — Added communication panel include
- `routes/web.php` — Added communication routes

## 5. Database Changes

**Table: `communications`**
- `id` (bigint, PK)
- `tenant_id` (bigint, FK → tenants)
- `visitor_vin` (string) — indexed
- `channel` (enum: sms, email, notice, call, meeting) — indexed
- `content` (text, nullable)
- `notice_id` (bigint, nullable) — no FK constraint (no notices table yet)
- `sent_at` (timestamp)
- `created_at`, `updated_at` (timestamps)

**Indexes:**
- `(visitor_vin, created_at)`
- `(tenant_id, visitor_vin)`
- `channel`

## 6. Routes / API

| Method | URL | Name | Purpose |
|---|---|---|---|
| GET | `/visitors/{vin}/communications` | `visitors.communications.index` | List communications |
| POST | `/visitors/{vin}/communications` | `visitors.communications.store` | Send/log communication |
| GET | `/visitors/{vin}/communications/{communicationId}` | `visitors.communications.show` | Get communication detail |

**Response shape (CommunicationResource):**
```json
{
  "data": {
    "id": 1,
    "channel": "sms",
    "channel_label": "SMS",
    "content": "Hello, this is a test SMS.",
    "notice_id": null,
    "sent_at": "2026-08-22T00:00:00.000000Z",
    "type": "system",
    "created_at": "2026-08-22T00:00:00.000000Z"
  }
}
```

## 7. UI

- **Communication panel** in visitor workspace
- Channel picker with 5 buttons (SMS, Email, Notice, Call, Meeting)
- Content textarea (required for SMS/Email/Notice/Call; optional for Meeting)
- Button label changes: "Send" for system channels, "Log" for Call/Meeting
- History list with channel badges, timestamps, and content preview
- Empty state: "No communications yet. Send the first message to start tracking outreach."
- **Note:** Notice picker not implemented (no notices table exists yet)

## 8. Communication Channels

| Channel | Enum Value | Event Type | Event Source |
|---|---|---|---|
| SMS | `sms` | System | SMS Sent |
| Email | `email` | System | Email Sent |
| Notice | `notice` | System | Notice Sent |
| Phone Call | `call` | User | Call |
| Meeting | `meeting` | User | Meeting |

## 9. Provider / Adapter

- **Not implemented** — providers are external (JotPOT SMS/BulkSMS, email)
- Per MOD-004 spec: "providers are external (JotPOT SMS/BulkSMS, email — `260-jotpotsms-integration.md`, `270-email-integration.md`)"
- Prototype records communications directly without provider dispatch
- Future implementation will use driver/adapter pattern per WWDF

## 10. Timeline / Events

| Communication | Event Type | Event Source | Summary Format |
|---|---|---|---|
| SMS | System | SMS Sent | "SMS sent: {content}" |
| Email | System | Email Sent | "Email sent: {content}" |
| Notice | System | Notice Sent | "Notice sent: {content}" |
| Call | User | Call | "Phone Call sent: {content}" or "Phone Call logged" |
| Meeting | User | Meeting | "Meeting sent: {content}" or "Meeting logged" |

- Events are appended via `TimelineService::appendEvent()`
- No delete/edit of recorded communications (BDR-005 immutable history)

## 11. Authorization / Tenant Isolation

- **CommunicationPolicy:**
  - `viewAny` / `view`: Any authenticated user
  - `create`: Super Admin, Company Owner, Marketer
  - `update` / `delete`: Never allowed (immutable history)
- **Tenant isolation:** All queries scoped by `tenant_id`
- **BDR-021 enforced:** No cross-tenant data access

## 12. Tests

**19 Pest tests covering:**
1. Send SMS communication
2. Send email communication
3. Log call communication
4. Log meeting communication
5. System event for SMS
6. System event for email
7. User event for call
8. User event for meeting
9. List communications
10. Get communication detail
11. 404 for non-existent communication
12. Validation error for invalid channel
13. Validation error for missing content on SMS
14. Validation error for missing content on email
15. Validation error for missing content on call
16. Allows empty content for meeting
17. Empty list for visitor with no communications
18. Proper API resource shape
19. Denies unauthenticated access (403)

## 13. Verification

| Check | Result |
|---|---|
| `composer test` | ✅ 64 tests, 132 assertions — PASS |
| `vendor/bin/pint` | ✅ PASS |
| `npm run build` | ✅ PASS |

## 14. Open Questions

| Question | Impact |
|---|---|
| No `notices` table exists | Notice channel works but `notice_id` has no FK constraint; no notice picker UI |
| Notice entity definition | Spec says "referenced Notice" but no table defined in current modules |
| Send-failure recording | PROC-006 Open Question — not implemented |
| Provider credentials/config | Security/secrets management — future enhancement |

## 15. Out of Scope

Per MOD-004 spec, the following are NOT implemented:
- Communication providers/adapters (JotpotSMS, email)
- Message templates
- Scheduling
- Inbound communication capture
- Send-failure recording
- Notice picker UI (no notices table)
- Chat systems, WhatsApp, Facebook Messenger, social media messaging
- Push notifications, video calls, voice recording
- Contact-center features, campaigns, bulk messaging
- Marketing automation, analytics
- Undocumented search/filter systems
