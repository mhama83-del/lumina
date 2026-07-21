# Continuum V2 — Decision Log

Format: `[D-nnn] date — decision — rationale — Build Pack refs`

## D-001 — 2026-07-21 — Retain CodeIgniter 4 stack, add module overlay
V1 is `codeigniter4/appstarter` on `php ^8.2`, `codeigniter4/framework ^4.7` (confirmed in `composer.json`).
Per `03_SYSTEM_ARCHITECTURE.md` we keep the stack and build a **modular monolith** under
`app/Modules/*` with PSR-4 namespace `Continuum\`. V1 code is preserved untouched as legacy reference;
no V1 controller/service/session/scoring code is extended.

## D-002 — 2026-07-21 — Domain engines are framework-independent pure PHP
`RerEngine`, `EvidenceLabelPolicy`, `ApplicationStateMachine`, `StaleDetection` and all enums have
**zero** CodeIgniter dependency. Reason: (a) `05_EDGE_AND_RER_ENGINES.md` is the second-highest
precedence source and correctness is non-negotiable; (b) keeps the formula in exactly one place
(`09_SECURITY_QUALITY_AND_TESTS.md`: "No duplicated formula"); (c) makes the engines unit-testable
without booting the framework or a database. CI4 models/controllers wrap these engines.

## D-003 — 2026-07-21 — RER credit sourced only from confirmed evidence sufficiency
Graph relations (`TaxonomyRelation`) never contribute RER credit — they only emit
`QuestionToConfirm` prompts. Implemented as a hard boundary: `RerEngine` accepts a
`RequirementSufficiency` value object whose sufficiency is derived solely from
`EvidenceLabelPolicy`, never from taxonomy edges. Ref `02_NON_NEGOTIABLES.md`, `05` §3.

## D-004 — 2026-07-21 — Consent is an immutable per-application, per-role-version snapshot
`ConsentSnapshot` stores `allowed_claim_ids` + `preview_hash`. The employer review payload is
rebuilt from the same allowed set that produced `preview_hash`, guaranteeing
"candidate preview == employer view" (`13_ACCEPTANCE_CHECKLIST.md`). Ref `04_DATA_MODEL_AND_CONSENT.md`.

## D-005 — 2026-07-21 — Security baseline corrected from V1
V1 had `csrf`, `honeypot`, `secureheaders` commented out of global `Filters` and a broad CORS.
V2 enables CSRF + secure headers on browser POST routes and uses an explicit CORS allowlist.
Ref `08_LEGACY_V1_MIGRATION.md`, `09_SECURITY_QUALITY_AND_TESTS.md`.

## D-006 — 2026-07-21 — Scenario Switcher is demo-only context swap, never authorization
`DEMO_MODE` gates the switcher. It swaps the active demo identity through `PolicyContext`; every
sensitive read/write still runs the full policy + consent check. Ref `07`, `03`, `10`.
