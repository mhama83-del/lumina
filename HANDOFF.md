# Lumina Graph — Developer Handoff

**Product:** Lumina Graph — AI Talent Intelligence Layer for Asia's Career OS. Tagline: *Hire for Trajectory, Not Just History.* For Talentbank Tech Hackathon 2026.
**Live:** https://lumina.durianbytes.com  ·  **Repo branch:** v2  ·  code lives in this CI4 app.

## Stack & hosting
- CodeIgniter 4.7 · PHP 8.2 · MySQL · Chart.js (CDN). Vanilla PHP views, no build step.
- Hostinger shared hosting. SSH. Root: /home/u965880117/domains/lumina.durianbytes.com/ · App: app_ci4/ · docroot: app_ci4/public/.
- No login — 3 persona demo modes via Demo::enter() (session role).

## Deploy workflow (follow exactly)
1. Edit on the server (sandbox only drafts files).
2. Per change: backup (cp file file.bak.$(date +%s)) -> write -> php -l file -> php spark cache:clear -> git commit.
3. PAGE CACHE gotcha: pagecache filter caches full pages. After editing ANY view you MUST run `php spark cache:clear` or the old page keeps showing.
4. Prefer pasting `cat > path <<'EOF' ... EOF` heredoc blocks over SSH (uploads unreliable).
5. Rollback: git checkout <file> / *.bak.* / php spark migrate:rollback.

## Database
- Creds in app_ci4/.env (database.default.*). Tables: students(1,504), skills, student_skills, employer_feedback, resume_analyses, candidate_profiles, candidate_role_matches(extended), employers(100), employer_roles(1,000 JD), employer_skill_requirements(~9k), role_work_animal_fit, employer_shortlists.
- Schema: php spark migrate. Seed 1,000 JD (idempotent): php spark db:seed EmployerDatabaseSeeder. QC: php spark lumina:validate-employer-data. Matching test: php spark lumina:test-matching.
- Synthetic JD have is_synthetic=1; re-seed clears only those; never touches student/resume data.

## Architecture (app/)
- Controllers: Home (index, architecture=/how-it-works, styleguide, selftest), Demo, Candidate (home, start, sample, animal, input, passport, compass, whatif, smatch, resume, resumeAnalyze), Employer (index=browser, role, candidate=brief, compare, shortlist), University (dashboard, interventions), Api (analyzeResume, buildProfile, matchCandidates, compareCandidates, cohortInsight).
- Services: ScoreService (engine), ResumeParserService (delegates animals), RecommendationService, NoResumeProfileBuilderService, AnimalInferenceService (12 Work Animals), LearningVelocityService, TalentMatchService, UniversityInsightService. (Dead: EmployerComparisonService.)
- Libraries: Catalog, Explain, WorkAnimal(12). Helper: ui_helper. Config: Lumina.php (weights), Routes.php.
- Views reuse classes: card, card-tight, pill(ok/nudge/risk), skill(+inferred), kpi, ring, section-label, btn(btn-gold/btn-ghost), grid grid-2/3/4, donut-wrap. CSS vars: --indigo --teal --gold --violet --muted --line.

## Scoring
- Career Readiness = 40% skill + 25% evidence + 20% activity + 15% pace. Bands 0-49 At Risk / 50-74 Needs a Nudge / 75-100 On Track.
- Talent Match Signal = 40 Skill + 20 Evidence + 20 Learning velocity + 10 Work-Animal fit + 5 Domain + 5 CGPA. Bands 85+ Strong / 70-84 Good / 55-69 Potential / 40-54 Needs Development / <40 Weak.
- 12 Work Animals: Lion/Eagle/Wolf/Owl · Dolphin/Peacock/Elephant/Horse · Ant/Cheetah/Fox/Octopus. Evidence-based, safe-worded.

## DONE
Fasa 0-6 (persona switcher, resume analyzer+persist, no-resume builder, employer compare, 1,000-JD DB + TalentMatchService + browser/role/candidate-brief/compare, university dashboard+filter+interventions, 12 Work Animal engine, headless API, README_LUMINA_V2.md). Polish 1 homepage problem+flow; 2 demo/synthetic labels; 3 match legend; 4 university PDPA; 5 softened poor-fit label; 6 employer weighted Why + interview Qs + Candidate Brief; 9 university headline insight + intervention why; 10 /how-it-works System Design page + navbar link + footer v2.0.

## TODO
- Polish 7: candidate/passport.php — readiness formula visual (component x weight = score). Read the file first.
- Polish 8: candidate/compass.php — make 30/60/90 plan + "readiness +X if you add Y" prominent (controller already computes planFor/trajectoryFor).
- Optional QA cleanup: remove dead code (EmployerComparisonService + 5 unused employer models + welcome_message.php) + 27 *.bak.* files. Reversible via git.
- Calibration (optional): Evidence/Domain/CGPA saturate at 100 -> candidates look similar (~72). Temper evidenceStrength/cgpaFit to spread ranking; keep cross-domain matching.
- Data wording (optional): role_work_animal_fit.poor_fit_risk still blunt; reword in LuminaJdGenerator + re-seed.
- Perf note: Employer::role + University::dashboard(All) process all 1,504 students per load (~1-3s). Cache/cap if slow; do NOT domain-filter employer ranking (kills cross-domain).

## Guardrails
Additive-only DB. Never break Fasa 1-6. Reuse UI classes; keep dark theme. Safe non-biased wording. Don't over-engineer. Every deploy: backup + php -l + cache:clear + commit.
