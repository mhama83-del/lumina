<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
helper('ui');
$layers = [
  ['1','Input Layer','Resume paste · No-Resume guided form · MyCSD transcript · Work Animal tap-quiz','indigo',''],
  ['2','Evidence Parser','Infers skills from free-text evidence; detects projects, leadership and activities — even with no resume.','indigo','AI-ready'],
  ['3','Skill Taxonomy + Work Animal Engine','Normalised skill vocabulary + 12-archetype behavioural signal (primary / secondary / growth).','teal','AI-ready'],
  ['4','Scoring Engines','Career Readiness · Learning Velocity · Talent Match Signal — deterministic and fully explainable.','teal',''],
  ['5','Data Layer','Candidate profiles + 1,000-JD synthetic employer database + 1,500-student cohort (MySQL).','gold',''],
  ['6','Matching Engine','Candidate ↔ role, with weighted contributions, skill overlap, gaps and reasons.','gold',''],
  ['7','Experience Layer','Candidate hub · Employer dashboard (browse/rank/compare) · University intelligence + interventions.','violet',''],
  ['8','Headless API','JSON endpoints (analyze-resume, build-profile, match-candidates, compare-candidates, cohort-insight).','violet','integration'],
  ['9','Human Decision','Every score is decision support. People make the final call.','green',''],
];
?>
<section class="hero">
  <div class="section-label">Under the hood · System Design</div>
  <h1>How Lumina works.</h1>
  <p class="lead">Lumina is an <strong class="gold">AI Talent Intelligence Layer</strong> — not a job portal. It reads scattered evidence, scores it transparently, and connects candidates, employers and universities. Designed to plug into Talentbank's Career OS, not compete with it.</p>
</section>

<!-- Architecture stack -->
<section class="section">
  <div class="section-label">Architecture — layered, explainable, API-first</div>
  <div class="stack" style="margin-top:10px">
    <?php foreach ($layers as $l): ?>
      <div class="card card-tight" style="display:flex;align-items:center;gap:14px;border-left:3px solid var(--<?= $l[3] ?>)">
        <div class="ring" style="flex:0 0 auto"><?= $l[0] ?></div>
        <div style="flex:1;min-width:0">
          <strong><?= esc($l[1]) ?></strong>
          <?php if ($l[4]): ?><span class="skill inferred" style="font-size:10px;margin-left:6px"><?= esc($l[4]) ?></span><?php endif; ?>
          <div class="muted" style="font-size:13px"><?= esc($l[2]) ?></div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
  <p class="muted" style="font-size:12px;margin-top:8px">Data flows top → bottom; every layer is independently swappable. The scoring layer exposes its math, so nothing is a black box.</p>
</section>

<!-- Scoring models -->
<section class="section">
  <div class="grid grid-2">
    <div class="card">
      <div class="section-label">Career Readiness</div>
      <p style="font-size:14px">Skill coverage <strong>40%</strong> + Evidence <strong>25%</strong> + Activity <strong>20%</strong> + Learning pace <strong>15%</strong></p>
      <p class="muted" style="font-size:13px">Bands: 0–49 At Risk · 50–74 Needs a Nudge · 75–100 On Track.</p>
    </div>
    <div class="card">
      <div class="section-label">Talent Match Signal</div>
      <p style="font-size:14px">Skill <strong>40%</strong> + Evidence <strong>20%</strong> + Learning velocity <strong>20%</strong> + Work-Animal fit <strong>10%</strong> + Domain <strong>5%</strong> + CGPA <strong>5%</strong></p>
      <p class="muted" style="font-size:13px">Bands: 85+ Strong · 70–84 Good · 55–69 Potential · 40–54 Needs Development · &lt;40 Weak.</p>
    </div>
  </div>
</section>

