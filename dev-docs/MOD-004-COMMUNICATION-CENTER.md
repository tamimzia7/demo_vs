# MOD-004 — Communication Center

> **Type:** Module Implementation Record (development-facing)
> **Status:** NOT_STARTED · **Compiled:** 2026-08-18
> Source documents remain authoritative; nothing here invents business rules.

---

## A. Module Overview

| Attribute | Value |
|---|---|
| **Module ID** | MOD-004 |
| **Module name** | Communication Center |
| **Purpose** | Send and track communications across channels. |
| **Business objective** | Reach visitors and record every outreach. |
| **Business meaning** | Every outbound communication becomes a Timeline Event (BDR-014); "what did we say, and when?" is always answered. |
| **Product Map position** | `VisiCore → Visitors → Visitor Workspace → Communication Center`. |
| **MVP/Post-MVP status** | **MVP.** Outbound communication is in MVP scope (`020-mvp-definition.md`). |
| **Scope** | Create and send communications (SMS, Email, Notice, Phone Call, Meeting and future channels); record channels + referenced content (e.g., a Notice) as Timeline Events; view outreach history. |
| **Non-scope** | Providers are external (JotPOT SMS/BulkSMS, email — `260-jotpotsms-integration.md`, `270-email-integration.md`); templates/scheduling/inbound capture are future enhancements (MOD-004 definition). |

---

## B. Source Traceability

| Concern | Source ID / Location |
|---|---|
| MOD definition | MOD-004 in `docs/05-Product-Blueprint/080-module-definition-records.md` |
| Requirements | REQ-008 (record every outbound communication), REQ-009 (channels: SMS, Email, Notice, Phone Call, Meeting) in `req-mod-004-communication-center.md` |
| Business process | PROC-006 (Outbound Communication) in `docs/03-Business-Processes/` |
| BDRs | BDR-014 (all communications become Timeline Events), BDR-016 (System event catalogue) |
| NFRs | NFR-004 (auditability), NFR-005 (responsive), NFR-010 (integration) |
| Data model | Communication entity + `communications` table (`020-entity-catalog.md`, `080-table-catalog.md`) |
| API | `docs/07-API/180-communication-api.md`; SMS `260-jotpotsms-integration.md`; email `270-email-integration.md` |
| UI/UX | `docs/06-UI-UX/110-communication-center.md`, `010-workspace-specification.md`, `040-notification-philosophy.md` |
| Access control | Permission matrix, visibility matrix, authorization philosophy |
| Architecture | Driver/adapter pattern (`00-WWDF/04-Architecture/07-driver-adapter-pattern.md`), application event model (`060-application-event-model.md`) |
| Feature list | MOD-004 rows in `docs/FEATURE-LIST-WITH-USER-ROLES.md` |
| Reference | `docs/ULTIMATE-VISICORE-REFERENCE.md` |

---

## C. Role-Based Access

| Action | SA | CO | MG (target) | SE (target) | MO (future) |
|---|---|---|---|---|---|
| View communication history | Global read | Company read | Team read | Own + transferred-read | Planned |
| Send SMS / Email / Notice | No (platform role) | Yes | Yes | Yes | Planned |
| Log Phone Call / Meeting | Platform-level? (not documented) | Yes | Yes | Yes | Planned |
| Delete communication | **No** | **No** | **No** | **No** | **No** |

**Restrictions**
- Targeted contact visibility per visibility matrix; tenant-scoped (BDR-021).
- Send/Llog actions limited to in-scope marketers (permission matrix).
- History immutable — no delete (BDR-005/014).
- SA is a platform/global role, not a daily communicator; exact SA send
  permissions not enumerated in source.

---

## D. Complete Feature Breakdown

### MVP (V1)

**F-015 — Send/log a communication**
- Behavior: Initiate outbound communication via a channel (SMS, Email, Notice,
  Phone Call, Meeting or future channel) (REQ-009, PROC-006).
- Rules: On send/completion, Timeline Event created — System-Generated for
  SMS, Email, Notice; User-Generated for Phone Call, Meeting (PROC-006).
  Channel and referenced content (e.g., a Notice) recorded.
- Permissions: In-scope CO/MG/SE.

**F-016 — Communication history**
- Behavior: View outreach history for a visitor (REQ-008).
- Rules: history = Timeline Events; immutable; newest first.
- Permissions: Read per visibility matrix.

**F-017 — Channel support (extensible)**
- Behavior: Support SMS, Email, Notice, Phone Call, Meeting; future channels
  provisioned via adapter pattern (REQ-009; `07-api/` integrations).
- Rules: providers behind drivers/adapters (WWDF); notifications philosophy for
  user-visible messages (Notice).
- Permissions: as F-015.

### Post-MVP / Future
- Templates, scheduling, inbound capture (MOD-004 definition "Future Enhancements").
- Send-failure recording (Open Question).

---

## E. Complete User Flow

