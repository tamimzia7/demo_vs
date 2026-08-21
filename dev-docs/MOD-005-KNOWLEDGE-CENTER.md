# MOD-005 — Knowledge Center

> **Type:** Module Implementation Record (development-facing)
> **Status:** NOT_STARTED · **Compiled:** 2026-08-18
> Source documents remain authoritative; nothing here invents business rules.

---

## A. Module Overview

| Attribute | Value |
|---|---|
| **Module ID** | MOD-005 |
| **Module name** | Knowledge Center |
| **Purpose** | Manage and share referenced knowledge. |
| **Business objective** | Provide and control access to the right material. |
| **Business meaning** | The platform references knowledge (metadata + link + permission), never stores large files (BDR-009), and shares by **Visitor Identity Number** (VIN) Google-Docs style (BDR-010). |
| **Product Map position** | `VisiCore → Knowledge Center` (module-level) and per-visitor shared-items view. |
| **MVP/Post-MVP status** | **MVP** (basic knowledge sharing is in MVP scope per `020-mvp-definition.md`). |
| **Scope** | Knowledge Items (metadata, link, permission, history, version — **not files**); grant/revoke access by VIN; view items shared with a visitor; sharing history retained even after revoke. |
| **Non-scope** | Storing large files; private sharing of raw content bodies; versioning UI and provider integrations (future enhancement). |

---

## B. Source Traceability

| Concern | Source ID / Location |
|---|---|
| MOD definition | MOD-005 in `docs/05-Product-Blueprint/080-module-definition-records.md` |
| Requirements | REQ-010 (store metadata, not files), REQ-011 (share by VIN) in `req-mod-005-knowledge-center.md` |
| Business process | PROC-007 (Knowledge Sharing) |
| BDRs | BDR-009 (knowledge referenced, not stored), BDR-010 (share by VIN, Google-Docs style), BDR-016 (Knowledge Shared event) |
| NFRs | NFR-003 (data protection), NFR-010 (integration) |
| Data model | Knowledge Item entity + `knowledge_items` table (`020-entity-catalog.md`, `080-table-catalog.md`) |
| API | `docs/07-API/190-knowledge-api.md` |
| UI/UX | `docs/06-UI-UX/120-knowledge-center.md`, `010-workspace-specification.md` |
| Access control | Permission matrix (`040-permission-matrix.md`), visibility matrix, authorization philosophy; sharing model `070-sharing-model.md` |
| Architecture | Driver/adapter pattern for providers (`00-WWDF/04-Architecture/07-driver-adapter-pattern.md`) |
| Feature list | MOD-005 rows in `docs/FEATURE-LIST-WITH-USER-ROLES.md` |
| Reference | `docs/ULTIMATE-VISICORE-REFERENCE.md` |

---

## C. Role-Based Access

| Action | SA | CO | MG (target) | SE (target) | MO (future) |
|---|---|---|---|---|---|
| Manage Knowledge Items (create/version/link) | Global (platform scope) | Yes | Yes | Yes (own) | Planned |
| Grant/revoke access by VIN | Global read | Yes (company) | Yes (team) | Yes (own in scope) | Planned |
| View items shared with a visitor | Global read | Yes | Yes | Yes | Planned |
| Access control inherit | Via permission matrix | | | | |

**Restrictions**
- Grant/revoke limited to in-scope marketers (permission matrix MOD-005 row).
- Visitor-specific access governed by VIN; tenant-scoped (BDR-021).
- Knowledge content lives outside the app (BDR-009) — access is a permission
  record, not a file copy.

---

## D. Complete Feature Breakdown

### MVP (V1)

**F-018 — Create/Manage Knowledge Items**
- Behavior: Create item with metadata + link (REQ-010). Store **metadata,
  permission, history, version, and link** only; never large files.
- Rules: BDR-009 enforced at data layer (no file storage).
- Permissions: in-scope CO/MG/SE.

**F-019 — Share by VIN**
- Behavior: Grant access to a Knowledge Item by Visitor Identity Number
  (REQ-011, BDR-010, PROC-007 step 1).
- Rules: platform records **permission** (plus metadata/history/version/link);
  System-Generated **"Knowledge Shared"** Timeline Event is created (PROC-007
  step 3, BDR-016).
- Permissions: in-scope marketers.

**F-020 — Revoke by VIN**
- Behavior: Revoke access later; **sharing history retained** (PROC-007 step 4,
  exception case).
- Rules: revoke removes access; history persists (BDR-005/immutable timeline).
- Permissions: in-scope marketers.

**F-021 — View per-visitor shared items**
- Behavior: `GET /visitors/{vin}/knowledge` — items shared with a visitor
  (`190-knowledge-api.md`).
- Rules: shows referenced items for the visitor (not file bodies).
- Permissions: read per visibility matrix.

