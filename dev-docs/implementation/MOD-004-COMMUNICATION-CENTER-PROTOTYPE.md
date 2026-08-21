# MOD-004 — Communication Center Implementation Record

## 1. Feature
- MOD-004
- Communication Center
- **Status:** Prototype Implemented

## 2. Source Traceability
| Concern | Source ID / Location |
|---|---|
| MOD definition | MOD-004 in `docs/05-Product-Blueprint/080-module-definition-records.md` |
| Requirements | REQ-008 (record every outbound communication), REQ-009 (channels: SMS, Email, Notice, Phone Call, Meeting) |
| Business process | PROC-006 (Outbound Communication) |
| BDRs | BDR-014 (all communications become Timeline Events), BDR-016 (System event catalogue) |
| NFRs | NFR-004 (auditability), NFR-005 (responsive), NFR-010 (integration) |
| Data model | Communication entity; `communications` table |
| API | `docs/07-API/180-communication-api.md` |
| UI/UX | `docs/06-UI-UX/110-communication-center.md`, `010-workspace-specification.md` |

## 3. Implemented Scope
- Communication model with channel enum (sms/email/notice/call/meeting)
- CommunicationService: send/log communication, list communications, get communication detail, timeline event projection
- CommunicationController: index (history), store (send/log), show (detail)
- Communication composer UI with channel picker (SMS/Email/Notice/Call/Meeting)
- Communication history list with channel badges
- Timeline integration: SMS/Email/Notice → System-Generated events; Call/Meeting → User-Generated events

## 4. Files Changed
| File | Action |
|---|---|
| `database/migrations/2026_08_22_000010_create_communications_table.php` | Created |
| `app/Models/Communication.php` | Created |
| `app/Communication/Services/CommunicationService.php` | Created |
| `app/Http/Controllers/Communication/CommunicationController.php` | Created |
| `resources/views/communications/_panel.blade.php` | Created |
| `resources/views/visitors/workspace.blade.php` | Modified |
| `app/Http/Controllers/Visitor/VisitorController.php` | Modified |
| `routes/web.php` | Modified |
| `tests/Feature/Communication/CommunicationTest.php` | Created |

## 5. Database Changes
### `communications` table
- `id` (bigint, PK)
- `tenant_id` (FK → tenants, cascade delete)
- `visitor_vin` (string)
- `channel` (enum: sms, email, notice, call, meeting)
- `content` (text, nullable)
- `notice_id` (bigint, nullable)
- `sent_at` (timestamp)
- `created_at`, `updated_at`
- Indexes: (`visitor_vin`, `created_at`), (`tenant_id`, `visitor_vin`), `channel`

## 6. Routes / API
| Method | URL | Purpose | Controller |
|---|---|---|---|
| GET | `/visitors/{vin}/communications` | Communication history | `CommunicationController@index` |
| POST | `/visitors/{vin}/communications` | Send/log communication | `CommunicationController@store` |
| GET | `/visitors/{vin}/communications/{communicationId}` | Communication detail | `CommunicationController@show` |

## 7. UI
- Communication Center panel in workspace with:
  - "Send/Log Communication" toggle button
  - Channel picker (SMS, Email, Notice, Call, Meeting)
  - Content textarea
  - Send/Log button (text changes based on channel)
  - Communication history list with channel badges and timestamps
  - Empty state: "No communications yet. Send the first message to start tracking outreach."

## 8. Communication Channels
- **SMS**: System-Generated event, "SMS Sent" source
- **Email**: System-Generated event, "Email Sent" source
- **Notice**: System-Generated event, "Notice Sent" source
- **Call**: User-Generated event, "Call" source
- **Meeting**: User-Generated event, "Meeting" source

## 9. Timeline / Events
- **SMS/Email/Notice sent:** System-Generated event (PROC-006, BDR-014)
- **Call/Meeting logged:** User-Generated event (PROC-006)
- Events appended via `TimelineService::appendEvent()`

## 10. External Integrations
- Source identifier not defined in available documentation.
- Provider adapters (JotPOT SMS, email) are referenced in MOD-004 but not implemented in prototype.
- External provider calls MUST NOT be placed directly inside controllers or business services.
- Follow documented adapter/driver pattern when providers are implemented.

## 11. Authorization / Tenant Isolation
- All queries scoped to `tenant_id` from authenticated user
- Tenant isolation enforced at service level (BDR-021)
- No cross-tenant data access possible

## 12. Tests
| Test | Description |
|---|---|
| `it_can_send_an_SMS_communication` | Send SMS communication via API |
| `it_can_send_an_email_communication` | Send Email communication via API |
| `it_can_log_a_call_communication` | Log Call communication via API |
| `it_creates_a_system_event_for_SMS_communication` | Verify System-Generated event for SMS |
| `it_creates_a_user_event_for_call_communication` | Verify User-Generated event for Call |
| `it_can_list_communications_for_a_visitor` | List communications via API |
| `it_can_get_communication_detail` | Get communication detail via API |
| `it_returns_404_for_non_communication` | Verify 404 for non-existent communication |

## 13. Verification
- `composer test`: 45 tests, 87 assertions — **PASS**
- `vendor/bin/pint`: **PASS** (fixed 4 files)
- `npm run build`: **PASS** (built in 2.09s)

## 14. Open Questions
| Question | Impact |
|---|---|
| Send-failure recording/handling | Error UX + status (Open Question in PROC-006) |
| Provider credentials/config mgmt (V1 transport) | Security/secrets (Open Question) |
| Notice channel: which message types? | User-Visible messaging (Open Question) |
| Inbound communication capture? | Replies/replies capture (Open Question in PROC-006) |

## 15. Out of Scope
- Provider adapters (JotPOT SMS, email) — referenced but not implemented in prototype
- Templates, scheduling, inbound capture (future enhancement per MOD-004)
- Send-failure recording/handling (Open Question in PROC-006)
- Chat systems, WhatsApp, Messenger, social media integrations
- Inbox systems, campaign management, bulk messaging
- Automation, scheduled campaigns, marketing journeys
- Templates, analytics, AI-generated replies, contact-center functionality
- Communication Policy / role-based authorization (deferred)
- Audit logging beyond timeline events (deferred)