```text
Marketer opens Communication Center for a visitor
↓
Selects channel (SMS, Email, Notice, Call, Meeting) and composes/picks content
↓
System validates permission & content
↓
System sends via provider (drivers: JotPOT/BulkSMS, email adapter) or logs manual
↓
On send/completion System creates Timeline Event
  (SMS/Email/Notice → System-Generated; Call/Meeting → User-Generated)
↓
Event shows channel + referenced Notice/content + timestamp
```

### Failure flows
- **Send failure → handling not yet defined (PROC-006 Open Question).**
- **Provider failure → retry? not defined; driver must surface error.**
- **Authorization failure → 403; visitor not found → 404.**
- **Invalid payload (bad channel/content) → 422.**

---

## F. Business Rules

1. **All communications become Timeline Events (BDR-014 / REQ-008).** Every
   outbound communication is recorded; nothing at the margin is dropped.
2. **Channels:** SMS, Email, Notice, Phone Call, Meeting; extensible (REQ-009).
3. **Event classification:** SMS/Email/Notice sent → System-Generated; Phone
   Call/Meeting → User-Generated (PROC-006; BDR-013).
4. **System event catalogue (BDR-016):** SMS Sent, Email Sent, Notice Sent,
   etc. Authoritative.
5. **History immutable (BDR-005).** No delete/edit of recorded communications.
6. **Providers behind adapters (WWDF `07-driver-adapter-pattern.md`).** Provider
   choice does not leak into business logic.

---

## G. States and Lifecycle

Communication record lifecycle (derived from PROC-006; an explicit state
diagram is not documented in source):

```text
Initiated -> Sent (recorded as Timeline Event)    [System types]
          -> Logged (Call/Meeting)                 [User types]
          -> (Send failed — handling open)
```

| Attribute | Value |
|---|---|
| States | Initiated → Sent (system channels) / Logged (call/meeting); history states immutable |
| Allowed transitions | Initiated→Sent; Initiated→Logged; (Failure pipeline open question) |
| Forbidden transitions | Delete/edit of Sent/Logged records |
| Trigger / Actor / Result | Marketer initiates; System sends; event appended |

> "Sent failure" states not defined in source (PROC-006 Open Question) — do not
> invent a status enum without a decision.

---

## H. Timeline Integration

| Attribute | Value |
|---|---|
| Event types produced | SMS Sent, Email Sent, Notice Sent (System, BDR-016); Phone Call, Meeting (User, PROC-006) |
| Trigger | Send/completion of any outbound communication (PROC-006) |
| User/System | System for SMS/Email/Notice; User for Phone Call/Meeting |
| Actor | Marketer (initiator) / System (sender) |
| Visitor | Comms for the visitor's VIN |
| Timestamp | ISO-8601 UTC |
| Channel + content | Channel and referenced Notice/content recorded (PROC-006 step 3) |
| Append-only | Yes |

---

## I. Audit Integration

- Communications recorded are themselves audit-relevant (NFR-004) as Timeline
  events (audit philosophy `100-audit-philosophy.md`).
- Provider send status (Success/Failure) should be recorded — exact failure
  handling is open (PROC-006).

---

## J. Data Model

Physical table: **`communications`** (from `080-table-catalog.md`)

| Element | Value |
|---|---|
| Primary key | `id` |
| Business identifier | communication ID (`PREFIX-NNNNNN`; pattern per `060-identifier-strategy.md`) |
| Foreign keys | `tenant_id`; `visitor_vin`; `channel`; `notice_id` (referenced Notice); `provider*` status columns as needed |
| Tenant ownership | Yes |
| Soft delete | No (immutable history — BDR-005) |
| Archive | N/A (never removed) |
| Versioning | N/A (append-only) |
| Audit | Yes (recording of send) |
| Indexes | `visitor_vin`, `channel`, `created_at` |
| Constraints | channel ∈ {sms, email, notice, call, meeting, ...}; referenced Notice if channel=notice |

> Exact column set for provider status/failure not enumerated in source — follow
> `080-table-catalog.md`. Do not add provider-specific business columns without
> an adapter (`07-driver-adapter-pattern.md`).

---

## K. Laravel Implementation

