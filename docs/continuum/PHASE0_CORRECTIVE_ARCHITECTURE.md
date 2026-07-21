# Phase 0 — Corrective Architecture (verified against V2.01 + V1)

Findings below were confirmed by reading the actual code, not assumed from the brief.

## Confirmed defects (now fixed in this pass)
| # | Defect (verified in code) | Severity | Fix |
|---|---|---|---|
| 1 | `Config\Continuum` parsed env flags with `(bool) env(...)`; `(bool) "false"` is TRUE, so `continuum.demoMode=false` did NOT disable the Scenario Switcher. | Blocker (security) | Strict `envBool()` parser (1/true/yes/on only). |
| 2 | `EvidenceLabelPolicy::sufficiency()` returned **2** for a `HumanVerified` label even with no valid verifier/source — silent Supported credit without a source. | Major (RER integrity) | HV now returns 3 only if verified, else degrades to 2 *only* with an approved source, else 1. New unit assertions added. |
| 3 | Routes `Candidate::survey` and `Candidate::addEvidence` existed but the controller methods did NOT — broken routes. | Major | Implemented both methods (real persistence). |
| 4 | `Demo` controller actions did not check `demoMode`; base controller trusted the demo session unconditionally. | Blocker (adoption) | Demo actions fail-closed when `demoMode=false`; base controller splits demo vs production identity resolution. |
| 5 | Seeder inserted **no** `survey_responses`, so the Meridian *reflection* layer was empty. | Major | 51 reflections seeded across candidates (c09 intentionally empty). |

## Preserve / Refactor / Migrate / Remove / Build
**Preserve (V1, untouched):** entire Lumina V1 on `main` + its DB as reference.
**Preserve (V2 engines, keep + test):** RER, queue gates, evidence-label policy (bug fixed),
application state machine, stale detection, consent preview/hash, Meridian aggregation.
**Refactor (from V1, governed):** CV/JD parsing → *suggestions only*; taxonomy extraction/aliases →
staging → reviewer approval → approved taxonomy; cohort aggregation pattern; 1,000+ rows → fixtures.
**Migrate safely:** V2 owns its own tables in a separate DB (`u965880117_continuum`); the one shared
name `taxonomy_skills` is intentionally kept V1-shaped (seeder inserts only id/code/label/domain).
**Remove / never reuse as behaviour:** WorkAnimal, old ScoreService formula, TalentMatch ranking,
LearningVelocity, auto-growing graph, shortlist/compare, session-role-as-auth.
**Build new (this + later phases):** survey flow (started), governed graph staging (P-next),
role composer publish (P-next), per-claim consent picker + immutable payload snapshot + hash verify +
revoke (P-next), stale scheduler command (P-next), human-review scorecard (P-next), real cohort
aggregation (P-next), control-tower expansion (P-next), production auth resolver (stubbed, fails closed),
mobile bottom-nav (P-next), FKs/constraints (P-next), integration tests (P-next).

## Broken/stub route inventory (V2.01)
- `POST /candidate/survey`, `POST /candidate/evidence/add` — **were** dead → now implemented.
- `POST /employer/roles/compose`, `POST /employer/roles/{id}/publish` — Role Composer is a **stub**
  (`NOT IMPLEMENTED`: no draft persistence or immutable version creation via UI; a published role is
  seeded so the rest of the loop works).
- `POST /employer/review/{id}/feedback` — inserts a feedback row but there is **no structured
  scorecard UI** yet (`NOT IMPLEMENTED`).
- University `cohort` aggregate is **partly hardcoded** in the controller (`NOT IMPLEMENTED`: real
  membership + computed aggregate).
- Operator stale detection runs **on page open**, not via a scheduled command (`NOT IMPLEMENTED`:
  idempotent scheduler; current sweep can emit duplicate stale events on refresh).

## Data ownership map (module → tables)
CorePolicy → audit_events · Evidence → survey_responses, evidence_claims/sources/links ·
Taxonomy → taxonomy_skills, taxonomy_relations · Roles → roles, role_versions, role_requirements ·
Applications → applications, application_events, consent_snapshots, availability_pulses ·
EmployerReview → human_reviews, feedback_records · University → cohorts, intervention_cases ·
Mentoring → mentor_requests · (candidates is shared read; writes via Evidence/Applications only).
Cross-module access must go through services; no raw cross-module SQL as a habit (some controllers
still query sibling tables directly — flagged for service extraction in P-next).

## Migration plan
Keep the single V2 schema migration authoritative. Next migration adds: FKs + ON DELETE rules,
`consent_snapshots.frozen_payload` (immutable preview JSON, not just claim ids), `roles.lifecycle`
draft support, and a `graph_staging` table for governed taxonomy. Seeder truncation must be guarded
to demo/test env only (P-next).
