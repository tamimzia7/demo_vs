# MOD-006 — Visit Management Implementation Record

## 1. Feature
- MOD-006
- Visit Management
- **Status:** Prototype Implemented

## 2. Source Traceability
| Concern | Source ID / Location |
|---|---|
| MOD definition | MOD-006 in `docs/05-Product-Blueprint/080-module-definition-records.md` |
| Requirements | REQ-012 (visits + participants → event), REQ-013 (promote participant) |
| Business processes | PROC-004 (Visit Logging), PROC-011 (Participant Promotion) |
| BDRs | BDR-007 (family members are Visit Participants), BDR-012 (every interaction → event) |
| NFRs | NFR-001 (permanence), NFR-004 (auditability) |
| Data model | Visit entity, Participant entity; `visits`, `visit_participants` |
| API | `docs/07-API/160-visit-api.md` |
| UI/UX | `docs/06-UI-UX/100-visit-screen.md`, `010-workspace-specification.md` |

## 3. Implemented Scope
- Visit model with relationships to Tenant and VisitParticipant
- VisitParticipant model with promotion tracking (`promoted_to_vin`)
- VisitService: create visit with participants, list visits, get visit detail, timeline event projection
- ParticipantPromotionService: promote participant to Visitor, create system timeline event
- VisitController: index (list), store (log visit), show (detail)
- ParticipantController: promote (participant to visitor)
- Visit form UI with participant entry (Alpine.js dynamic list)
- Visit list with participant chips and promote affordance
- Timeline integration: "Visit" User-Generated event on log, "Visitor Created" System-Generated event on promotion

## 4. Files Changed
| File | Action |
|---|---|
| `database/migrations/2026_08_22_000008_create_visits_table.php` | Created |
| `database/migrations/2026_08_22_000009_create_visit_participants_table.php` | Created |
| `app/Models/Visit.php` | Created |
| `app/Models/VisitParticipant.php` | Created |
| `app/Visits/Services/VisitService.php` | Created |
| `app/Visits/Services/ParticipantPromotionService.php` | Created |
| `app/Http/Controllers/Visit/VisitController.php` | Created |
| `app/Http\Controllers\Visit\ParticipantController.php` | Created |
| `resources/views/visits/_panel.blade.php` | Created |
| `resources/views/visitors/workspace.blade.php` | Modified |
| `app/Http/Controllers/Visitor/VisitorController.php` | Modified |
| `routes/web.php` | Modified |
| `tests/Feature/Visit/VisitTest.php` | Created |

## 5. Database Changes
### `visits` table
- `id` (bigint, PK)
- `tenant_id` (FK → tenants, cascade delete)
- `visitor_vin` (string)
- `visit_date` (date)
- `context` (string, nullable)
- `outcome` (string, nullable)
- `created_at`, `updated_at`
- Indexes: (`visitor_vin`, `created_at`), (`tenant_id`, `visitor_vin`), `visit_date`

### `visit_participants` table
- `id` (bigint, PK)
- `visit_id` (FK → visits, cascade delete)
- `tenant_id` (FK → tenants, cascade delete)
- `name` (string)
- `promoted_to_vin` (string, nullable)
- `created_at`, `updated_at`
- Indexes: (`visit_id`, `tenant_id`), `promoted_to_vin`

## 6. Routes / API
| Method | URL | Purpose | Controller |
|---|---|---|---|
| GET | `/visitors/{vin}/visits` | List visits | `VisitController@index` |
| POST | `/visitors/{vin}/visits` | Log a visit | `VisitController@store` |
| GET | `/visitors/{vin}/visits/{visitId}` | Visit detail | `VisitController@show` |
| POST | `/participants/{participantId}/promote` | Promote to visitor | `ParticipantController@promote` |

## 7. UI
- Visit Management panel in workspace with:
  - "Log Visit" toggle button
  - Visit form: date (required), context, outcome, participants (dynamic add/remove)
  - Visit list with expandable participant chips
  - "Promote" action on each participant (with confirm dialog)
  - Empty state: "No visits yet. Log the first visit to start tracking engagement."

## 8. Visit Participants
- Participants are recorded as accompanying parties in a visit
- Participants are distinct from Visitors (BDR-007)
- Participants do NOT become Visitors automatically
- Participant names are captured as strings (no separate identity record)

## 9. Participant Promotion
- Optional promotion of a Participant to a Visitor (REQ-013, PROC-011)
- New Visitor created with VIN (`VC-YYYY-NNNNNN`)
- Participant record updated with `promoted_to_vin`
- System-Generated "Visitor Created" timeline event created
- Cannot promote an already-promoted participant (422)

## 10. Timeline / Events
- **Visit logged:** User-Generated "Visit" event (PROC-004, BDR-012)
- **Participant promoted:** System-Generated "Visitor Created" event (PROC-011)
- Events appended via `TimelineService::appendEvent()`

## 11. Authorization / Tenant Isolation
- All queries scoped to `tenant_id` from authenticated user
- Tenant isolation enforced at service level (BDR-021)
- No cross-tenant data access possible

## 12. Tests
| Test | Description |
|---|---|
| `it_can_log_a_visit_with_participants` | Log visit with date, context, outcome, participants |
| `it_creates_a_timeline_event_when_logging_a_visit` | Verify User-Generated "Visit" event created |
| `it_can_list_visits_for_a_visitor` | List visits via API |
| `it_can_get_visit_detail` | Get visit detail via API |
| `it_can_promote_a_participant_to_a_visitor` | Promote participant, verify new Visitor created |
| `it_creates_a_system_event_when_promoting_a_participant` | Verify System-Generated event created |
| `it_cannot_promote_an_already_promoted_participant` | Verify 422 for duplicate promotion |

## 13. Verification
- `composer test`: 37 tests, 74 assertions — **PASS**
- `vendor/bin/pint`: **PASS** (fixed 2 files)
- `npm run build`: **PASS** (built in 2.25s)

## 14. Open Questions
| Question | Impact |
|---|---|
| Mandatory minimum visit fields | Currently: date required; context/outcome optional |
| Scheduled vs occurred event types | Not implemented (Open Question in MOD-006) |
| Promotion criteria/triggers (M-13) | No business rules beyond "participant exists and not already promoted" |
| Promotion reversible? | Not implemented (Open Question in PROC-011) |

## 15. Out of Scope
- Scheduling/calendar and route planning (future enhancement)
- Distinct scheduled-vs-occurred event types
- Promotion criteria/triggers (M-13)
- Promotion reversibility
- Edit/delete of logged visits (immutable history, BDR-005)
- Visit Policy / role-based authorization (deferred)
- Audit logging beyond timeline events (deferred)
- API endpoints per `160-visit-api.md` (web routes only for prototype)