| Component | Expected |
|---|---|
| Controllers | `Http/Controllers/Communication/CommunicationController` (send/log, history). |
| Form Requests | SendCommunicationRequest (channel, recipient VIN, content, notice_id). |
| Resources | `CommunicationResource` (id, channel, content/notice, sent_at, type). |
| Services | `Communication\\Services\\CommunicationService` (dispatch through channel drivers, project Timeline event). |
| Drivers | `Sms\` (`JotpotSms`/BulkSMS adapter), `Email\` (adapter) — per WWDF driver pattern and `260`/`270` integration docs. |
| Models | `Communication` (channel enum). |
| Policies | `CommunicationPolicy` (view scope, send). |
| Middleware | Tenant scope. |
| Events | `CommunicationSent` → Timeline projection. |
| Routes | `POST /visitors/{vin}/communications`, `GET /visitors/{vin}/communications`. |
| Views/components | Communication composer + channel picker; history list; notice picker. |
| Tests | See §Q. |

---

## L. API Specification

From `docs/07-API/180-communication-api.md`.

| Method | URL | Purpose | Auth | Authz |
|---|---|---|---|---|
| POST | `/visitors/{vin}/communications` | Send/log communication | Session/API key | In-scope marketer |
| GET | `/visitors/{vin}/communications` | Communication history | Session/API key | Scope read |

**Payload (POST):** channel (sms/email/notice/call/meeting), content or
referenced notice_id (for Notice channel), recipient VIN path.
**Response:** 201 envelope per `050-request-response-standard.md`.
SMS provider per `260-jotpotsms-integration.md`; email per `270-email-integration.md`.
**Errors:** 401, 403, 404 (vin), 422 (invalid channel/content), 429, 5xx.
Provider failures surfaced (exact handling open).

---

## M. Validation

- Valid channel; required content/recipient; notice_id must reference an
  existing in-scope Notice for notice channel.
- VIN format `VC-YYYY-NNNNNN` validated.
- Payload per `050-request-response-standard.md`.

---

## N. Error Handling

401/403/404/422/429/5xx per `060-error-handling.md`. Provider failures:
surface, retry semantics open (PROC-006). Idempotency key support if resending
(`080-idempotency.md`).

---

## O. Security

- Auth: session or API key.
- Authorization: send/log in-scope; history read per visibility matrix.
- Tenant isolation (BDR-021).
- Provider credentials held in config/env, never exposed via API.
- No deletion of history (BDR-005).

---

## P. UI/UX

Per `docs/06-UI-UX/110-communication-center.md`, `010-workspace-specification.md`:
- Composer with channel picker (SMS/Email/Notice/Call/Meeting), attachment of a
  Notice (referenced not copied — MOD-005/BDR-009).
- History list & success/failure indication.
- Empty state ("Send the first message") + notice picker integration.
- Notifications per `040-notification-philosophy.md`.
- Responsive/accessible/loading states per standard.

---

## Q. Testing

- Unit: channel routing; event classification (System vs User).
- Feature: send via each channel; log call/meeting; notice reference; history.
- API: POST/GET per `180-communication-api.md`.
- Authorization: out-of-scope 403; notice-reference validation 422.
- Timeline: SMS/Email/Notice → System events; Call/Meeting → User events.
- Driver tests: stub provider adapter (no real SMS in tests).
- Edge: send-failure path (status recording), empty history.

---

## R. Acceptance Criteria

- [ ] Marketer can send via SMS, Email, Notice, Phone Call, Meeting (REQ-009).
- [ ] Every outbound communication is recorded as a Timeline Event (REQ-008,
      BDR-014).
- [ ] Classification correct: SMS/Email/Notice System; Call/Meeting User.
- [ ] History viewable; immutable (no delete).
- [ ] Provider abstractions in place (adapters) — no provider leaking into
      business logic (WWDF).

---

## S. Developer Checklist

- **Backend:** CommunicationController; CommunicationService; channel drivers
  (SMS/email adapters); Communication model.
- **API:** POST/GET per `180-communication-api.md`.
- **Database:** `communications` table (tenant, visitor_vin, channel, content/
  notice_id, sent_at).
- **Authorization:** in-scope send + read; tenant middleware.
- **Timeline:** project events (SMS/Email/Notice → System; Call/Meeting → User).
- **Providers:** adapters per `260-`/`270-` integration docs (mock in tests).
- **Frontend:** composer + history + empty state.
- **Testing:** §Q.
- **Documentation:** update VISICORE-MODULE-INDEX.md.

---

## Module Dependencies

- **Depends on:** MOD-001 (visitor context/VIN), MOD-005 (Notice reference —
  referenced, not stored — BDR-009), external channel providers (adapters).
- **Used by:** MOD-001 (communication center link), MOD-002 (events), MOD-010
  (aggregates), MOD-013 (settings of provider config? — via adapters).
- **Produces:** Timeline Events (SMS/Email/Notice Sent → System; Call/Meeting →
  User).
- **Consumes:** Visitor VIN; Notice metadata.

> No dependency cycles — one-way flow of events into Timeline.

---

## Open Questions

| Question | Why it matters | Source documents checked | Possible impact |
|---|---|---|---|
| Inbound communication capture? (PROC-006) | Replies/replies capture | PROC-006 Open Questions | Scope + schema |
| Send-failure recording/handling | Error UX + status | PROC-006 Open Questions | Status enum + retry |
| Provider credentials/config mgmt (V1 transport) | Security/secrets | `260`/`270` integration docs | Env/config design |
| Notice channel: which message types? | User-Visible messaging | `040-notification-philosophy.md`, BDR-016 | Notice enum |

---

*Source documents remain authoritative. Follow the BDR registry and the frozen
architecture (v1.0) when implementing.*