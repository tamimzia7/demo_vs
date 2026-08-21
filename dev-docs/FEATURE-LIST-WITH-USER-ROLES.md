# VisiCore — Feature List with User Roles

> **Purpose:** The complete feature inventory of VisiCore, mapped to the modules that
> provide them and the **user roles** allowed to use each one. It is derived from the
> functional requirements (REQ-001…021), module definition records (MOD-001…013),
> processes (PROC-001…012), and the access-control matrices
> (`docs/04-Architecture/access-control/`).
>
> **Compiled:** 2026-08-17 · Source documents are authoritative in case of conflict.

---

## Role legend

| Code | Role | Status | V1 active? |
|---|---|---|---|
| **SA** | Super Admin | Current | Yes (platform) |
| **CO** | Company Owner / Marketer (subscriber) | Current | **Yes — the V1 user** |
| **SE** | Sales Executive | Current (target, daily user) | No — Team Edition |
| **MG** | Manager | Current (target, daily user) | No — Team Edition |
| **MO** | Marketing Officer | Future | No |
| **CS** | Client Staff | Future | No |

**Scope notes used below:**
- **Own** = the record/relationship is assigned to the acting user.
- **Team/Company/Global** = visibility scope for that role.
- **V1** = the feature ships in the Solo Edition (single-marketer).
- **Team** = feature becomes available when the multi-user Team Edition activates.
- Permissions follow the Permission Matrix (`040-permission-matrix.md`) — e.g. Sales
  Executives **request** a transfer; Managers **approve** (target) / Company Owner
  **approves** (V1). History is never deleted (BDR-005) — "delete" applies to
  non-history records only.

---

## Platform Foundation (pre-module)

| # | Feature | Description | Allowed roles | Edition |
|---|---|---|---|---|
| P-01 | Multi-tenant workspace | Each subscriber gets an isolated tenant; no cross-tenant data access | SA, CO | V1 (BDR-021) |
| P-02 | Authentication (portal login) | Session login with email/mobile + password | SA, CO, (SE, MG in Team) | V1 |
| P-03 | API keys | App ID/Secret for programmatic access; rotatable/revocable | SA, CO | V1 |
| P-04 | Role-based authorization | Actions gated by role + record scope (own/shared/transferred/global) | SA, CO, SE, MG | V1 (Solo) / Team |
| P-05 | VIN generation | Visitor Identity Number `VC-YYYY-NNNNNN`; permanent, immutable, never reused | System | V1 (BDR-018) |
| P-06 | Audit of system actions | All system actions recorded as Timeline Events | System (visible to SA, CO) | V1 (NFR-004) |

---

## MOD-001 — Visitor Workspace

| # | Feature | Description | Allowed roles | Edition | Source |
|---|---|---|---|---|---|
| F-001 | Visitor Workspace | Primary tabbed workspace for a single visitor; home base for all visitor-scoped modules | SA (global), CO (company), SE (own), MG (team) | V1 | REQ-001 |
| F-002 | Create a Visitor | Create a visitor the first time interest is expressed (any channel); generates "Visitor Created" event | SE (own), MG, CO, SA | V1 | REQ-001, PROC-001 |
| F-003 | View / search visitors | List and search visitors (by name, VIN, tags) | SA (global), CO (company), MG (team), SE (own) | V1 | REQ-001 |
| F-004 | Update visitor profile | Edit visitor identity details (not history) | SE (own), MG, CO, SA | V1 | Permission matrix |
| F-005 | Open Timeline as home | Workspace opens on Timeline tab by default | All roles | V1 | REQ-002, BDR-011 |
| F-006 | Archive a Visitor | Move an inactive visitor to Archived; history preserved & reactivatable | SE (own), MG, CO, SA | V1 | PROC-010 |
| F-007 | Restore an archived Visitor | Reactivate an archived visitor | MG, CO, SA (SE cannot) | V1 | Permission matrix |

---

## MOD-002 — Timeline

| # | Feature | Description | Allowed roles | Edition | Source |
|---|---|---|---|---|---|
| F-008 | View Timeline (newest first) | Chronological history of the visitor, newest first | SA (global), CO (company), MG (team), SE (own + read of transferred) | V1 | REQ-003 |
| F-009 | Immutable history | Timeline Events are never deleted; corrections append, never overwrite | System (enforced); view by all relevant roles | V1 | REQ-004, BDR-005 |
| F-010 | Event classification | Each event shown as User-Generated or System-Generated | All roles (view) | V1 | REQ-005, BDR-013 |
| F-011 | Filter / search events | Filter timeline by type/channel/date (future enhancement) | All roles | V1/future | MOD-002 future |

> Timeline is **view-only** for users — no Create/Update/Delete on history.

---

## MOD-003 — Relationship Center

