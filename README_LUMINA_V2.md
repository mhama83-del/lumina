# Lumina Graph — System Documentation (v2)

**AI Talent Intelligence Layer for Asia's Career OS** · *Hire for Trajectory, Not Just History.*
Stack: CodeIgniter 4.7 · PHP 8.2 · MySQL · Chart.js. Host: lumina.durianbytes.com.
No login — three persona modes: **Candidate · Employer · University**.

---

## 1. Personas & entry
Homepage + navbar expose three buttons (*I am a Candidate / Employer / University*). Clicking sets a session `role` via `Demo::enter()` — no authentication. Same data, different views/actions per persona.

## 2. Route map
**Public / UI**
- `/` landing · `/selftest` engine self-check · `/styleguide`
- `demo/(:segment)` persona+stage switch
- **Candidate:** `/candidate` · `/start` · `/onboard/animal` · `/onboard/input` (guided/paste/transcript/5Q) · `/passport` (Living Portfolio) · `/resume` + `POST /resume/analyze` · `/compass` + `POST /whatif` · `/match`
- **Employer:** `/employer` (1,000-JD browser + filters) · `/employer/role/{id}` (JD + ranked candidates) · `/employer/compare?role_id=&ids[]=` · `/employer/shortlist`
- **University:** `/university` (cohort dashboard, filter by university) · `/university/interventions`

**Headless JSON API (Fasa 6.2)**
- `POST/GET /api/analyze-resume` — resume_text → full analysis
- `POST/GET /api/build-profile` — no-resume fields → starter profile
- `GET /api/match-candidates?role_id=&limit=` — rank cohort for a role
- `GET /api/compare-candidates?role_id=&ids[]=` — compare 2–4 candidates
- `GET /api/cohort-insight?uni=` — university cohort snapshot

## 3. Architecture
**Controllers:** Home, Demo, Candidate, Employer, University, Api.
**Services (`app/Services`):**
- `ScoreService` — skill inference, readiness, match, what-if, employability (engine core).
- `ResumeParserService` — projects/leadership/career-cluster extraction; delegates animals to AnimalInferenceService.
- `RecommendationService` — internships, feedback, next-best-action, micro-courses.
- `NoResumeProfileBuilderService` — resume draft, first project, activities.
- `AnimalInferenceService` — **12 Work Animal** inference (primary/secondary/growth + confidence + evidence + explanation + career fit + growth advice).
- `LearningVelocityService` — growth/complexity/recency/diversity/domain-progression.
- `TalentMatchService` — **Talent Match Signal** (employer side), reads DB roles.
- `EmployerComparisonService` — legacy compare (Fasa 4; superseded by TalentMatch on DB roles).
- `UniversityInsightService` — cohort snapshot + intervention plan.
**Libraries:** `Catalog` (sample role catalog + skill labels, fallback), `Explain` (why-panels), `WorkAnimal` (12-archetype tap-quiz).
**Models:** Student/Score/Skill/Role, ResumeAnalysis, CandidateProfile, Employer, EmployerRole, EmployerSkillRequirement, RoleWorkAnimalFit, CandidateRoleMatch, EmployerShortlist.
**Commands:** `lumina:validate-employer-data`, `lumina:test-matching`.

## 4. Database
Original: users, students, skills, student_skills, roles, role_skills, evidence, certifications, opportunities, matches, cohort_metrics, employer_feedback, student_scores.
Added (additive migrations, never destructive):
- `resume_analyses`, `candidate_profiles`, `candidate_role_matches` (extended with fit sub-scores + explanation)
- `employers`, `employer_roles`, `employer_skill_requirements`, `role_work_animal_fit`, `employer_shortlists`

## 5. Scoring models
**Career Readiness** = 40% skill coverage + 25% evidence + 20% activity + 15% learning pace → band: 0–49 At Risk · 50–74 Needs a Nudge · 75–100 On Track.
**Talent Match Signal (employer)** = 40% Skill + 20% Evidence + 20% Learning Velocity + 10% Work-Animal Fit + 5% Domain + 5% CGPA → 85+ Strong · 70–84 Good · 55–69 Potential · 40–54 Needs Development · <40 Weak.
Weights centralised in `app/Config/Lumina.php`.

## 6. Work Animal (12)
Leadership: **Lion** (Commander), **Eagle** (Visionary), **Wolf** (Pack Leader), **Owl** (Scholar).
Relational: **Dolphin** (Connector), **Peacock** (Performer), **Elephant** (Mentor), **Horse** (Loyalist).
Execution: **Ant** (Architect), **Cheetah** (Sprinter), **Fox** (Strategist), **Octopus** (Maker).
Inference is evidence-based and safe-worded ("your resume shows strong Lion signals…", never "you are permanently a Lion"). Candidate animals map 1:1 to the same 12 names used in employer `role_work_animal_fit`, so animal-fit scoring is exact.

## 7. Employer / JD dataset
1,000 synthetic JD generated deterministically by `LuminaJdGenerator` (seeded from the Gemini research: company matrix, taxonomy, animal fit, salary norms). Distribution: domain 320/300/250/130 · level Internship 350 / Fresh 350 / Grad-Trainee 150 / Junior 150 · 11 countries (MY 500) · 100 employers · ~9,000 skill requirements. QC via `php spark lumina:validate-employer-data` (18 rules PASS). All `is_synthetic=1`.

## 8. Deploy / operate
```bash
# migrations
cd app_ci4 && php spark migrate
# seed 1,000 JD (idempotent — clears only is_synthetic=1)
php spark db:seed EmployerDatabaseSeeder
# QC + matching test
php spark lumina:validate-employer-data
php spark lumina:test-matching
```
Every code change is deployed via a backup → write → `php -l` → git commit script. Git baseline lives in `app_ci4/`.

## 9. Rollback
- Code: `git checkout <file>` or `*.bak.<timestamp>` copies.
- Schema: `php spark migrate:rollback`.
- Employer data only: `DELETE FROM employers WHERE is_synthetic=1;` (cascades). Student/resume data never touched.

## 10. Suggested demo flow (for judges)
1. **Candidate (with resume):** `/resume` → *Use a sample* → live skill extraction, 12 Work Animal (primary/secondary/growth), readiness, matches, feedback, next action.
2. **Candidate (no resume):** `/onboard/input` → *Guided setup* → Starter Living Portfolio + resume draft.
3. **Employer:** `/employer` → filter (e.g. Data · Malaysia) → open a role → ranked candidates with explainable Talent Match + Compare 2–4 + Shortlist.
4. **University:** `/university` → pick a university → cohort readiness, No-Resume %, Work Animal distribution, skill gaps → **Generate Intervention Plan**.
5. **Headless:** `curl /api/analyze-resume?resume_text=...` → same intelligence as JSON.

## 11. Known limitations / next
- Student cohort evidence is repetitive (pre-existing seed) → Work Animal distribution concentrates on Wolf/Owl; richer student evidence would diversify it. The engine itself discriminates correctly on varied input.
- Employer `company_type` skews to large enterprises; add more Mid/SME/Startup employers to hit 450/250/200/100.
- Simulated (deterministic) AI throughout — designed to swap in live models/APIs later without changing callers.
