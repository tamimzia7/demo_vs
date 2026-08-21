# MOD-012 — Administration

> **Type:** Module Implementation Record (development-facing)
> **Status:** NOT_STARTED · **Compiled:** 2026-08-18
> Source documents remain authoritative; nothing here invents business rules.
> **Caution:** The detailed role/permission (access-control) model is **deferred
> to a future sprint** (REQ-019 note, MOD-012 Open Question). V1 access model is
> per BDR-020.

---

## A. Module Overview

| Attribute | Value |
|---|---|
| **Module ID** | MOD-012 |
| **Module name** | Administration |
| **Purpose** | Platform-level administration of users, roles, and system tags. |
| **Business objective** | Govern the platform and its people. |
| **Business meaning** | Answer "who has access, and how is the platform configured?" — manage marketers, roles, and system tags (REQ-019). |
| **Product Map position** | `VisiCore → Administration` (cross-cutting). |
| **MVP/Post-MVP status** | **Platform/module** — required for foundation (users/roles/tenancy); build as part of platform foundation and enhanced post-MVP. See `020-mvp-definition.md`. |
| **Scope** | Manage marketers (users), roles, System Tags (immutable); configuration. |
| **Non-scope** | RBAC UI and audit-logs UI (future enhancement); the full access-control matrix is an Open Question (future sprint); settings (MOD-013). |

---

## B. Source Traceability

| Concern | Source ID / Location |
|---|---|
| MOD definition | MOD-012 in `docs/05-Product-Blueprint/080-module-definition-records.md` |
| Requirements | REQ-019 (manage marketers, roles, system tags), REQ-020 (System Tags immutable) in `req-mod-012-administration.md` |
| Business processes | PROC-002, PROC-003 (relationship admin), PROC-012 (Tagging) |
| BDRs | BDR-003 (relationships belong to marketers), BDR-008 (customers/users), BDR-015 (System vs Custom tags) |
| Access control | Role definitions `020-role-definitions.md`, identity model `010-identity-model.md`, permission matrix `040-permission-matrix.md`, authorization philosophy `090-authorization-philosophy.md` |
| Data model | User/Role/System Tag entities; `users`, `roles`, `system_tags` tables (`020-entity-catalog.md`, `080-table-catalog.md`) |
| API | Authentication `020-authentication.md`; authorization `030-authorization.md`; (admin endpoints not in 140–250 range — may be platform/web-only) |
| UI/UX | `docs/06-UI-UX/180-settings-screen.md` |
| Feature list | MOD-012 rows in `docs/FEATURE-LIST-WITH-USER-ROLES.md` |
| Reference | `docs/ULTIMATE-VISICORE-REFERENCE.md` |

---

## C. Role-Based Access

| Action | SA | CO | MG (target) | SE (target) | MO (future) |
|---|---|---|---|---|---|
| Manage marketers (users) | Yes | Company-level | No | No | Planned |
| Manage roles | Yes | Company-level (future) | No | No | Planned |
| Manage System Tags | Yes | No (System-owned) | No | No | No |
| Apply System Tags (via tagging) | — | Yes (use) | Yes (use) | Yes (use) | Yes |
| Delete a System Tag | **Blocked** (BDR-015/REQ-020) | Blocked | Blocked | Blocked | Blocked |

**Restrictions**
- Full role/permission matrix is an **Open Question** (future sprint) — V1
  access model per BDR-020 (SA platform + CO company + Team roles later).
- System Tags immutable; delete blocked (BDR-015, PROC-012 exception).
- Tenant-scoped (BDR-021).

---

## D. Complete Feature Breakdown

### MVP (V1) — foundation only
- Users/roles/tenancy required for platform foundation (`020-mvp-definition.md`):
  users, roles (SA/CO), tenant membership.
- System Tag provisioning (as used by platform classification).

### Planned (post-MVP) — per MOD-012 future
- RBAC UI, audit-logs UI (MOD-012 "Future Enhancements").
- Full access-control matrix (Open Question).

