# MOD-013 — Settings

> **Type:** Module Implementation Record (development-facing)
> **Status:** NOT_STARTED · **Compiled:** 2026-08-18
> Source documents remain authoritative; nothing here invents business rules.

---

## A. Module Overview

| Attribute | Value |
|---|---|
| **Module ID** | MOD-013 |
| **Module name** | Settings |
| **Purpose** | User and workspace preferences. |
| **Business objective** | Let users tailor their environment. |
| **Business meaning** | Answer "how do I want things to work?" — user-level preferences and workspace configuration. |
| **Product Map position** | `VisiCore → Settings` (per-user). |
| **MVP/Post-MVP status** | **Platform/later** — minimal (custom tags + preferences) via MOD-013; future enhancements (notifications, localization) post-MVP. |
| **Scope** | User custom tags (creator-owned, BDR-015) and workspace preferences. |
| **Non-scope** | Administration (MOD-012), notifications and localization (future enhancement), settings scope is an Open Question. |

---

## B. Source Traceability

| Concern | Source ID / Location |
|---|---|
| MOD definition | MOD-013 in `docs/05-Product-Blueprint/080-module-definition-records.md` |
| Requirements | REQ-021 (manage custom tags and workspace preferences) in `req-mod-013-settings.md` |
| Business process | PROC-012 (Tagging) |
| BDRs | BDR-015 (custom tags belong to the creator) |
| NFRs | NFR-003 (data protection), NFR-005 (responsive) |
| Data model | Custom Tag entity; `custom_tags` table (`020-entity-catalog.md`, `080-table-catalog.md`) |
| UI/UX | `docs/06-UI-UX/180-settings-screen.md`, `010-workspace-specification.md` |
| Access control | Identity model, role definitions |
| Feature list | MOD-013 rows in `docs/FEATURE-LIST-WITH-USER-ROLES.md` |
| Reference | `docs/ULTIMATE-VISICORE-REFERENCE.md` |

---

## C. Role-Based Access

| Action | SA | CO | MG (target) | SE (target) | MO (future) |
|---|---|---|---|---|---|
| Manage own Custom Tags | Yes (own) | Yes (own) | Yes (own) | Yes (own) | Planned |
| Manage own workspace preferences | Yes (own) | Yes (own) | Yes (own) | Yes (own) | Planned |
| Access others' settings | **No** | **No** | **No** | **No** | **No** |

**Restrictions**
- Custom Tags belong only to the creator (BDR-015); transfer behavior is an Open
  Question (BDR-015 implications).
- Settings are user-scoped; tenant-scoped (BDR-021).

---

## D. Complete Feature Breakdown

### MVP (V1)
- Minimal user preferences + custom tags (needed for tagging — PROC-012).
- Custom Tag creation/management by creator (REQ-021).

### Planned (post-MVP) / Future
- Notifications and localization (MOD-013 "Future Enhancements").
- Broader settings scope (Open Question).

**F-043 — Manage custom tags**
- Behavior: users manage their own custom tags (REQ-021, BDR-015).
- Rules: custom tags belong to creator; System Tags immutable (MOD-012);
  tagging applies to entities (PROC-012).
- Permissions: owner-only.

**F-044 — Workspace preferences**
- Behavior: users manage workspace preferences (REQ-021).
- Rules: user-scoped config.
- Permissions: owner-only.

### Post-MVP / Future
- Notifications, localization settings.

---

## E. Complete User Flow

```text
User opens Settings
↓
Manages own custom tags (create/rename/delete own) + workspace preferences
↓
System validates ownership (creator-only)
↓
System persists (user-scoped, tenant-scoped)
↓
Custom Tags available for tagging (PROC-012)
```

### Failure flows
- **Modifying another user's tags → 403.**
- **Deleting a System Tag → blocked (BDR-015; see MOD-012).**
- **Invalid preference data → 422.**

---

## F. Business Rules

1. **Custom Tags belong only to the creator (BDR-015 / REQ-021).**
2. **System Tags are immutable (BDR-015; MOD-012).**
3. **Tagging applies to entities (visitor, relationship, knowledge item)
   (PROC-012).**
4. **Settings are user-scoped** — one user's preferences never leak to another.
5. **Custom Tag transfer behavior is an Open Question** (BDR-015 implications) —
   do not invent.

---

## G. States and Lifecycle

Custom Tag lifecycle (derived from PROC-012 + BDR-015):

```text
Custom Tag: Created (creator) -> renamed -> deleted (by creator only)
System Tag: Created -> immutable (never deleted)
```