<!-- LUMINA-MATH-START -->
<!-- The exact math -->
<section class="section">
  <div class="section-label">The exact math · every score is traceable</div>
  <p class="muted" style="font-size:13px;margin:-4px 0 6px">Read each engine top-to-bottom: <strong>the question it answers → the formula → a worked example with real numbers → where you see it in the product.</strong> No black box; every component is 0–100 and the weights sum to 1.</p>
  <div class="mathmap">Evidence in&nbsp; →&nbsp; 6 scoring engines&nbsp; →&nbsp; 3 decisions&nbsp;: &nbsp;Candidate direction · Employer shortlist · University intervention</div>
  <div class="grid grid-2">

    <div class="card">
      <div class="section-label">1 · Career Readiness</div>
      <p class="mathq"><span>Q.</span> Is this candidate ready for a <em>specific target role</em> — and what exactly holds the number back?</p>
      <div class="mathbox">Readiness = 0.40·coverage + 0.25·evidence
          + 0.20·activity + 0.15·pace

coverage = matched required skills ÷ required × 100
evidence = min(100, 25·verified + 10·projects + 3·#skills)
activity = min(100, 20·activities)
pace     = Fast 80 · Steady 60 · Building 40</div>
      <div class="mathbox eg">Worked example — Aiman, target Data Analyst
coverage 50 · evidence 85 · activity 60 · pace 60
= 0.40×50 + 0.25×85 + 0.20×60 + 0.15×60
= 20 + 21 + 12 + 9   →   62%  ·  Needs a nudge</div>
      <p class="muted" style="font-size:12px">Bands: 0–49 At Risk · 50–74 Needs a Nudge · 75–100 On Track. &nbsp;<strong>Shown on:</strong> candidate Living Portfolio → "Why this score?"</p>
    </div>

    <div class="card">
      <div class="section-label">2 · Employability</div>
      <p class="mathq"><span>Q.</span> Across a whole cohort, how employable is each student <em>regardless of one role</em> — so every faculty is judged fairly?</p>
      <div class="mathbox">Employability = min(100, 6·#skills + 8·verified
              + 9·projects + 8·activities + paceBonus)

paceBonus = Fast 18 · Steady 12 · Building 4</div>
      <div class="mathbox eg">Worked example — a thin profile
5 skills · not yet verified · 1 project · 1 activity · Building
= min(100, 6×5 + 8×0 + 9×1 + 8×1 + 4)
= 30 + 0 + 9 + 8 + 4   →   51  ·  Needs a nudge</div>
      <p class="muted" style="font-size:12px">Field-agnostic. &nbsp;<strong>Shown on:</strong> University dashboard segmentation (On track / nudge / at risk).</p>
    </div>

    <div class="card">
      <div class="section-label">3 · Learning Velocity</div>
      <p class="mathq"><span>Q.</span> How fast is this person <em>growing</em> — trajectory, not just where they are today?</p>
      <div class="mathbox">Velocity = 0.30·skillGrowth + 0.25·projComplexity
         + 0.20·recency + 0.15·diversity
         + 0.10·domainProgression

skillGrowth    = min(100, 12·#skills)
projComplexity = min(100, 25·projects)
recency        = Fast 90 · Steady 60 · Building 35
diversity      = min(100, 20·#distinct-skill-groups)
domainProg     = 75 if verified else 45</div>
      <div class="mathbox eg">Worked example — Aiman
10 skills · 2 projects · Steady · 3 groups · verified
skillGrowth 100 · projComplex 50 · recency 60
diversity 60 · domainProg 75
= 30 + 12.5 + 12 + 9 + 7.5   →   71  ·  High</div>
      <p class="muted" style="font-size:12px">Bands: ≥70 High · 45–69 Steady · &lt;45 Emerging. &nbsp;<strong>Feeds:</strong> 20% of the employer Talent Match Signal.</p>
    </div>

    <div class="card">
      <div class="section-label">4 · Talent Match Signal</div>
      <p class="mathq"><span>Q.</span> For a given job, how well does this candidate fit — on skills <em>and</em> trajectory — with a defensible reason?</p>
      <div class="mathbox">Match = 0.40·skill + 0.20·evidence + 0.20·velocity
      + 0.10·animalFit + 0.05·domainFit + 0.05·cgpaFit

skill     = 100·credit ÷ required  (exact 1.0 · graph-adjacent 0.5)
            + prefs + keyword hits            (cap 100)
evidence  = employability + numbers + action verbs  (cap 100)
velocity  = engine 3 above
animalFit = 100 primary · 85 secondary · 60 same-category
            · 50 acceptable · 0 poor-fit
domainFit = 100 same domain / programme · else 40
cgpaFit   = 100 meets min · scaled below · 70 unknown</div>
      <div class="mathbox eg">See it live — click <strong>"Why?"</strong> on any ranked candidate.
Lumina prints these six numbers for that exact
person against that exact role — nothing hidden.</div>
      <p class="muted" style="font-size:12px">Bands: 85+ Strong · 70–84 Good · 55–69 Potential · 40–54 Needs Development · &lt;40 Weak. &nbsp;<strong>Shown on:</strong> every employer role page.</p>
    </div>

    <div class="card" style="grid-column:1 / -1">
      <div class="section-label">5 · Opportunity sub-signals — the clickable cohort KPIs</div>
      <p class="mathq"><span>Q.</span> Beyond readiness, what is each student's exposure to <em>industry</em>, <em>high-income</em> paths and <em>entrepreneurship</em>?</p>
      <div class="mathbox">Industry exposure = min(100, 25·internship + 20·projects + 15·certs + 10·global)
High-income       = min(100, 15·high-value-skills + 20·certs + 25·high-income-domain)
Job-creator       = min(100, 20·entrepreneur + 20·innovation + 15·leadership + 10·projects)</div>
      <p class="muted" style="font-size:12px"><strong>Shown on:</strong> the University KPI cards — click any card to drill into the exact students behind that number.</p>
    </div>

  </div>
  <p class="muted" style="font-size:12px;margin-top:12px"><strong>Graph-adjacency:</strong> the Lumina Graph gives partial credit (0.5) when a candidate has skills that co-occur with a required one — rewarding trajectory over exact history. Every number above is reproduced in the "Why?" panels, so judges can verify any score by clicking.</p>
</section>
<!-- LUMINA-MATH-END -->
<!-- Three personas flow -->
<section class="section">
  <div class="section-label">One layer, three stakeholders</div>
  <div class="grid grid-3">
    <div class="card"><h3>Candidate</h3><p class="muted">Turns scattered evidence into a Living Portfolio, readiness score, career paths and matches — even with no resume.</p></div>
    <div class="card"><h3>Employer</h3><p class="muted">Ranks candidates on fit and trajectory with explainable scores, candidate briefs, compare and shortlist.</p></div>
    <div class="card"><h3>University</h3><p class="muted">Cohort readiness, no-resume risk, skill gaps and the single highest-impact intervention per programme.</p></div>
  </div>
</section>

<!-- AI disclosure + stack -->
<section class="section">
  <div class="grid grid-2">
    <div class="card" style="border-left:3px solid var(--gold)">
      <div class="section-label">AI disclosure</div>
      <p class="muted" style="font-size:14px">Today Lumina runs a <strong>deterministic, explainable scoring layer</strong> with simulated AI for the demo — every number is traceable. It is built API-first so real AI (LLM resume parsing, embedding-based skill matching) can be plugged into the Evidence Parser and Matching Engine <strong>without changing any caller</strong>.</p>
    </div>
    <div class="card">
      <div class="section-label">Tech stack</div>
      <p class="muted" style="font-size:14px">CodeIgniter 4.7 · PHP 8.2 · MySQL · Chart.js. Schema via <code>spark migrate</code>; 1,000 JD via <code>spark db:seed</code>; QC via <code>spark lumina:validate-employer-data</code>. Headless JSON API for integration.</p>
    </div>
  </div>
</section>

<!-- Integration -->
<section class="section">
  <div class="card">
    <div class="section-label">Plugs into Talentbank Career OS</div>
    <p style="font-size:14px">Lumina is an intelligence <strong>layer</strong>, not a portal. It can consume Talentbank's employer JD database and university student data through the same interfaces used here, and expose readiness + matching signals back to the Career OS — connecting employers and graduates across Asia with explainable, trajectory-based intelligence.</p>
    <div class="row" style="margin-top:12px">
      <a class="btn btn-gold" href="<?= base_url('/') ?>">← Back to Lumina</a>
      <a class="btn btn-ghost" href="<?= base_url('api/cohort-insight') ?>" target="_blank">See a live API response</a>
    </div>
  </div>
</section>
<?= $this->endSection() ?>