**F-040 — Manage marketers (users)**
- Behavior: administrators manage marketers (REQ-019).
- Rules: V1 = SA + CO roles (BDR-020); detailed matrix deferred.
- Permissions: SA (platform), CO (company-level).

**F-041 — Manage roles**
- Behavior: manage roles (REQ-019).
- Rules: deferred detail (Open Question).
- Permissions: SA.

**F-042 — System Tags (immutable)**
- Behavior: manage System Tags; **cannot be deleted** (REQ-020, BDR-015).
- Rules: delete blocked (PROC-012 exception).
- Permissions: SA; usage by all roles.

### Post-MVP / Future
- RBAC UI, audit logs (MOD-012).
- Access-control matrix finalized.

---

## E. Complete User Flow

```text
SA (platform) / CO (company) opens Administration
↓
Manages users (create/invite marketer), roles, tenant membership
↓
System validates permission (SA/CO per BDR-020)
↓
System applies user/role changes
↓
System Tags: create/update; delete attempts blocked (BDR-015)
↓
Tagging across entities uses System Tags (PROC-012) + Custom Tags (MOD-013)
```

### Failure flows
- **Delete System Tag → blocked (BDR-015/REQ-020).**
- **Out-of-scope admin action → 403 (role model deferred).**
- **Invalid user/role data → 422.**

---

## F. Business Rules

1. **System Tags are immutable — cannot be deleted (BDR-015 / REQ-020).**
2. **Custom Tags belong only to the creator (BDR-015; MOD-013).**
3. **V1 access model: SA + CO (BDR-020); Team roles (MG/SE) activate later.**
4. **Detailed role/permission matrix deferred to future sprint (Open Question)** —
   do not invent granular permissions.
5. **Marketers own relationships (BDR-003)** — users ↔ relationships governed by
   assignment (MOD-003).

---

## G. States and Lifecycle

User/Role lifecycle per role definitions + BDR-020:

```text
User: Invited/Provisioned -> Active -> Deactivated (role model deferred)
System Tag: Created -> Immutable (never deleted)
```

| Attribute | Value |
|---|---|
| User states | Provisioned → Active → Deactivated (exact transitions per role model — deferred) |
| System Tag states | Created → immutable (delete blocked) |
| Allowed transitions | Manage users/roles; create System Tags |
| Forbidden transitions | Delete System Tag; granting roles outside approved model |
| Trigger / Actor / Result | Admin actions (SA/CO); System enforces |

> Do NOT build a full RBAC UI until the access-control matrix is defined
> (Open Question). V1: minimal users/roles/tenancy.

---

## H. Timeline Integration

| Attribute | Value |
|---|---|
| Event type produced | **No direct Timeline events** (MOD-012 definition: "No (or indirect)") |
| Indirect | User/role changes may surface in audit trail, not Timeline |
| Consumes | None |

---

## I. Audit Integration

- User/role/tag changes are auditable (NFR-004) per `100-audit-philosophy.md`;
  audit-logs UI is a future enhancement (MOD-012).

---

## J. Data Model

Physical tables: **`users`**, **`roles`**, **`system_tags`** (+ pivot tables)
(from `080-table-catalog.md`)

| Element | Value |
|---|---|
| Primary key | `id` |
| Business identifier | `user_id`/`role_id`/tag id (standard) |
| Foreign keys | `tenant_id` (users), role assignments |
| Tenant ownership | Yes |
| Soft delete | users: deactivated flag per role model; system tags: **never deleted** (BDR-015) |
| Archive | N/A |
| Versioning | N/A |
| Audit | Yes |
| Indexes | `tenant_id`, `email`/`username`, role |
| Constraints | System Tag delete prevented at DB/service layer |

> Confirm exact columns in `080-table-catalog.md`. Keep V1 minimal per BDR-020.

---

