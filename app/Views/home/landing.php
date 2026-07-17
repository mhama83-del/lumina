<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<!-- Hero (§5.1) -->
<section class="hero">
  <div class="section-label">Built for Talentbank Career OS</div>
  <h1>Make potential readable — before the perfect resume.</h1>
  <p class="lead">Lumina turns a young person's evidence into a clearer profile, career direction and next action.</p>

  <div class="row" style="margin-top:18px;gap:10px;flex-wrap:wrap">
    <a href="#" class="btn btn-primary btn-lg" data-tour="1">Start the guided demo</a>
    <a href="<?= base_url('demo/candidate-' . (session('stage') ?? '19-22')) ?>" class="btn btn-ghost">Explore as Candidate</a>
  </div>

  <p class="muted" style="font-size:12px;margin-top:16px">Synthetic demo data · Decision support only · People decide</p>
</section>

<!-- Problem + journey visual (§5.2) -->
<section class="section">
  <div class="card">
    <p class="lead" style="margin:0">Students aren't invisible because they lack talent — they're invisible because their <strong style="color:var(--text)">evidence is scattered</strong>.</p>
  </div>

  <div class="section-label" style="margin-top:18px">How Lumina connects the dots</div>
  <div style="display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin-top:8px">
    <span class="skill">Your evidence</span><span class="muted">&rarr;</span>
    <span class="skill">Lumina EDGE Profile</span><span class="muted">&rarr;</span>
    <span class="skill">Career Compass</span><span class="muted">&rarr;</span>
    <span class="skill">Smart Matching</span><span class="muted">&rarr;</span>
    <span class="skill">Career Action Journey</span>
  </div>
</section>

<!-- Persona ecosystem (§5.3) -->
<section class="section">
  <div class="section-label">One evidence trail. Three decisions.</div>
  <a class="card" href="<?= base_url('demo/candidate-' . (session('stage') ?? '19-22')) ?>" style="text-decoration:none;display:block;margin-bottom:14px">
    <h2 style="margin-bottom:4px">Candidate</h2>
    <p class="lead" style="margin:0">Understand potential and choose a direction.</p>
  </a>
  <div class="grid grid-2">
    <a class="card card-tight" href="<?= base_url('demo/employer') ?>" style="text-decoration:none">
      <h3>Employer</h3><p class="muted" style="margin:0">Review evidence before the interview.</p>
    </a>
    <a class="card card-tight" href="<?= base_url('demo/university') ?>" style="text-decoration:none">
      <h3>University</h3><p class="muted" style="margin:0">See where support is needed.</p>
    </a>
  </div>
</section>

<!-- Talentbank Career OS value (kekal) -->
<section class="section">
  <div class="section-label">A Career Intelligence layer for Talentbank Career OS</div>
  <p class="muted" style="margin-bottom:14px">Lumina plugs into Talentbank to enrich every stakeholder with deeper, evidence-backed intelligence — beyond resumes and keywords.</p>
  <div class="grid grid-4">
    <div class="card"><h3>Candidate Intelligence</h3><p class="muted">Richer profiles — strengths, evidence, readiness and career intent.</p></div>
    <div class="card"><h3>Matching Intelligence</h3><p class="muted">Explainable matching using skills, evidence strength and trajectory.</p></div>
    <div class="card"><h3>Employer Intelligence</h3><p class="muted">Clearer comparisons, evidence confidence and focused interview questions.</p></div>
    <div class="card"><h3>University Intelligence</h3><p class="muted">Cohort readiness, aspiration trends and where support is needed.</p></div>
  </div>
</section>

<!-- SDG thin impact line -->
<section class="section">
  <p class="muted" style="font-size:12px;margin:0">Opportunity for all — SDG 4 Quality Education · SDG 8 Decent Work · SDG 10 Reduced Inequalities.</p>
</section>
<?= $this->endSection() ?>
