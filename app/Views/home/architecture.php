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