| # | Feature | Description | Allowed roles | Edition | Source |
|---|---|---|---|---|---|
| F-012 | Assign a relationship | Assign a marketer's responsibility for a visitor within a context | SE (own), MG, CO, SA | V1 (single user) / Team | REQ-006, PROC-002 |
| F-013 | Request a relationship transfer | A marketer requests transfer of a relationship to another marketer | SE (request), MG (approve, target) | V1 / Team | REQ-007, PROC-003 |
| F-014 | Approve a relationship transfer | Authorize the transfer; full history preserved for the new owner | **CO (V1)**; MG (target Team) | V1 | REQ-007, BDR-019 |
| F-015 | Read transferred history | Previous marketer retains read access to the history (influence recorded) | SE (read only), MG, CO | V1 | BDR-003/004 |

---

## MOD-004 — Communication Center

| # | Feature | Description | Allowed roles | Edition | Source |
|---|---|---|---|---|---|
| F-016 | Send SMS | Send SMS via JotpotSMS adapter; recorded as "SMS Sent" event | SE (own), MG, CO, SA | V1 | REQ-008/009, PROC-006 |
| F-017 | Send Email | Send email via email adapter; recorded as "Email Sent" event | SE (own), MG, CO, SA | V1 | REQ-008/009 |
| F-018 | Send Notice | Send a formal notice; recorded as "Notice Sent" event | SE (own), MG, CO, SA | V1 | REQ-008/009 |
| F-019 | Log Phone Call | Log an outbound/inbound call as a User event | SE (own), MG, CO, SA | V1 | REQ-008/009 |
| F-020 | Log Meeting | Log a meeting as a User event | SE (own), MG, CO, SA | V1 | REQ-008/009 |
| F-021 | Communication history | View all communications sent to a visitor (from the Timeline) | SA (global), CO (company), MG (team), SE (own) | V1 | REQ-008 |

---

## MOD-005 — Knowledge Center

| # | Feature | Description | Allowed roles | Edition | Source |
|---|---|---|---|---|---|
| F-022 | Create a Knowledge Item | Register a referenced asset (metadata, link, version) — never the file itself | SE (own), MG, CO, SA | V1 | REQ-010, BDR-009 |
| F-023 | Share by VIN | Grant access to a Knowledge Item by Visitor Identity Number; recorded as "Knowledge Shared" | SE (own), MG, CO, SA | V1 | REQ-011, BDR-010 |
| F-024 | Revoke access | Withdraw access at any time; sharing history retained | SE (own), MG, CO, SA | V1 | REQ-011, BDR-010 |
| F-025 | View shared knowledge | See knowledge items shared with you / your scope | SA (global), CO (company), MG (team), SE (own + shared) | V1 | Visibility matrix |
| F-026 | Version knowledge items | Track versions of a Knowledge Item (future UI) | SE, MG, CO, SA | Future | MOD-005 future |

---

## MOD-006 — Visit Management

| # | Feature | Description | Allowed roles | Edition | Source |
|---|---|---|---|---|---|
| F-027 | Log a Visit | Record an in-person/scheduled visit with participants; creates "Visit" event | SE (own), MG, CO, SA | V1 | REQ-012, PROC-004 |
| F-028 | Add Visit Participants | Attach accompanying parties (e.g., family members) without creating visitors | SE (own), MG, CO, SA | V1 | REQ-012, BDR-007 |
| F-029 | Promote a Participant to Visitor | Optionally promote a participant to a full Visitor (linked to visit history) | SE (own), MG, CO, SA | V1 | REQ-013, PROC-011 |
| F-030 | View visit history | See past visits for a visitor | SA (global), CO (company), MG (team), SE (own) | V1 | REQ-012 |

---

## MOD-007 — Purchase Management

| # | Feature | Description | Allowed roles | Edition | Source |
|---|---|---|---|---|---|
| F-031 | Record a Purchase | Record a completed purchase; creates System "Purchase" event | SE (own), MG, CO, SA | V1 | REQ-014, PROC-008 |
| F-032 | Advance visitor lifecycle | Purchase advances lifecycle (Purchased / Repeat Customer) automatically | System (driven by F-031) | V1 | REQ-014, BDR-006/016 |
| F-033 | View purchase history | See purchases per visitor | SA (global), CO (company), MG (team), SE (own) | V1 | REQ-014 |
| F-034 | Purchase webhook | Notify external systems on purchase ("PurchaseRecorded") | System (configured by SA/CO) | V1 | API |

---

## MOD-008 — Relationship Investment

| # | Feature | Description | Allowed roles | Edition | Source |
|---|---|---|---|---|---|
| F-035 | Record an Expense / Effort | Log time/effort/expense invested in a relationship; creates "Expense" (User) event | SE (own), MG, CO, SA | Post-MVP (deferred in MVP) | REQ-015 |
| F-036 | Expense categories & history | Categorize and review investments (ROI view) | SE (own), MG (team), CO (company), SA (global) | Post-MVP | REQ-015 |

---

## MOD-009 — Offering Management