## K. Laravel Implementation

| Component | Expected |
|---|---|
| Controllers | `Http/Controllers/Admin/UserController`, `RoleController`, `SystemTagController`. |
| Form Requests | User/role/tag requests (validate per scope). |
| Services | `Admin\\Services\\AdminService` (user/role/tag management, immutability enforcement). |
| Models | `User`, `Role`, `SystemTag`. |
| Policies | `AdminPolicy` (SA/CO per BDR-020). |
| Middleware | Tenant scope; auth guard. |
| Routes | Admin web/API routes (foundation); API admin endpoints per deferred matrix — keep minimal. |
| Views/components | Admin user list, role management (deferred UI), System Tag management. |
| Tests | See §Q. |

---

## L. API Specification

Admin endpoints are **not enumerated** in the 140–250 API set; authentication
per `020-authentication.md`, authorization per `030-authorization.md` + BDR-020.
Treat admin as web-scaffolded (Blade) in V1; API surface per the deferred
access-control matrix.

---

## M. Validation

- Validate user/role/tag data; email uniqueness; tenant membership.
- System Tag delete → rejected (BDR-015).
- Authorization per SA/CO roles (BDR-020).

---

## N. Error Handling

401/403/404/409 (role conflicts)/422/429/5xx per `060-error-handling.md`.
System Tag delete attempt → business error.

---

## O. Security

- Auth: session or API key.
- Authorization: SA (platform), CO (company); tenant isolation (BDR-021).
- System Tags immutable (BDR-015).
- User/role management is sensitive — strictest controls.

---

## P. UI/UX

Per `docs/06-UI-UX/180-settings-screen.md` (settings overlaps MOD-013) and
`010-workspace-specification.md`:
- Admin area: users, roles, system tags.
- Confirmations for destructive actions; System Tag delete unavailable/blocked.
- Empty states; responsive/accessible per standard.

---

## Q. Testing

- Unit: user/role management; System Tag immutability.
- Feature: create user, assign role; delete System Tag attempt blocked.
- API/security: out-of-scope admin 403; tenant isolation.
- Audit: admin changes logged.
- Edge: duplicate email; deactivated user re-auth; System Tag used by entities.

---

## R. Acceptance Criteria

- [ ] Administrators manage marketers, roles, System Tags (REQ-019).
- [ ] System Tags cannot be deleted (REQ-020, BDR-015).
- [ ] V1 access model per BDR-020; tenant isolation (BDR-021).
- [ ] No invented granular permissions (matrix deferred).

---

## S. Developer Checklist

- **Backend:** Admin controllers/services/models; policies.
- **Database:** users/roles/system_tags per catalog (V1 minimal).
- **Authorization:** SA/CO roles; tenant middleware.
- **System Tags:** immutability enforced (delete blocked).
- **Audit:** admin actions.
- **Frontend:** admin UI (minimal V1), settings link.
- **Testing:** §Q.
- **Documentation:** update VISICORE-MODULE-INDEX.md.

---

## Module Dependencies

- **Depends on:** Identity/tenancy foundation (authn/authz).
- **Used by:** All modules (users/roles/tags cross-cutting); MOD-009 (catalog
  administration), MOD-013 (settings), MOD-005 (tagging on knowledge).
- **Produces:** none direct.
- **Consumes:** none.

> Cross-cutting — no dependency cycles. Foundation module.

---

## Open Questions

| Question | Why it matters | Source documents checked | Possible impact |
|---|---|---|---|
| Access-control matrix / role-permission model (deferred) | RBAC + API surface | REQ-019 note; MOD-012 Open Questions; permission matrix | Authorization engine |
| Exact user/role state transitions | Lifecycle | `020-role-definitions.md` | User model |
| System Tag management scope | Admin UI | MOD-012 def; PROC-012 | UI + validation |

---

*Source documents remain authoritative. Follow the BDR registry and the frozen
architecture (v1.0) when implementing.*