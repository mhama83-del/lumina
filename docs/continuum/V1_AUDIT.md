# V1 Audit (Phase 0)

## Stack
`codeigniter4/appstarter`, PHP `^8.2`, `codeigniter4/framework ^4.7`, PHPUnit `^10.5`, Faker. 124 PHP
files, session-driven controllers, no `.env`/vendor/base schema/test suite in the archive.

## Reusable / refactor (imported via read-only adapters or reworked services)
- Five EDGE signals → canonical `EdgeSignal` enum.
- 13-question survey situations → reworked into ONE canonical 15-question bank (`Config\EdgeSurvey`),
  option scoring and WorkAnimal coupling removed.
- `Edge.php` dual-map concept → new `MeridianMapService` + accessible SVG.
- `TaxonomyService` nodes/aliases/edges → curated `taxonomy_skills`/`taxonomy_relations` with
  source/version/status (staging|approved).
- Evidence labels → single `EvidenceLabelPolicy`.
- Employer role/requirement schema → `roles`/`role_versions`/`role_requirements` with
  Critical/Important/Supporting + immutable versions.

## Must be pensioned (prohibited — deleted from active V2, never relabelled)
Confirmed present in the archive and excluded from V2:
`WorkAnimal`, `AnimalInferenceService`, `PotentialProfileService`, `ScoreService`
(readiness/match/employability/risk), `TalentMatchService` (ranking + related-skill half-credit),
`LearningVelocityService`; session role/share/shortlist; `employer/compare`, `employer/shortlist`
routes. Grep of the archive found 245 references to rank/shortlist/employability/animal concepts.

## Technical / security / data risks (corrected in V2)
- `csrf`, `honeypot`, `secureheaders` were commented out of global `Filters` → re-enabled (D-005).
- CORS was broad → explicit allowlist.
- V1 `edge` action could mark self-added material as Supported → `EvidenceLabelPolicy` blocks
  self-promotion to Supported without an approved source, and to Human Verified without a verifier.
- Inconsistent readiness/match formula paths → ONE `RerEngine`.
- Synthetic JD data described as "real" → all fixtures carry `data_classification=synthetic_fixture`.

## Migration plan (strangler)
Preserve V1 untouched; build V2 modules independently; import only cleaned assets via
fixtures/adapters; map legacy role requirements into drafts requiring human review; keep 15 curated
scenarios + 1,000+ background rows as scale-only fixtures. `candidate_role_matches` is archived, not
migrated (not an application).