| # | Feature | Description | Allowed roles | Edition | Source |
|---|---|---|---|---|---|
| F-037 | Define Offerings / Products | Create and manage offerings and products (catalog) | CO, SA (Administer); MG, SE view/associate | Post-MVP (deferred in MVP) | REQ-016 |
| F-038 | Associate Offering with Visitor | Link offerings to visitor interest / purchases | SE, MG, CO, SA | Post-MVP | REQ-016 |
| F-039 | View offering catalog | Browse offerings company-wide | All roles (Company scope) | Post-MVP | Visibility matrix |

---

## MOD-010 — Reports & Intelligence

| # | Feature | Description | Allowed roles | Edition | Source |
|---|---|---|---|---|---|
| F-040 | Aggregated reports | Aggregate Timeline Events into reports (conversion, referrals, investment themes) | SA (global), CO (company), MG (team), SE (limited), MO (future) | Post-MVP (deferred) | REQ-017 |
| F-041 | Reporting dashboard | Read-only dashboards with filters (date/channel/owner); drill-through to visitors | SA, CO, MG, MO | Post-MVP | REQ-017 |
| F-042 | Export reports | Export reporting data | SA, CO, MG (team); SE cannot | Post-MVP | Permission matrix |

---

## MOD-011 — Subscription

| # | Feature | Description | Allowed roles | Edition | Source |
|---|---|---|---|---|---|
| F-043 | Record a Subscription | Record recurring commitment; creates System "Subscription" event | SE, MG, CO, SA | Post-MVP (business model not approved) | REQ-018 |
| F-044 | Manage subscriptions | Renew / cancel subscriptions; renewal webhooks | SE, MG, CO, SA | Post-MVP | REQ-018 |

---

## MOD-012 — Administration

| # | Feature | Description | Allowed roles | Edition | Source |
|---|---|---|---|---|---|
| F-045 | Manage users & roles | Add/remove marketers, assign roles | **CO, SA** only | V1 (users) / Team (roles) | REQ-019 |
| F-046 | Manage System Tags | Create and manage platform tags | **CO, SA** (Administer); others view | V1 | REQ-019/020 |
| F-047 | System Tags immutable | System Tags can never be deleted | System (enforced) | V1 | REQ-020, BDR-015 |
| F-048 | View system audit | Oversee system audit records | **SA (global), CO (company)**, MG (limited) | V1 | NFR-004 |

---

## MOD-013 — Settings

| # | Feature | Description | Allowed roles | Edition | Source |
|---|---|---|---|---|---|
| F-049 | Manage Custom Tags | Create/manage personal tags (creator-owned) | All user roles (creator only) | V1 | REQ-021, BDR-015 |
| F-050 | Workspace preferences | Personalize notifications, profile, security preferences | All user roles | V1 | REQ-021 |
| F-051 | Notification preferences | Configure which notifications surface (in-app / Timeline) | All user roles | V1 | REQ-021 |

---

## Cross-cutting capabilities

| # | Feature | Description | Allowed roles | Edition | Source |
|---|---|---|---|---|---|
| F-052 | Record User-Generated Event | Manually log any interaction (Call, Meeting, Note, Reminder, Discussion, Follow-up) | SE (own), MG, CO, SA | V1 | PROC-005 |
| F-053 | Create a Referral | Record a visitor referring someone new; creates new visitor + event | SE (own), MG, CO, SA | V1 | PROC-009 |
| F-054 | Tag visitors / relationships | Apply System or Custom tags for classification/filtering | SE (custom, own), MG, CO, SA (system) | V1 | PROC-012 |
| F-055 | Reminders / Follow-ups | Schedule a nudge to follow up (user events) | SE, MG, CO, SA | V1 / planned (PROC-013) | Glossary |
| F-056 | Notifications | Surface transfer received, knowledge shared/revoked, reminder due, approvals | All roles (scoped) | V1 | UI-UX philosophy |

---

## Feature availability summary by edition

| Edition | Features available |
|---|---|
| **V1 (Solo MVP)** | P-01…P-06, F-001…F-034 (minus Expense F-035/036), F-045…F-056. **Actors:** Super Admin + Company Owner/Marketer only. |
| **V1 Post-MVP** | Adds F-035/036 (Expense), F-037…F-042 (Offering, Reports), F-043/044 (Subscription). |
| **Team Edition (future)** | Sales Executive & Manager roles activate: own-scope relationship work, team supervision, transfer approval by Manager, role-based dashboards. |
| **Enterprise (future)** | Marketing Officer + Client Staff, ABAC, SSO, CRM/ERP sync, advanced reporting, group/public sharing, QR. |

---

*Cross-references: Permission Matrix `docs/04-Architecture/access-control/040-permission-matrix.md`, Visibility Matrix `050-visibility-matrix.md`, Role Definitions `020-role-definitions.md`, Identity Model `010-identity-model.md`, Requirement Index `docs/02-Requirements/020-requirement-index.md`, Module Records `docs/05-Product-Blueprint/080-module-definition-records.md`.*