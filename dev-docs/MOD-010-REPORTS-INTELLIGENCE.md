# MOD-010 — Reports & Intelligence

> **Type:** Module Implementation Record (development-facing)
> **Status:** NOT_STARTED · **Compiled:** 2026-08-18
> Source documents remain authoritative; nothing here invents business rules.
> **Caution:** Reporting PROC-016 is **Planned only** and metrics/KPIs are
> explicitly **not defined** (REQ-017 note). Do not invent metrics.

---

## A. Module Overview

| Attribute | Value |
|---|---|
| **Module ID** | MOD-010 |
| **Module name** | Reports & Intelligence |
| **Purpose** | Turn history into learning — the platform's intelligence output. |
| **Business objective** | Answer cross-visitor questions and improve future success. |
| **Business meaning** | The learning loop (BDR-011/012): aggregate Timeline Events into reports so marketers learn from history. |
| **Product Map position** | `VisiCore → Dashboard / Reporting`. |
| **MVP/Post-MVP status** | **Post-MVP** (`020-mvp-definition.md` excludes MOD-010). Build after MOD-009/MOD-011 per build order. |
| **Scope** | Read-only aggregation of Timeline Events across visitors; KPI summary, visitor analytics, async export. |
| **Non-scope** | Specific KPIs/metrics (not defined — Open Question M-8); AI-driven insights and predictive scoring (future enhancement); writing to Timeline. |

---

## B. Source Traceability

| Concern | Source ID / Location |
|---|---|
| MOD definition | MOD-010 in `docs/05-Product-Blueprint/080-module-definition-records.md` |
| Requirements | REQ-017 (aggregate history into reports) in `req-mod-010-reports-intelligence.md` |
| Business process | PROC-016 (Reporting & Intelligence) — **Planned, not detailed** |
| BDRs | BDR-011 (Timeline), BDR-012 (learning loop) |
| NFRs | NFR-008 (scale), NFR-005 (responsive) |
| Data model | Reads Timeline Events; Dashboard/Reporting dashboards (`160-reporting-dashboard.md`) |
| API | `docs/07-API/250-reporting-api.md` (Draft) |
| UI/UX | `docs/06-UI-UX/070-dashboard-specification.md`, `160-reporting-dashboard.md`, `030-widget-library.md` |
| Access control | Visibility matrix (aggregates must respect scope) |
| Feature list | MOD-010 rows in `docs/FEATURE-LIST-WITH-USER-ROLES.md` |
| Reference | `docs/ULTIMATE-VISICORE-REFERENCE.md` |

---

## C. Role-Based Access

| Action | SA | CO | MG (target) | SE (target) | MO (future) |
|---|---|---|---|---|---|
| View reports/dashboard | Global | Company-wide | Team | Team (shared) | Planned |
| Export | Global | Yes | Yes | Per scope | Planned |
| Define metrics | No (platform) | No | No | No | No |

**Restrictions**
- Aggregates must respect **visibility scope** (a MG sees team data; CO sees
  company data; SE sees own + shared/transferred) — visibility matrix.
- Read-only: no Timeline writes.
- Tenant-scoped (BDR-021).

---

## D. Complete Feature Breakdown

### MVP (V1)
**Not in MVP** (`020-mvp-definition.md`). MOD-010 is post-MVP.

### Planned (post-MVP)

**F-034 — KPI summary**
- Behavior: `GET /reports/summary` — KPI summary across visitors (REQ-017).
- Rules: metrics per visibility scope; exact KPI set is Open Question M-8.
- Permissions: scope-aware read.

**F-035 — Visitor analytics**
- Behavior: `GET /reports/visitors` — visitor analytics from Timeline Events.
- Rules: aggregation read-only; no metric invention (M-8).
- Permissions: scope-aware read.

**F-036 — Async export**
- Behavior: `GET /reports/export` — async export of reports.
- Rules: async processing; export respects scope.
- Permissions: scope-aware read.

### Post-MVP / Future
- AI-driven insights, predictive scoring (MOD-010 "Future Enhancements").
- Metrics/KPIs defined with product (Open Question M-8).

---

## E. Complete User Flow

```text
User opens Dashboard / Reporting
↓
System validates visibility scope (SA global / CO company / MG team / SE own)
↓
System aggregates Timeline Events (read-only; newest first source)
↓
System renders KPI summary + visitor analytics (widgets)
↓
User requests export → async job → download (scope-aware)
```

### Failure flows
- **Out-of-scope metric access → 403.**
- **Empty data → empty state ("No data yet").**
- **Async export failure → retry/error per standards.**

---

## F. Business Rules

1. **Learn from history (REQ-017; BDR-011/012 learning loop).** Reports are
   derived from Timeline Events.
2. **Read-only** (MOD-010 definition: no Timeline events produced).
3. **Aggregation respects visibility scope** (visibility matrix) — never leak
   across tenant/scope (BDR-021, NFR-003).
4. **Specific metrics/KPIs are NOT defined (Open Question M-8)** — do not invent.
5. **PROC-016 is Planned, intentionally not detailed** (process catalog) — do
   not invent the analytics process.