### Post-MVP / Future
- Versioning UI, provider integrations (MOD-005 definition "Future Enhancements").
- Group/segment sharing, expiry (Open Questions).

---

## E. Complete User Flow

```text
Marketer opens Knowledge Center
↓
Creates Knowledge Item (metadata + link) — no file upload
↓
Opens item → selects "Share" → enters Visitor Identity Number (VIN)
↓
System validates VIN exists and permission scope
↓
System records permission + metadata/history/version/link
↓
System creates "Knowledge Shared" Timeline Event (System-Generated)
↓
Visitor gains access via link
(Revoke later → access removed, sharing history retained)
```

### Failure flows
- **Invalid/unknown VIN → 404/422** (visitor identity check).
- **Out-of-scope grant/revoke → 403.**
- **Sharing target that does not exist (group/expiry) → not supported yet (open).**

---

## F. Business Rules

1. **Knowledge is referenced, not stored (BDR-009 / REQ-010).** Metadata,
   permission, history, version, link only — no large files.
2. **Share by VIN (BDR-010 / REQ-011).** Google-Docs-style access control.
3. **"Knowledge Shared" is a System-Generated event (BDR-016).**
4. **Revoke removes access, but sharing history remains (PROC-007).**
5. **Sharing history is retained** — timeline append-only (BDR-005).
6. **Access granularity (V1): single visitor via VIN.** Group/segment sharing and
   expiry are open questions (PROC-007).

---

## G. States and Lifecycle

Knowledge Item lifecycle (per `020-entity-catalog.md` + PROC-007):

```text
Created -> active (versioned)
Sharing per visitor:  Grant -> active -> Revoked (history retained, access removed)
```

Sharing states: **Granted → Revoked** (access removed; history kept). Item
status (draft/active/archived) not enumerated in source — use entity catalog;
do not invent beyond it.

| Attribute | Value |
|---|---|
| Item states | Created → active/history; per entity catalog |
| Sharing states | Granted → Revoked |
| Allowed transitions | Grant, Revoke (revoke irreversible on access; record kept) |
| Forbidden transitions | Deleting sharing history |
| Trigger / Actor / Result | Marketer grant/revoke; System records event |

---

## H. Timeline Integration

| Attribute | Value |
|---|---|
| Event type produced | **"Knowledge Shared"** — System-Generated (BDR-016; PROC-007 step 3) |
| Trigger | Grant of access by VIN (PROC-007) |
| User/System | System (recorded when marketer grants) |
| Actor | Marketer (grants) / System (records) |
| Visitor | The visitor whose VIN received access |
| Timestamp | ISO-8601 UTC |
| Append-only | Yes |
| Revoke | Access removed; event/history retained (PROC-007 exception) |

---

## I. Audit Integration

- Grants/revokes are auditable actions (NFR-004); recorded as Timeline Events +
  sharing history retained (`100-audit-philosophy.md`).
- Permission changes should be traceable to marketer + timestamp.

---

## J. Data Model

Physical tables: **`knowledge_items`** + sharing permission
(per `070-entity-to-table-mapping.md`, `080-table-catalog.md`)

| Element | Value |
|---|---|
| Primary key | `id` |
| Business identifier | `knw` (KNW-NNNNNN, `190-knowledge-api.md`) |
| Foreign keys | `tenant_id` (items); sharing rows: `item_id`, `visitor_vin` |
| Tenant ownership | Yes |
| Soft delete | per entity catalog (do not invent) |
| Archive | N/A |
| Versioning | Version tracked (REQ-010) |
| Audit | Grants/revokes |
| Indexes | `tenant_id`; `visitor_vin` (sharing lookup) |
| Constraints | link required; no file/blob column (BDR-009) |
| Derived fields | none |

> Do NOT redesign. Confirm exact table names in
> `070-entity-to-table-mapping.md` before implementing.

---

## K. Laravel Implementation

| Component | Expected |
|---|---|
| Controllers | `Http/Controllers/Knowledge/KnowledgeItemController` (create/share/revoke/visitor-list). |
| Form Requests | CreateKnowledgeItemRequest; ShareKnowledgeRequest (vin); revoke. |
| Resources | `KnowledgeItemResource` (knw, metadata, link, version); `VisitorKnowledgeResource`. |
| Services | `Knowledge\\Services\\KnowledgeService` (grant/revoke, side-effect event projection). |
| Models | `KnowledgeItem`, `KnowledgeSharedVisitor` (permission row). |
| Policies | `KnowledgeItemPolicy` (manage, share, read). |
| Middleware | Tenant scope. |
| Events | `KnowledgeShared`, `KnowledgeAccessRevoked` → Timeline. |
| Routes | `POST /knowledge-items`, `POST /knowledge-items/{knw}/share`, `DELETE /knowledge-items/{knw}/share/{vin}`, `GET /visitors/{vin}/knowledge`. |
| Views/components | Knowledge Center list, item form (metadata+link), share dialog (VIN input), visitor shared-items panel. |
| Tests | See §Q. |

