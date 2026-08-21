# VisiCore — Module Index

> **Purpose:** Single index of the 13 VisiCore modules (MOD-001 … MOD-013) with
> their implementation records, MVP status, and dependencies.
> **Status:** Complementary to `docs/05-Product-Blueprint/080-module-definition-records.md`
> (that file remains authoritative for definitions). Created during the Laravel
> implementation documentation effort (2026-08-18).

## How to Read

- Each row links the **implementation record** (this series) back to its
  **definition**, **requirements**, **process(es)**, **BDRs**, and **API** contract.
- **MVP status** per `docs/09-Development/implementation/020-mvp-definition.md`.
- **Build order** per `docs/09-Development/implementation/030-module-build-order.md`.

## The 13 Modules

| Module | Implementation Record | MVP | Build step | Core BDRs | API contract |
|---|---|---|---|---|---|
| MOD-001 Visitor Workspace | [MOD-001-VISITOR-WORKSPACE.md](MOD-001-VISITOR-WORKSPACE.md) · [Implementation](implementation/MOD-001-VISITOR-WORKSPACE-PROTOTYPE.md) | yes | 2 | BDR-002, BDR-018 | `140-visitor-api.md` |
| MOD-002 Timeline | [MOD-002-TIMELINE.md](MOD-002-TIMELINE.md) · [Implementation](implementation/MOD-002-TIMELINE-PROTOTYPE.md) | yes | 4 | BDR-005, BDR-011, BDR-012, BDR-013, BDR-016 | `170-timeline-api.md` |
| MOD-003 Relationship Center | [MOD-003-RELATIONSHIP-CENTER.md](MOD-003-RELATIONSHIP-CENTER.md) · [Implementation](implementation/MOD-003-RELATIONSHIP-CENTER-PROTOTYPE.md) | yes | 3 | BDR-003, BDR-004, BDR-019 | `150-relationship-api.md` |
| MOD-004 Communication Center | [MOD-004-COMMUNICATION-CENTER.md](MOD-004-COMMUNICATION-CENTER.md) | yes | 6 | BDR-014, BDR-016 | `180-communication-api.md` |
| MOD-005 Knowledge Center | [MOD-005-KNOWLEDGE-CENTER.md](MOD-005-KNOWLEDGE-CENTER.md) | yes (basic) | 7 | BDR-009, BDR-010, BDR-016 | `190-knowledge-api.md` |
| MOD-006 Visit Management | [MOD-006-VISIT-MANAGEMENT.md](MOD-006-VISIT-MANAGEMENT.md) | yes | 5 | BDR-007, BDR-012 | `160-visit-api.md` |
| MOD-007 Purchase Management | [MOD-007-PURCHASE-MANAGEMENT.md](MOD-007-PURCHASE-MANAGEMENT.md) | yes | 8 | BDR-005, BDR-006, BDR-016 | `220-purchase-api.md` |
| MOD-008 Relationship Investment | [MOD-008-RELATIONSHIP-INVESTMENT.md](MOD-008-RELATIONSHIP-INVESTMENT.md) | no | 9 | BDR-012 | `210-expense-api.md` |
| MOD-009 Offering Management | [MOD-009-OFFERING-MANAGEMENT.md](MOD-009-OFFERING-MANAGEMENT.md) | no | 10 | BDR-002, BDR-006 | `200-offering-api.md` |
| MOD-010 Reports & Intelligence | [MOD-010-REPORTS-INTELLIGENCE.md](MOD-010-REPORTS-INTELLIGENCE.md) | no | 12 | BDR-011, BDR-012 | `250-reporting-api.md` |
| MOD-011 Subscription | [MOD-011-SUBSCRIPTION.md](MOD-011-SUBSCRIPTION.md) | no | 11 | BDR-016 | `230-subscription-api.md` |
| MOD-012 Administration | [MOD-012-ADMINISTRATION.md](MOD-012-ADMINISTRATION.md) | foundation | 1 | BDR-015, BDR-020 | authn/authz docs |
| MOD-013 Settings | [MOD-013-SETTINGS.md](MOD-013-SETTINGS.md) | later | 13 | BDR-015 | settings (user-scoped) |

## Supporting Documents (this series)

| Document | Purpose |
|---|---|
| [LARAVEL-DEVELOPMENT-STANDARD.md](LARAVEL-DEVELOPMENT-STANDARD.md) | Laravel-specific build standard (skeleton, structure, patterns). |
| [LARAVEL-PACKAGES.md](LARAVEL-PACKAGES.md) | Free/open-source package evaluation for the skeleton. |
| [VISICORE-UI-UX-STANDARD.md](VISICORE-UI-UX-STANDARD.md) | Frontend/UI-UX standard (Tailwind 4, Blade, Alpine). |
| [VISICORE-DEVELOPMENT-WORKFLOW.md](VISICORE-DEVELOPMENT-WORKFLOW.md) | Day-to-day workflow (dev server, tests, git, verification). |
| [OPENCODE-VISICORE-RULES.md](OPENCODE-VISICORE-RULES.md) | Rules for AI-assisted development in this repo. |

## MVP Quick Reference

**MVP = Solo Edition (single-marketer) — BDR-017/020/021.**

- **In MVP:** platform foundation (tenancy+auth+access), MOD-001, MOD-003,
  MOD-002, MOD-006, MOD-004, MOD-005 (basic), MOD-007.
- **Post-MVP / deferred:** MOD-008, MOD-009, MOD-011, MOD-010; Team Edition;
  Enterprise (ABAC/SSO/ERP); public/temporary sharing; QR.
- **Success criteria:** complete permanent journey captured newest-first;
  SMS/email sent and recorded; knowledge shared by VIN; purchase advances
  lifecycle.

## Key Identifiers

- **VIN** — `VC-YYYY-NNNNNN` (BDR-018).
- **Module record IDs** — `REL`, `VIS`, `KNW`, `PUR`, `EXP`, `OFF`, `EVN`,
  `REM`, `REF`, `PAR`, `SUB`, `NOT` (`PREFIX-NNNNNN`, `060-identifier-strategy.md`).

## Notable Open Questions

Full register: `docs/04-Architecture/access-control/` and `docs/09-Development/`.
High-level: access-control matrix (MOD-012), reporting KPIs (MOD-010),
subscription business model (MOD-011), lifecycle/VIP criteria (M-2),
group sharing/expiry (M-6), refunds/auto-advance (M-7), investment definition
(M-10), correction format (M-15), offering lifecycle (M-21).

---

*Source documents remain authoritative. See `docs/ULTIMATE-VISICORE-REFERENCE.md`
for the full reference.*