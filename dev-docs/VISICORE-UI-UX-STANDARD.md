# VisiCore UI/UX Standard

> **Type:** Implementation standard (frontend)
> **Status:** USE WHEN BUILDING · **Compiled:** 2026-08-18
> Sources: `docs/06-UI-UX/*` (workspace spec, navigation, widgets, screens),
> `00-WWDF/05-Development-Standards/04-frontend-standards.md`, and the live
> design system already committed in
> `laravel-visicore-app/resources/css/app.css`.

---

## 1. Stack (already present)

- **Blade-first, server-rendered** (`00-WWDF` frontend standard): the server is
  the source of truth; forms post and redirect.
- **Tailwind 4** via `@tailwindcss/vite`; **Alpine 3** for progressive
  enhancement (never to enable core function).
- **Inter Variable** (`@fontsource-variable/inter`).
- **No SPA by default** — a client-side router requires an approved ADR.

## 2. Design Language (frozen in `app.css`)

> The committed `app.css` IS the standard — do not rebuild it. Use its tokens.

- **System:** "Refined Neumorphic" — Cool Slate + Indigo, light-only default,
  ~70% flat / 30% raised, with a token-based **dark theme** override under
  `html[data-theme='dark']`.
- **Tokens (`@theme`):**
  - Surfaces: `surface #e7ebf1`, `raised #f3f6fa`, `inset #e2e6ee`,
    `hairline`.
  - Ink: `ink-900/700/600/400/300`.
  - Accent: `accent-700/600/500/200/100/50`.
  - Status: `success`, `warning`, `danger`, `info`, `vip` (+ `-soft` variants).
  - Elevation: `--shadow-raise*`, `--shadow-inset*` (neumorphic).
- **Base:** `color-scheme: light`; `scroll-behavior: smooth`; Inter with
  `cv02/cv03/cv04/cv11`; thin scrollbars; `:focus-visible` outline uses accent.
- **Components (all in `app.css`):** `.surface-raised/.surface-inset`,
  `.card/.card-sm`, `.btn` variants (`.btn-primary/.btn-soft/.btn-ghost/
  .btn-danger`, sizes `.btn-sm/.btn-md`, `.btn-icon`), `.field-input/.input/
  .select/.textarea`, `.label/.hint/.field-error`, `.badge*`, `.dot`, `.avatar`,
  `.alert-*`, `.nav-item/.nav-divider`, `.tab-btn/.tabs-scroll`, `.switch/.seg`,
  `.table`, `.timeline-well/.timeline-rail/.timeline-item/.timeline-medallion/
  .timeline-card`, `.modal-mask/.modal-card`, `.menu/.menu-item`, `.toast*`,
  `.page-btn`, `.skeleton`, `.chip`, `.eyebrow`, `.icon` (stroke system).
- **Reduced motion:** `@media (prefers-reduced-motion: reduce)` blanket disable.

## 3. Rules

1. **Use the design system tokens/classes from `app.css`.** No ad-hoc hex colors
   or new shadow values — add tokens to `@theme` only when genuinely needed.
2. **Blade-first + progressive enhancement** — forms work without JS; Alpine
   only enhances (dropdowns, modals, toasts, dynamic panels).
3. **Business identifiers in UI, never internal IDs** (WWDF; BDR-018 — show VIN
   `VC-YYYY-NNNNNN`, records like `REL-…`, never the numeric PK).
4. **Field order/labels follow user mental models**, not DB columns (WWDF).
5. **Empty states are required** per `docs/06-UI-UX/050-empty-state-philosophy.md`:
   "No X yet." + a next-action primary button.
6. **Notifications** per `040-notification-philosophy.md`; use `.toast-*`
   overlays and the Notification Center (MOD-004/`240-notification-api.md`).
7. **Widgets** from `030-widget-library.md`: Action Bar, Action Button, Summary
   Card, Filter Bar, Timeline, List/Table, Badge, Avatar — consistent per screen.
8. **Timeline (signature)** per `090-timeline-specification.md`: newest first,
   `.timeline-*` components, User vs System events distinguished (badge/icon).
9. **Responsive/accessible/loading states:** use `.skeleton` shimmer, semantic
   HTML, `:focus-visible`, `prefers-reduced-motion`; pages remain usable without
   JS.
10. **Navigation** per `020-navigation-flow.md`: primary spine (Dashboard,
    Visitors, Relationships, Offerings, Reports, Administration, Settings) with
    the active item as `.nav-item.is-active`.
11. **No comments** in UI unless the surrounding file already explains itself;
    keep Blade partials small and reusable only where duplication is real.

## 4. Screen Conformance

Each screen follows its spec in `docs/06-UI-UX/`:
dashboard `070-`, visitor workspace `080-`, timeline `090-`, visit `100-`,
communication center `110-`, knowledge center `120-`, purchase `130-`,
expense `140-`, offering `150-`, reporting dashboard `160-`, subscription
`170-`, settings `180-`. Build them with the components in §2.

## 5. Verification

- `npm run dev` / `npm run build` (Vite) must pass.
- Check light + dark (`html[data-theme='dark']`), reduced motion, and
  no-JS operation on the key flows.

---

*The committed `resources/css/app.css` and `docs/06-UI-UX/` remain
authoritative.*