---

## L. API Specification

From `docs/07-API/190-knowledge-api.md`.

| Method | URL | Purpose | Auth | Authz |
|---|---|---|---|---|
| POST | `/knowledge-items` | Create item (metadata + link) | Session/API key | In-scope marketer |
| POST | `/knowledge-items/{knw}/share` | Grant by VIN | Session/API key | In-scope marketer |
| DELETE | `/knowledge-items/{knw}/share/{vin}` | Revoke by VIN | Session/API key | In-scope marketer |
| GET | `/visitors/{vin}/knowledge` | Items shared with a visitor | Session/API key | Scope read |

**Response:** envelope `{data, meta}` (`050-request-response-standard.md`);
share returns the updated permission; create returns created item.
**Errors:** 401, 403, 404 (knw/vin), 422 (invalid VIN/link), 429, 5xx.

---

## M. Validation

- VIN validated against format + existing visitor (BDR-018 `VC-YYYY-NNNNNN`).
- Link required, validated URL per items.
- No file/blob payload allowed (REQ-010/BDR-009).
- Envelope/pagination per standards.

---

## N. Error Handling

401/403/404/422/429/5xx per `060-error-handling.md`. Provider/link failures
handled by adapters; do not leak provider errors.

---

## O. Security

- Auth: session or API key.
- Authorization: manage/share/revoke per scope; VIN binding.
- Tenant isolation (BDR-021).
- Only references stored — the actual files stay at the provider (minimal data
  footprint, NFR-003).
- Never expose provider secrets or raw links beyond authorized scope.

---

## P. UI/UX

Per `docs/06-UI-UX/120-knowledge-center.md`, `010-workspace-specification.md`:
- Knowledge Center list + item detail (metadata, link, version, share history).
- Share dialog: VIN entry; show added/revoked access.
- Per-visitor "shared with them" panel.
- Empty state: no items → create first item / share to a visitor.
- Notifications on share outcomes; responsive/accessible per standard.

---

## Q. Testing

- Unit: grant/revoke logic; BDR-009 no-file-in-db invariant; version handling.
- Feature: create item; share by VIN; revoke (history retained).
- API: all four endpoints per `190-knowledge-api.md`.
- Authorization: out-of-scope 403; invalid VIN 422.
- Timeline: "Knowledge Shared" System event appears.
- Audit: share/revoke trail retained.
- Edge: revoke then view history; share to same VIN twice (dedupe rule per
  source — if unspecified, Open Question).

---

## R. Acceptance Criteria

- [ ] Items store metadata, permission, history, version, link — never files
      (REQ-010, BDR-009).
- [ ] Grant/revoke by Visitor Identity Number works (REQ-011, BDR-010).
- [ ] "Knowledge Shared" System-Generated Timeline Event is created on grant
      (PROC-007, BDR-016).
- [ ] Revoking removes access but retains sharing history (PROC-007).
- [ ] Per-visitor shared-items view works and is scope-aware.

---

## S. Developer Checklist

- **Backend:** KnowledgeItemController; KnowledgeService; models; policy; events
  → Timeline.
- **API:** 4 endpoints per `190-knowledge-api.md`.
- **Database:** `knowledge_items` + permission rows (metadata/link only; no files).
- **Authorization:** manage/share/revoke scope; tenant middleware.
- **Timeline:** project "Knowledge Shared".
- **Audit:** grants/revokes.
- **Frontend:** list/detail, share dialog, visitor panel, empty state.
- **Testing:** §Q.
- **Documentation:** update VISICORE-MODULE-INDEX.md.

---

## Module Dependencies

- **Depends on:** MOD-001 (visitor context / VIN), external providers (via
  adapters). Independent of other modules' data.
- **Used by:** MOD-001 (Knowledge Center link + visitor panel), MOD-002 (its
  events), MOD-010 (aggregates).
- **Produces:** Timeline Events ("Knowledge Shared").
- **Consumes:** Visitor Identity Number (VIN); link metadata only.

> No dependency cycles — one-way event flow.

---

## Open Questions

| Question | Why it matters | Source documents checked | Possible impact |
|---|---|---|---|
| VIN format/structure (exact) | Sharing target binding | PROC-007 Open Questions; BDR-018 | Validation |
| Group/segment sharing? | Batch access | PROC-007 Open Questions | Schema + UI |
| Permission expiry? | Temporary access | PROC-007 Open Questions | Permission model |
| Item status enum (draft/active/archived) | Item lifecycle | `020-entity-catalog.md` | Migration + UI |

---

*Source documents remain authoritative. Follow the BDR registry and the frozen
architecture (v1.0) when implementing.*