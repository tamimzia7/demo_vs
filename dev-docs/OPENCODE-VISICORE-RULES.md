# OpenCode VisiCore Rules

> **Purpose:** Rules for AI-assisted (OpenCode) development in this repository.
> **Status:** USE ALWAYS · **Compiled:** 2026-08-18
> Complements the repo's `AGENTS.md` (layout/commands) and the standards it
> points to.

---

## 1. Never Invent

- Only use **real source identifiers** (REQ/BDR/PROC/MOD/NFR). When a source is
  silent, write "Source identifier not defined in available documentation." or
  raise it as an Open Question — **do not fabricate IDs, rules, or statuses.**
- Conflicts between BDR and other docs are **reported**, never resolved by
  guessing.

## 2. Source of Truth Hierarchy

1. `docs/01-Business/100-business-decision-records.md` (BDRs — top authority).
2. `docs/05-Product-Blueprint/080-module-definition-records.md` (MODs),
   `docs/02-Requirements/` (REQ/NFR).
3. `docs/03-Business-Processes/` (PROCs).
4. `docs/04-Architecture/`, `docs/07-API/`, `docs/06-UI-UX/`.
5. `00-WWDF/` standards + the frozen architecture (v1.0).
6. The committed skeleton (`laravel-visicore-app/`) is ground truth for stack.

## 3. Work Location

- Run **all** `php`, `composer`, `npm`, `artisan` commands from
  `laravel-visicore-app/`. Treat the repo root as a thin wrapper.
- Dev DB = MySQL; tests = in-memory SQLite. MariaDB must run for artisan DB work.

## 4. Module Work

- Read the module's implementation record (`docs/MOD-XXX-*.md`) before coding;
  it maps §A–§S (overview → acceptance criteria → checklist) to source docs.
- Build per `LARAVEL-DEVELOPMENT-STANDARD.md` and
  `VISICORE-UI-UX-STANDARD.md`. Keep the Timeline (MOD-002) append-only; only
  producing services write events (BDR-011/012).

## 5. Verification (mandatory, no CI)

- `composer test` (and targeted `php artisan test --filter=...`).
- `./vendor/bin/pint`.
- `npm run build` (frontend).
- Report failures; fix before finishing. Update `VISICORE-MODULE-INDEX.md`
  when a module's status/checklist changes.

## 6. Scope Discipline

- **Documentation effort:** only create/modify the specified docs — never touch
  application code.
- **Implementation effort:** only the module/feature requested; flag anything
  requiring a decision (Open Questions) instead of inventing.
- Keep Open Questions (M-1…M-21) visible; close them only when a decision
  exists.

## 7. Output Style

- Concise; no emojis; no speculative URLs.
- Reference code as `file:line`.
- Report what changed, tests run, and any open questions.

---

*Authoritative: this repo's AGENTS.md, `docs/`, and `00-WWDF/`.*