---

## G. States and Lifecycle

No new entities — MOD-010 is read-only aggregation. Export jobs may have a
lifecycle (queued → running → done → failed) per app standard, but no source
specifies it; keep implementation simple (async job framework).

---

## H. Timeline Integration

| Attribute | Value |
|---|---|
| Event type produced | **None** (read-only, MOD-010 definition) |
| Consumes | Timeline Events across visitors (aggregate) |
| Trigger | Dashboard/report view or export |
| Scope | Per visibility matrix |

---

## I. Audit Integration

- Report/export access may be auditable (NFR-004) per audit philosophy; source
  does not mandate report-view auditing — apply audit philosophy as documented.

---

## J. Data Model

- **No new business tables.** Reads `timeline_events` (and referenced module
  tables) for aggregation.
- Derived/aggregate tables or read models may be added later **only when
  justified** (WWDF "repos/read-models only when justified"); do not pre-design.

---

## K. Laravel Implementation

| Component | Expected |
|---|---|
| Controllers | `Http/Controllers/Report/ReportController` (summary/visitors/export). |
| Services | `Report\\Services\\ReportService` (aggregation, scope-filtered). |
| Jobs | Async export job (`/reports/export`). |
| Resources | `ReportSummaryResource`, `VisitorAnalyticsResource`. |
| Policies | `ReportPolicy` (scope-aware read/export). |
| Middleware | Tenant scope. |
| Routes | `GET /reports/summary`, `GET /reports/visitors`, `GET /reports/export`. |
| Views/components | Dashboard widgets, analytics charts, export button. |
| Tests | See §Q. |

---

## L. API Specification

From `docs/07-API/250-reporting-api.md` (Draft).

| Method | URL | Purpose | Auth | Authz |
|---|---|---|---|---|
| GET | `/reports/summary` | KPI summary | Session/API key | Scope-aware |
| GET | `/reports/visitors` | Visitor analytics | Session/API key | Scope-aware |
| GET | `/reports/export` | Async export | Session/API key | Scope-aware |

**Response:** envelope `{data, meta}`; export async — poll/download per standard.
**Errors:** 401, 403, 404, 422 (invalid filter), 429, 5xx.

---

## M. Validation

- Filter params (date range, visitor scope) validated.
- Export formats per standard.
- No metric invention (M-8 pending).

---

## N. Error Handling

401/403/404/422/429/5xx per `060-error-handling.md`. Async export failures
reported via job status.

---

## O. Security

- Auth: session or API key.
- **Scope enforcement at query level** — aggregates MUST be filtered by
  visibility scope + tenant (NFR-003, BDR-021).
- Read-only — no Timeline writes.

---

## P. UI/UX

Per `docs/06-UI-UX/160-reporting-dashboard.md`, `070-dashboard-specification.md`,
`030-widget-library.md`:
- KPI summary widgets; visitor analytics; export control.
- Empty state: "No data yet" → guide to start engaging.
- Scope indicator (what scope you're viewing).
- Responsive/accessible/loading per standard.

---

## Q. Testing

- Unit: aggregation math; scope filtering (SE/MG/CO/SA views).
- Feature: summary, analytics, export async job.
- API: 3 endpoints per `250-reporting-api.md`.
- Security: cross-tenant/scope leak prevention (critical).
- Audit: export/access logs per audit philosophy.
- Edge: empty dataset; large dataset (NFR-008); invalid filters.

---

## R. Acceptance Criteria

- [ ] Reports aggregate Timeline Events across visitors within scope (REQ-017).
- [ ] Read-only — no Timeline writes from MOD-010.
- [ ] No cross-scope/tenant data leakage.
- [ ] Export is async and scope-safe.

---

## S. Developer Checklist

- **Backend:** ReportController; ReportService (scope-filtered aggregation);
  export job.
- **API:** 3 endpoints per `250-reporting-api.md`.
- **Database:** none new (reads timeline_events).
- **Authorization:** scope-aware policy; tenant middleware (critical).
- **Frontend:** dashboard widgets, analytics, export, empty state.
- **Testing:** §Q (incl. security tests).
- **Documentation:** update VISICORE-MODULE-INDEX.md (post-MVP).

---

## Module Dependencies

- **Depends on:** MOD-002 (Timeline Events — main input), all event producers
  (MOD-003…MOD-008), visibility matrix.
- **Used by:** MOD-001 (dashboard), platform dashboards.
- **Produces:** none.
- **Consumes:** Timeline Events (aggregates).

> No dependency cycles; post-MVP. Metrics definition pending (M-8).

---

## Open Questions

| Question | Why it matters | Source documents checked | Possible impact |
|---|---|---|---|
| Metrics/KPIs definition (M-8) | Report contents | REQ-017 note; MOD-010 def Open Questions | Aggregation logic |
| PROC-016 (Reporting) process details | Analytics rules | `020-process-catalog.md` (Planned) | Behavior spec |
| Export format/file size | Async job design | `250-reporting-api.md` | Job + storage |

---

*Source documents remain authoritative. Follow the BDR registry and the frozen
architecture (v1.0) when implementing.*