| Attribute | Value |
|---|---|
| Custom Tag states | Created → active (creator may delete own) |
| Allowed transitions | Create/rename/delete by creator |
| Forbidden transitions | Modify/delete by non-creator; deleting System Tags |
| Trigger / Actor / Result | Creator action; PROC-012 tagging applies tags |

> Exact custom-tag schema (color, label) per `080-table-catalog.md`; do not
> invent beyond source.

---

## H. Timeline Integration

| Attribute | Value |
|---|---|
| Event type produced | **None** (MOD-013 definition: no Timeline events) |
| Consumes | None |

---

## I. Audit Integration

- Preference/tag changes may be auditable (NFR-004) per audit philosophy; no
  specific source mandate beyond general audit.

---

## J. Data Model

Physical tables: **`custom_tags`** (+ user preference storage) (from
`080-table-catalog.md`)

| Element | Value |
|---|---|
| Primary key | `id` |
| Business identifier | tag id (standard) |
| Foreign keys | `tenant_id`; `user_id` (creator) |
| Tenant ownership | Yes |
| Soft delete | creator may delete own custom tag (BDR-015) |
| Archive | N/A |
| Versioning | N/A |
| Audit | Yes |
| Indexes | `user_id`, `tenant_id`, `name` |
| Constraints | creator ownership enforced |

> Confirm columns in `080-table-catalog.md`. Preferences storage — use
> user-scoped config (per source; keep simple).

---

## K. Laravel Implementation

| Component | Expected |
|---|---|
| Controllers | `Http/Controllers/Settings/SettingsController` (preferences), `CustomTagController`. |
| Form Requests | SettingsRequest; CustomTagRequest. |
| Services | `Settings\\Services\\SettingsService` (preferences + custom tags). |
| Models | `CustomTag`, user preference model. |
| Policies | `CustomTagPolicy` (owner-only); settings owner-only. |
| Middleware | Tenant scope; auth. |
| Routes | Settings + custom-tags web/API routes. |
| Views/components | Settings screen, custom tag management UI. |
| Tests | See §Q. |

---

## L. API Specification

Settings/custom-tags endpoints are **not enumerated** in the 140–250 API set;
use standards (`050-request-response-standard.md`) + authentication. Treat
settings as user-scoped web + API per application conventions.

---

## M. Validation

- Custom tag ownership (creator) validated; name/label per schema.
- Preferences validated per config schema.

---

## N. Error Handling

401/403 (non-owner)/404/422/429/5xx per `060-error-handling.md`.

---

## O. Security

- Auth: session or API key.
- Authorization: owner-only tags/preferences; tenant isolation (BDR-021).
- System Tags protected (MOD-012).

---

## P. UI/UX

Per `docs/06-UI-UX/180-settings-screen.md`:
- Settings screen: custom tags + workspace preferences.
- Owner-only controls; System Tag list (read-only/immutable).
- Empty states; responsive/accessible/loading per standard.

---

## Q. Testing

- Unit: custom tag CRUD ownership; preference persistence.
- Feature: create/rename/delete own tag; block non-owner; block System Tag delete.
- Security: 403 cross-user; tenant isolation.
- Audit: change trail.
- Edge: tag used on entities then deleted (per schema — do not invent cascade
  rule; verify `080-table-catalog.md`/PROC-012 Open Questions).

---

## R. Acceptance Criteria

- [ ] Users manage own custom tags and workspace preferences (REQ-021).
- [ ] Custom Tags belong to creator (BDR-015); System Tags immutable.
- [ ] No cross-user/cross-tenant leakage.

---

## S. Developer Checklist

- **Backend:** Settings/CustomTag controllers/services/models/policies.
- **Database:** `custom_tags` + preferences per catalog.
- **Authorization:** owner-only; tenant middleware.
- **Frontend:** settings screen, custom tag management.
- **Testing:** §Q.
- **Documentation:** update VISICORE-MODULE-INDEX.md.

---

## Module Dependencies

- **Depends on:** MOD-012 (Administration — user context, System Tags), identity
  model.
- **Used by:** All modules (custom tags usable in tagging — PROC-012).
- **Produces:** none.
- **Consumes:** user context.

> Cross-cutting/later module — no dependency cycles.

---

## Open Questions

| Question | Why it matters | Source documents checked | Possible impact |
|---|---|---|---|
| Settings scope | Feature set | MOD-013 def Open Questions | UI + schema |
| Custom Tag transfer behavior | Ownership on reassignment | BDR-015 implications | Tag ownership logic |
| Notifications/localization settings | Future enhancements | MOD-013 def | Scope creep guard |

---

*Source documents remain authoritative. Follow the BDR registry and the frozen
architecture (v1.0) when implementing.*