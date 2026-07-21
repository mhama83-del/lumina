# Continuum V2 — Known Limits & REQUIRES TALENTBANK VALIDATION

## REQUIRES TALENTBANK VALIDATION
- **Passport API**: `PassportAdapter` is a mock returning fixtures only. Auth model, canonical
  profile IDs, field ownership, consent/retention agreement, webhooks/rate limits, and job
  publishing integration are all unconfirmed. No real HTTP call is made. (`14_API_AND_ADAPTER_CONTRACTS.md`)
- **Talentbank adoption**: UI must read "Continuum — proposed for Talentbank". No adoption claimed.
- **Labour-market / trend data**: `TREND_DATA_ENABLED=false`. No live market data. Any trend view
  must carry source, owner, refresh date and confidence before enabling.

## Not runnable in the build environment (run in your CI4 project)
- Composer packages (`vendor/`) and MySQL are not reachable here, so full CI4 model/migration/HTTP
  tests were not booted in this environment. The **framework-independent domain tests were executed
  here and pass** (see handoff). CI4-layer tests run with `vendor/bin/phpunit` after `composer install`.

## P1 / not in this P0 slice
- Mentoring workflow (interface + minimal only); paid mentor commerce is P2.
- Notification centre (event types are recorded; delivery UI is minimal).
- Extraction/resume-import adapter is a suggestion placeholder requiring candidate confirmation.
- Multi-university tenancy rollout and exports are P1/P2.
- Email/SMS delivery is adapter-mocked.
