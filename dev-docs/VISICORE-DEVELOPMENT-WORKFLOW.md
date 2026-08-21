# VisiCore Development Workflow

> **Type:** Implementation standard (day-to-day process)
> **Status:** USE WHEN BUILDING · **Compiled:** 2026-08-18
> Consolidates the WWDF git workflow, VisiCore engineering docs, and the repo's
> AGENTS.md. Run commands from `laravel-visicore-app/` unless noted.

---

## 1. Environment Setup

- **MariaDB running** for dev DB work (PHP lacks `pdo_sqlite`):
  `sudo systemctl start mariadb`.
- First-time setup: `composer run setup` (installs deps, creates `.env`, key,
  migrates, builds assets).
- Node local: `export PATH="$HOME/.local/node/bin:$PATH"` if `node`/`npm`/`npx`
  are missing.
- Dev servers: `composer run dev` (artisan dev) + `npm run dev` (Vite).

## 2. Day-to-Day Loop

1. **Plan from the docs.** Source of truth: `docs/` (BDRs, REQs, PROCs, MOD
   records, API contracts) + `00-WWDF/` standards. Read the relevant MOD-XXX
   implementation record before coding.
2. **Implement per standards.** `LARAVEL-DEVELOPMENT-STANDARD.md` (backend),
   `VISICORE-UI-UX-STANDARD.md` (frontend). Thin controllers → Services →
   Events → Timeline projection.
3. **Verify.**
   - `composer test` (clears config, runs PHPUnit on in-memory SQLite);
     focus: `php artisan test --filter=...`.
   - `./vendor/bin/pint` (code style).
   - `npm run build` / `npm run dev` for frontend.
   - No CI — verification is manual every time.
4. **Update docs** as needed (VISICORE-MODULE-INDEX.md, MOD-XXX checklist).

## 3. Git Workflow

Per `docs/09-Development/160-git-workflow.md`, `170-branching-strategy.md`,
`180-commit-message-standard.md`, `00-WWDF/150-git-workflow.md`:

- **Branching:** feature/`MOD-XXX`-short-name from `main`/`develop`; one
  branch per module/feature. Never commit directly to the default branch.
- **Commits:** conventional style, present tense, focused; reference the MOD/
  REQ where relevant. No secrets.
- **Before commit:** `git status`, `git diff`, `git log --oneline -10`.
- **PR/merge:** verify tests + Pint + build; use the PR checklist
  (`190-pull-request-checklist.md`) and code-review checklist
  (`200-code-review-checklist.md`).

## 4. Definition of Done

Per `docs/09-Development/220-definition-of-done.md` + module §R/§S checklists:

- Feature implemented per MOD-XXX record; API per the contract; UI per spec.
- Tests written and green (`composer test`).
- Pint clean; build passes; manual smoke check (incl. light/dark, no-JS flows).
- No invented business rules (source IDs only; Open Questions surfaced).
- Docs (index + module record) updated.

## 5. Build Order Guardrails

- Follow `docs/09-Development/implementation/030-module-build-order.md`:
  Foundation (tenancy+auth+access) → MOD-001 → MOD-003 → MOD-002 → MOD-006 →
  MOD-004 → MOD-005 → MOD-007 → MOD-008 → MOD-009 → MOD-011 → MOD-010 →
  MOD-012/013.
- **Timeline (MOD-002) must exist before event-producing modules ship.**
- Never build a dependent module before its prerequisite.

## 6. Testing Strategy

Per `docs/09-Development/210-testing-strategy.md` + module §Q:

- Unit (services/enums/policies) + Feature (endpoints/UI) + API contract tests.
- Use factories; in-memory SQLite for tests; no external services in tests
  (providers stubbed behind adapters).
- Security tests for authorization/tenant isolation are mandatory.

## 7. Traceability & Decision Hygiene

- Reference **real IDs only** (REQ/BDR/PROC/MOD/NFR). When a source is silent,
  say "Source identifier not defined" or add to Open Questions — never invent.
- BDR conflicts are **reported, not guessed**.
- Open questions (M-1…M-21) stay visible in module records until resolved.

---

*Authoritative sources: `docs/09-Development/`, `00-WWDF/`, and this repo's
AGENTS.md.*