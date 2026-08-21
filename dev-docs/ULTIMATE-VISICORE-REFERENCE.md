# VisiCore — Ultimate Reference Document

> **Purpose:** The single master reference for the VisiCore project. It consolidates
> identity, business decisions, product structure, requirements, roles, architecture,
> processes, editions, and current status — derived from the authoritative `docs/`
> and `00-WWDF/` documentation. Where anything in this file conflicts with a source
> document, the **source document is authoritative** (BDRs are the top authority).
>
> **Compiled:** 2026-08-17 · **Project Status:** Planning → Implementation-ready (frozen v1.0)

---

## 1. Product Identity

| Attribute | Value |
|---|---|
| **Name** | VisiCore |
| **Category** | Customer Journey Intelligence Platform (**not** a CRM) |
| **Domain** | [visicoreapp.com](https://visicoreapp.com) |
| **Tagline** | *Every Visitor. Every Journey. One Smart Platform.* |
| **Version** | 0.1.0 (planning) |
| **Project type** | Multi-tenant Web SaaS |
| **Owner** | Win-Win Service Provider |
| **Core promise** | **Never lose a visitor.** Communication may stop; history remains. |

### One-liner

> VisiCore preserves every visitor relationship across every channel, forever, and
> turns each interaction into intelligence that helps marketers win the next sale.

### The Single Idea

> **The visitor is the center of the platform.** Projects and offerings organize
> visitors; relationships belong to marketers; but the visitor — identity, history,
> journey — is permanent, central, and never deleted.

### Vision

A world where **no company ever loses a visitor relationship**. History becomes the
company's most reliable competitive advantage: relationships outlast employees,
quarters, and campaigns.

### Mission / Primary Goals

1. **Never lose a visitor** — every interaction captured and preserved, regardless of channel.
2. **Immutable history** — a visitor's journey is never edited away or deleted.
3. **Transferable relationships** — relationships move between marketers without losing history.
4. **Learning loop** — every successful sale teaches the next sale.
5. **One problem, solved exceptionally** — focused on visitor-journey intelligence.

### Non-Goals

- Not a CRM; no generic CRM feature breadth.
- Not a bulk file storage system — large files live externally; VisiCore stores metadata, links, and permissions only.

---

## 2. The 8 Product Principles (Philosophy)

1. **Never lose a visitor.** A visitor never disappears once interest is expressed.
2. **Every communication creates knowledge.** Each call/visit/message is a unit of knowledge; silence is paused history, not erased history.
3. **Every visit is an investment.** Time, attention, and trust are spent on both sides.
4. **Relationships are investments.** Relationships are company assets, not personal possessions.
5. **History is never deleted.** Corrections are added, never overwritten or erased.
6. **Every successful sale teaches the next sale.** Closed deals are reusable lessons.
7. **We record influence, not ownership.** Attribution reflects real influence; ownership framing never gates history.
8. **Simple software solving one business problem exceptionally well.** Focus is a feature; complexity must be earned.

---

## 3. Golden Implementation Rules (invariants)

1. Build to the contract — never invent business rules.
2. Thin controllers; logic in services. External calls via adapters only.
3. Use **VIN** (`VC-YYYY-NNNNNN`); never expose internal DB keys (BDR-018).
4. History is immutable — corrections append, never overwrite or hard-delete (BDR-005).
5. Tenant isolation is non-negotiable — no cross-tenant data access (BDR-021).
6. Transfer approved by **Company Owner** in V1 (BDR-019).
7. Small, reviewable, traceable changes — one concern per PR.
8. Tests accompany every change — invariants are permanent regression tests.

---

## 4. Business Decision Records (BDR) — the authoritative contracts

The **21 approved BDRs** are binding. They must never be silently reverted; changes
supersede formally.

| ID | Decision | Core meaning |
|---|---|---|
| BDR-001 | Category | Customer Journey Intelligence Platform, not a CRM |
| BDR-002 | Visitor is the center | Projects/offerings organize visitors; visitor is permanent |
| BDR-003 | Relationship ownership | Relationships belong to marketers; visitors do not |
| BDR-004 | Transferable relationships | Transfer preserves all history |
| BDR-005 | Immutable history | History is never deleted; a visitor never disappears |
| BDR-006 | One visitor, many journeys | Multiple projects, visits, purchases, referrals, forever |
| BDR-007 | Participants | Family members are Visit Participants, promotable to Visitors |
| BDR-008 | Customers & users | Companies buy; Sales Executives & Managers use daily; Marketing Officers future |
| BDR-009 | Referenced knowledge | Metadata/permission/history/version/link only; no large files |
| BDR-010 | Share by VIN | Grant/revoke Knowledge Item access by Visitor Identity Number |
| BDR-011 | Timeline is the home | Timeline = primary workspace; not a module; newest first; single source of history |
| BDR-012 | Events | Every important interaction creates a Timeline Event |
| BDR-013 | Event types | User-Generated vs System-Generated events kept separate |
| BDR-014 | Communications → events | SMS, Email, Notice, Knowledge Sharing, Call, Meeting all become events |
| BDR-015 | Tagging | System Tags (immutable) vs Custom Tags (creator-owned) |
| BDR-016 | System event catalogue | SMS Sent, Email Sent, Knowledge Shared, Purchase, Transfer, Subscription, Visitor Created, Notice Sent, etc. |
| BDR-017 | Manager role | Manager is a current role (target state); activates in Team Edition |
| BDR-018 | VIN format | `VC-YYYY-NNNNNN`; permanent, immutable, sortable, QR-ready |
| BDR-019 | Transfer approval | Company Owner approves transfers in V1; Manager later |
| BDR-020 | Access-control V1 | Super Admin + Company Owner/Marketer; ABAC planned (not V1) |
| BDR-021 | Tenancy | Multi-tenant SaaS; isolated workspace per subscriber; solo→team growth |

---

## 5. People, Roles & Identity

### 5.1 System users (people who log in)

| Identity | Status | Role summary | Owns | Never owns |
|---|---|---|---|---|
| **Super Admin** | Current | Governs the whole platform; companies, system tags, global oversight, audit | Platform configuration & system tags | A specific visitor's relationship |
| **Company Owner** | Current (V1 active) | Owns the company account & data; user management; final authority; approves transfers in V1 | Company account & aggregate visitor data | Individual relationships to the exclusion of marketers |
| **Sales Executive** | Current (daily user, Team) | Owns and nurtures relationships day to day; calls, visits, comms, knowledge, purchases, expenses | Assigned relationships (not visitors) | The visitor record or its history |
| **Manager** | Current (target; Team) | Supervises relationships & staff; approves transfers; reassigns | Oversight of team's relationships | Underlying visitor records |
| **Marketing Officer** | Future | Leverages historical intelligence; analysis, segments, campaigns | Analysis & campaigns | Visitor relationship responsibility |
| **Client Staff** | Future | Scoped operational tasks within a client boundary | Scoped records in client context | Cross-client data |

### 5.2 Visitor-side identities (never log in)

| Identity | Summary |
|---|---|
| **Visitor** | The permanent center; a person/org that expressed interest; represented by VIN |
| **Visit Participant** | Accompanying party in a visit (e.g., family member); promotable to Visitor |
| **Referral** | A new visitor introduced by an existing visitor |

### 5.3 Edition model (who is active when)

- **V1 / Solo Edition:** **Super Admin + Company Owner/Marketer** only (single marketer per tenant).
- **Team Edition (future):** Manager, Sales Executive, Marketing Officer activate within a tenant.
- **Enterprise (future):** ABAC, SSO, CRM/ERP, advanced reporting.

### 5.4 Permission matrix (on own records)

| Action | Super Admin | Company Owner | Manager | Sales Executive | Marketing Officer (future) |
|---|---|---|---|---|---|
| Create | Yes | Yes | Yes | Yes | Planned |
| View | Yes (global) | Yes (company) | Yes (team) | Yes (own) | Planned (read) |
| Update | Yes | Yes | Yes | Yes (own) | Planned |
| Archive | Yes | Yes | Yes | Yes (own) | No |
| Restore | Yes | Yes | Yes | No | No |
| Transfer | Yes | Yes | Yes (approve) | Request | No |
| Share | Yes | Yes | Yes | Yes (own) | Planned |
| Delete (non-history) | Yes | Yes | Yes | No | No |
| Export | Yes | Yes | Yes (team) | No | Planned |
| Approve | Yes | Yes | Yes | No | No |
| Publish | Yes | Yes | No | No | Planned |
| Administer | Yes | Yes | No | No | No |

### 5.5 Visibility matrix (who sees what)

Legend: Own / Team / Company / Global / — (not visible).

| Resource | Super Admin | Company Owner | Manager | Sales Executive | Marketing Officer (future) |
|---|---|---|---|---|---|
| Visitors | Global | Company | Team | Own | Planned (aggregated) |
| Knowledge (metadata) | Global | Company | Team | Own + Shared | Planned |
| Timeline | Global | Company | Team | Own (+Transferred read) | Planned (aggregated) |
| Expenses | Global | Company | Team | Own | Planned (aggregated) |
| Purchases | Global | Company | Team | Own | Planned (aggregated) |
| Offerings | Global | Company | Company | Company | Company |
| Reports | Global | Company | Team | Limited | Yes |
| Private Notes | Global | Company | Team | Own (only) | No |
| Shared Notes | Global | Company | Team | Shared with | Shared with |
| Transferred Relationships | Global | Company | Team | Read (history) | No |
| System Audit | Global | Company | Limited | No | No |

### 5.6 Ownership model

> **We record influence, not ownership of visitors.**

| Information | Owned by | Transferable |
|---|---|---|
| Visitor identity & history | Platform / company | No (permanent) |
| Relationship | Marketer (role) | Yes (history preserved) |
| Timeline (history view) | Platform | No |
| Knowledge Item | Creator (unless shared) | No |
| Expenses / Purchases | Relationship | No (travel with transfer) |
| Offerings / Products | Company | No |
| Reports | Company | No |
| System Audit | System | No (immutable) |
| System Tags | Platform | No (immutable) |
| Custom Tags | Creator | No |

### 5.7 Sharing model

- **Private** (owner + oversight), **Shared** (explicit), **Read Only**, **Edit** (scoped), **Revoke** (always possible), **Transfer** (responsibility move), **Public/Temporary** (future).
- Sharing is **identity-based** (by VIN), not file-copying; the asset stays in place, only the permission changes.

---

## 6. Business Entities & Glossary (core)

| Entity | Definition |
|---|---|
| **Visitor** | Any person/org that expressed interest through any channel; permanent center |
| **Relationship** | A marketer's connection of responsibility to a visitor in a context |
| **Relationship Investment** | Accumulated time/effort/trust spent nurturing a relationship |
| **Offering / Product / Project** | What a company presents; purchasable unit; organisational grouping |
| **Visit / Participant** | In-person/scheduled interaction; accompanying party |
| **Timeline Event** | Atomic unit of history; User-Generated or System-Generated |
| **Communication / Notice** | Outbound contact via a channel; formal announcement |
| **Knowledge Item / Sharing** | Referenced asset (metadata+link only); grant/revoke by VIN |
| **Purchase / Referral / VIP / Archive** | Lifecycle states and events |

### Visitor lifecycle

`Interested → Negotiating → Purchased → Repeat Customer → VIP`, plus `Referral`
(branch) and any-state `→ Archived`. History is never deleted; archived visitors can
be reactivated; lifecycle changes create System events.

---

## 7. Product Structure — Modules (MOD-001…013)

Each module answers **one primary business question**.

| ID | Module | Scope | Primary question | REQ | Produces events |
|---|---|---|---|---|---|
| MOD-001 | Visitor Workspace | Visitor (container) | Who is this visitor? | REQ-001/002 | No (presents) |
| MOD-002 | Timeline | Visitor (home) | What happened? | REQ-003/004/005 | No (presents) |
| MOD-003 | Relationship Center | Visitor | Who owns this relationship? | REQ-006/007 | Yes (Transfer/Assign) |
| MOD-004 | Communication Center | Visitor | What did we say, and when? | REQ-008/009 | Yes |
| MOD-005 | Knowledge Center | Visitor | What can I share? | REQ-010/011 | Yes (Knowledge Shared) |
| MOD-006 | Visit Management | Visitor | When/where did we meet? | REQ-012/013 | Yes (Visit) |
| MOD-007 | Purchase Management | Visitor | What was achieved? | REQ-014 | Yes (Purchase) |
| MOD-008 | Relationship Investment | Visitor | What did I invest? | REQ-015 | Yes (Expense) |
| MOD-009 | Offering Management | Cross-visitor | What are we offering? | REQ-016 | Indirect |
| MOD-010 | Reports & Intelligence | Cross-visitor | What can we learn? | REQ-017 | No (read-only) |
| MOD-011 | Subscription | Cross-visitor | What recurring commitments exist? | REQ-018 | Yes (Subscription) |
| MOD-012 | Administration | Platform | Who has access / how configured? | REQ-019/020 | No |
| MOD-013 | Settings | Platform | How do I want it to work? | REQ-021 | No |

### Product map (navigation shape)

```text
VisiCore
├── Dashboard
├── Visitors
│   └── Visitor Workspace (primary workspace, one per visitor)
│       ├── Timeline (the home)
│       ├── Relationship Center
│       ├── Communication Center
│       ├── Knowledge Center
│       ├── Visit Management
│       ├── Purchase Management
│       └── Relationship Investment
├── Offering Management
├── Reports & Intelligence
├── Subscription
├── Administration
└── Settings
```

### UI screens (12)

Dashboard, Visitor Workspace, Timeline, Visit, Communication Center, Knowledge
Center, Purchase, Expense, Offering, Reporting Dashboard, Subscription, Settings.
All screen specs are **Draft**; the JotpotSMS integration spec is the most mature
(Enriched/V2).

---

## 8. Requirements

### Functional (21, all Accepted)

REQ-001 Visitor Workspace · REQ-002 Timeline home tab · REQ-003 Newest first ·
REQ-004 History never deleted · REQ-005 Event classification · REQ-006 Assign
relationship · REQ-007 Transfer relationship · REQ-008 Communications as events ·
REQ-009 Communication channels (SMS, Email, Notice, Call, Meeting) · REQ-010 Store
metadata not files · REQ-011 Share by VIN · REQ-012 Record visits & participants ·
REQ-013 Promote participant · REQ-014 Purchases advance lifecycle · REQ-015 Record
investments · REQ-016 Define offerings & associate · REQ-017 Aggregate history into
reports · REQ-018 Subscriptions as events · REQ-019 Manage marketers/roles/system
tags · REQ-020 System tags immutable · REQ-021 Custom tags & preferences.

### Non-functional (10, all Accepted)

NFR-001 History permanence · NFR-002 Transfer fidelity · NFR-003 Access control ·
NFR-004 Auditability · NFR-005 Responsive timeline · NFR-006 Efficient daily tasks ·
NFR-007 Availability · NFR-008 Scale with visitors/history · NFR-009 Distinguish
event types · NFR-010 Lean knowledge storage.

---

## 9. Business Processes (PROC-001…012)

| ID | Process | Primary actor | System event |
|---|---|---|---|
| PROC-001 | Visitor Intake & Creation | Marketer / System | Visitor Created |
| PROC-002 | Relationship Assignment | Manager / Marketer | Assign |
| PROC-003 | Relationship Transfer | Company Owner approves (V1) / System | Transfer |
| PROC-004 | Visit Logging | Marketer | Visit |
| PROC-005 | Timeline Event Creation | Marketer (User) / System | — |
| PROC-006 | Outbound Communication | Marketer / System | SMS/Email/Notice Sent |
| PROC-007 | Knowledge Sharing | Marketer / System | Knowledge Shared |
| PROC-008 | Purchase Recording | Marketer or Manager / System | Purchase |
| PROC-009 | Referral Creation | Marketer / System | Referral/Visitor Created |
| PROC-010 | Visitor Archiving | Marketer or Manager / System | Archive |
| PROC-011 | Participant Promotion | Marketer / System | Promote |
| PROC-012 | Tagging | Marketer (Custom) / System (System) | — |

Planned (not detailed): PROC-013 Reminder/Follow-up, PROC-014 Relationship
Reactivation, PROC-015 VIP Promotion, PROC-016 Reporting & Intelligence,
PROC-017 Notice Drafting & Approval.

---

## 10. Architecture (frozen v1.0)

- **Status:** Business Architecture **v1.0 frozen** (VC-10.6, re-confirmed VC-18); **implementation-ready**, maturity **~4.7 / 5**.
- **Boundaries:** thin controllers, service layer owns logic, driver/adapter pattern for all external integrations (SMS, email, storage), append-only Timeline event architecture.
- **Tenancy:** multi-tenant SaaS, isolated tenant workspaces (BDR-021), designed to grow solo → team without DB redesign.
- **Identity/access:** roles per §5; auth via session (portal) + API keys in V1; JWT/OAuth2/SSO/OTP future; ABAC future.
- **Identifiers:** external VIN `VC-YYYY-NNNNNN`; internal DB keys never exposed.
- **Data:** visitor-centric logical model; immutable history; knowledge referenced not stored; archive ≠ delete.
- **API:** contract-first, resource-oriented, URI versioning (`/api/v1/...`), `{data, meta}` envelopes, idempotency keys on mutating calls, rate limiting, webhooks (8-event catalogue, signed, at-least-once).
- **Integrations:** JotpotSMS (most mature — 6 SMS interactions, adapter/driver), Email (Draft), future Payment, Storage providers (Google Drive, OneDrive, Dropbox, S3, YouTube, etc.), SSO, CRM/ERP.

### API groups (logical contracts, all Draft)

Visitor, Relationship, Visit, Timeline, Communication, Knowledge, Offering, Expense,
Purchase, Subscription, Notification, Reporting — each maps to a MOD and produces/consumes the documented Timeline Events.

---

## 11. Development Setup & Stack

| Area | Choice | Notes |
|---|---|---|
| Backend | **Laravel 13 / PHP ^8.3** | Per WWDF standard (Laravel, current major) |
| Database | **MySQL** (primary; MariaDB for local dev) | WWDF standard; tests on in-memory SQLite |
| Web UI | **Blade** server-rendered + **Vite + Tailwind 4** (current scaffold) | WWDF default is Bootstrap + page-based Vue; Tailwind is the local setup here |
| Mobile | **Flutter** | When a mobile client is needed (future) |
| Version control | Git | WWDF `150-git-workflow.md` |
| Infra | Ubuntu LTS; Docker optional; Composer/npm | WWDF `01-technology-stack.md` |

**Current app state:** the `laravel-visicore-app/` is a fresh Laravel 13 skeleton —
welcome route only, default migrations, no domain code yet. All 306 project docs +
237 WWDF framework docs are written; **implementation has not started** (milestones M0–M6 pending).

---

## 12. Editions & Roadmap

### Implementation roadmap (track)

```text
Solo Edition (V1) → MVP → Hardening
        ↓
Team Edition (multi-user in a tenant) → Roles: Manager / Sales / Marketing
        ↓
Enterprise (ABAC, SSO, ERP/CRM, advanced reporting)
```

### MVP (Solo Edition) — in scope

Tenancy + Auth + Access-Control · Visitor + VIN · Relationship + Transfer ·
Timeline (append-only) · Visit + Participants · Communication (SMS/email) ·
Knowledge sharing (basic) · Purchase.

### MVP — non-goals (post-MVP)

Expense, Offering catalog, Subscription, Reporting (minimal/deferred) · Team
Edition multi-user · Enterprise (ABAC/SSO/ERP) · public/temporary sharing, QR,
group sharing.

### Module build order

1 Foundation (tenancy+auth+access) → 2 Visitor+VIN → 3 Relationship+Transfer →
4 Timeline/events → 5 Visit → 6 Communication+adapters → 7 Knowledge+storage →
8 Purchase → 9 Expense → 10 Offering → 11 Subscription → 12 Reporting →
13 Administration+Settings.

### Milestones

M0 (Foundation) → M1 (Core) → M2 (Engagement) → M3 (MVP Solo) → M4 (Post-MVP) →
M5 (Hardening) → M6 (Team).

---

## 13. Open Questions & Known Gaps (non-blocking)

- **32 open questions, 0 critical** (highest impact: M-4 mandatory fields, M-5
  inbound capture, M-7 refunds/cancellations, M-9 subscription model, M-11
  de-duplication, M-15 event payload schema, M-23 performance targets, L-5
  JotpotSMS binding contract).
- Custom Tag behaviour on transfer; VIP criteria; expense PROC; reports/KPIs;
  subscription business model; performance/availability SLOs — not yet defined.
- Planned architecture domains not yet written: integration, deployment,
  scalability, caching, event-architecture.

---

## 14. The WWDF Framework (how this project is governed)

VisiCore is the first full adoption of the **Win-Win Development Framework (WWDF
v1.1)** — the permanent engineering framework of Win-Win Service Provider.

- **Constitution:** 12 non-negotiable principles (business before technology;
  simplicity before cleverness; documentation before implementation; architecture
  before coding; contract-first; thin controllers/services own logic; repository/DTO
  only when justified; never invent business rules; history is valuable; AI assists,
  humans approve; reuse principles not implementations; maintainability over
  premature optimisation).
- **Governance:** the **Architect** owns the framework; changes require **FDRs**
  (framework-level) or **ADRs** (project-level); standards deviations need an
  approved decision record.
- **Lifecycle:** Idea → Business Analysis → Requirements → Architecture →
  Engineering → Implementation → Testing → Release → Maintenance → Lessons Learned →
  Framework Improvement. Architecture freeze precedes implementation (FDR-008).
- **Traceability codes:** BDR, REQ, NFR, PROC, MOD, ENT, UC, FDR, ADR, PF (see
  `00-WWDF/00-framework-overview.md`).
- **Tooling:** Intelephense LSP + Pint formatter; Playwright MCP for browser
  testing; tests via `composer test` (in-memory SQLite); code style via
  `./vendor/bin/pint`.

---

## 15. Where Everything Lives (quick map)

| Looking for | Go to |
|---|---|
| Start here | `docs/START-HERE.md` |
| Decisions (BDRs) | `docs/01-Business/100-business-decision-records.md` |
| Glossary | `docs/01-Business/010-business-glossary.md` |
| Requirements | `docs/02-Requirements/` (index: `020-requirement-index.md`) |
| Processes | `docs/03-Business-Processes/` |
| Modules | `docs/05-Product-Blueprint/080-module-definition-records.md` |
| Roles & permissions | `docs/04-Architecture/access-control/` |
| Architecture freeze | `docs/04-Architecture/review/070-architecture-freeze-certificate.md` |
| API contracts | `docs/07-API/` |
| MVP & build order | `docs/09-Development/implementation/` |
| Framework | `00-WWDF/` (constitution in `00-Core/08-wwdf-constitution.md`) |
| This document | `docs/ULTIMATE-VISICORE-REFERENCE.md` |
| Feature list w/ roles | `docs/FEATURE-LIST-WITH-USER-ROLES.md` |

---

*Status legend: this document is a **compiled overview**; every fact is traceable to a
source doc (BDR / REQ / NFR / PROC / MOD / access-control / architecture-review).
When in doubt, follow the BDR registry.*