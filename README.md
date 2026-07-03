# Lumina — Fasa 0–2 (PHP · CodeIgniter 4 · MySQL)

Starter files for the Lumina prototype, matching the demo's navy editorial style.
**Module names use our own (Living Portfolio · Smart Matching · Learning Velocity · Adaptive Readiness · University Intelligence)** — confirmed not mandatory by Talentbank's Build Phase Briefing.

## What's included (Fasa 0–2)

| Fasa | Delivered |
|---|---|
| **0 — Design system** | `public/css/app.css` (tokens + components), `app/Helpers/ui_helper.php`, `layouts/main.php`, `public/js/app.js`, `/styleguide` page |
| **1 — DB + engine** | `database/schema.sql` (13 tables), `database/seed_sample.sql`, `app/Services/ScoreService.php`, `app/Libraries/Explain.php`, models, `/selftest` page |
| **2 — Shell + entry + login** | top bar + role/stage switcher, `Home`, `Demo` (one-click login), landing page, role landings, stubs (no 404s) |
| **3 — Candidate cold-start** | Work Animal onboarding, evidence input (paste / transcript / 5Q), **Living Portfolio** + inferred skills + readiness donut + **Why? drawer** |
| **4 — Career Compass** | 3 path cards, **What-If simulator** (AJAX, donut animates), trajectory chart, 30/60/90 plan |
| **5 — Smart Matching + Employer** | candidate Best/Growth/Stretch cards + Why drawer; **Employer dashboard** ranks DB students + "Why this candidate?" (reason + evidence + 3 interview Qs) |
| **6 — Graduate Outcomes Dashboard** | University: 8 KPI cards, faculty bar, student bands doughnut, outcome heatmap, recommended intervention |
| **7 — Guided Demo Mode** | Cross-page **10-step guided tour** — walks a judge through the golden path; page stays interactive |
| **8 — Polish & acceptance** | **Impact/SDG section** (4·8·10), mobile fixes (responsive pillars, scrollable heatmap), judge-friendly CTA, **video script + acceptance checklist** (VIDEO_SCRIPT.md) |

## Install

1. Create the CI4 base: `composer create-project codeigniter4/appstarter lumina`
2. **Copy these files over** the appstarter (same paths): `app/…`, `public/css`, `public/js`, `database/…`.
3. Create a MySQL DB in Hostinger hPanel, then import:
   ```
   mysql -u USER -p DBNAME < database/schema.sql
   mysql -u USER -p DBNAME < database/seed_sample.sql
   ```
4. Edit `.env`: set `app.baseURL`, `database.default.*`, `CI_ENVIRONMENT = production`.
5. Local run: `php spark serve` → open `http://localhost:8080`.
6. Hostinger: point the subdomain document root to `public/`.

## Verify Fasa 0–2 (Definition of Done)

- [ ] `/styleguide` matches the demo look on desktop + mobile  → **Fasa 0 done**
- [ ] `/selftest` shows Aiman's readiness + a **positive** what-if delta  → **Fasa 1 done**
- [ ] Top-bar buttons enter Candidate/Employer/University in one click; stage dropdown loads a persona; no 404s  → **Fasa 2 done**
- [ ] `/start` → pick Work Animal / input / sample; `/onboard/animal` → result; `/onboard/input` (paste, transcript, 5Q) → `/passport` populated; donut + inferred chips + working **Why?** drawer  → **Fasa 3 done**
- [ ] `/compass` → 3 path cards; tick a gap skill → **readiness donut animates up** + delta; trajectory chart + 30/60/90 update  → **Fasa 4 done**
- [ ] `/match` → 3 Best/Growth/Stretch cards + **Why this match?** drawer; `/employer` → pick role → candidates re-rank + **Why this candidate?**  → **Fasa 5 done**
- [ ] `/university` → 8 KPI cards, faculty bar, segmentation doughnut, outcome heatmap, intervention  → **Fasa 6 done**
- [ ] Click **▶ Guided tour** → 10-step tour auto-walks Home → Passport → Compass (What-If) → Match → Employer → University → Home; page stays clickable  → **Fasa 7 done**
- [ ] Homepage shows SDG line + Impact section; pillars wrap on mobile; heatmap scrolls on mobile; run the full acceptance list in `VIDEO_SCRIPT.md`  → **Fasa 8 done · PROTOTYPE COMPLETE**

`/selftest` expected output (engine verified):
```
Readiness 58%  ·  Match 62% (stretch)  ·  What-if 58% -> 79% (+21)
```

## Notes

- **Demo numbers:** the engine is honest. To match the "72% → 84%" storyline in the deck, tune Aiman's seed (give one more matched skill, or raise verified/projects/activities) — adjust `seed_sample.sql`, not the formula.
- **Scale data:** expand `students` to ~1,500 with the prompt at the bottom of `seed_sample.sql`.
- **Work Animal:** the personas use placeholder animals (owl/fox/eagle). Replace with the real traits from yourworkanimal.com before Fasa 3.
- **AI:** fully simulated in `ScoreService` — no external calls, per Talentbank rules.

## Done 🎉

All 8 phases complete. Before submitting: replace Work Animal traits (yourworkanimal.com), scale sample students to ~1,500, record the walkthrough video (see `VIDEO_SCRIPT.md`), run the acceptance checklist.
