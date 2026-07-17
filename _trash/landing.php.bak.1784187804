<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php helper('ui'); ?>

<section class="hero">
  <div class="section-label">AI Talent Intelligence Layer · Career OS</div>
  <h1>Discover your direction. Build your readiness. Match your future.</h1>
  <p class="lead">Lumina turns who you already are — even with no resume — into a career direction, a readiness score, and the next step. Then it shows employers why you fit.</p>
  <p class="muted" style="margin-top:6px">Opportunity for all — SDG 4, 8 &amp; 10.</p>

  <div class="row" style="margin-top:18px">
    <a href="#" class="btn btn-gold btn-lg" data-tour="1">▶ Start the 5-minute guided tour</a>
  </div>

  <div class="row persona-cta" style="margin-top:14px;gap:10px;flex-wrap:wrap">
    <a href="<?= base_url('demo/candidate-' . (session('stage') ?? '19-22')) ?>" class="btn btn-ghost">I am a Candidate</a>
    <a href="<?= base_url('demo/employer') ?>" class="btn btn-ghost">I am an Employer</a>
    <a href="<?= base_url('demo/university') ?>" class="btn btn-ghost">I am a University</a>
  </div>
</section>

<!-- Problem statement + journey flow -->
<section class="section">
  <div class="card" style="border-left:3px solid var(--gold)">
    <p class="lead" style="margin:0">Students aren't invisible because they lack talent — they're invisible because their <strong class="gold">evidence is scattered</strong>. Lumina turns scattered evidence into a career direction, a readiness score, and employer-ready matching.</p>
  </div>
  <div class="section-label" style="margin-top:18px">How Lumina connects the dots</div>
  <div style="display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin-top:8px">
    <span class="skill">No Resume</span><span class="muted">&rarr;</span>
    <span class="skill">Living Portfolio</span><span class="muted">&rarr;</span>
    <span class="skill">Readiness Score</span><span class="muted">&rarr;</span>
    <span class="skill">Career Path</span><span class="muted">&rarr;</span>
    <span class="skill" style="background:rgba(245,197,24,.15);border-color:rgba(245,197,24,.4)">Employer Match</span><span class="muted">&rarr;</span>
    <span class="skill" style="background:rgba(167,139,250,.15);border-color:rgba(167,139,250,.4)">University Intervention</span>
  </div>
</section>

<!-- 3-step explainer -->
<section class="section">
  <div class="grid steps">
    <div class="card"><?= lumina_ring('1','Build Profile','From a resume, pasted activities, a transcript, or just answers — even with no resume.','indigo') ?></div>
    <div class="card"><?= lumina_ring('2','Find Direction','See realistic paths, your readiness, the gap, and the next step that moves the number.','gold') ?></div>
    <div class="card"><?= lumina_ring('3','Match Opportunity','Get matched by readiness and trajectory — and let employers see the reasons why.','green') ?></div>
  </div>
</section>

<!-- Role entry -->
<section class="section">
  <div class="section-label">Choose how to explore</div>
  <div class="grid grid-3">
    <a class="card" href="<?= base_url('demo/candidate-' . (session('stage') ?? '19-22')) ?>" style="text-decoration:none">
      <h3>I am a Candidate</h3><p class="muted">Build a living portfolio and see your readiness and matches.</p>
    </a>
    <a class="card" href="<?= base_url('demo/employer') ?>" style="text-decoration:none">
      <h3>I am an Employer</h3><p class="muted">Find the right people — with the reasons why, and interview questions.</p>
    </a>
    <a class="card" href="<?= base_url('demo/university') ?>" style="text-decoration:none">
      <h3>I am a University</h3><p class="muted">See which talent is almost ready — and what unlocks them.</p>
    </a>
  </div>
</section>

<!-- Impact & sustainability -->
<section class="section">
  <div class="section-label">Impact &amp; sustainability</div>
  <div class="grid grid-3">
    <div class="card"><h3>SDG 4 · Quality Education</h3><p class="muted">Turns learning and activities into recognised, actionable skills.</p></div>
    <div class="card"><h3>SDG 8 · Decent Work</h3><p class="muted">Connects young people to suitable opportunities on evidence — faster.</p></div>
    <div class="card"><h3>SDG 10 · Reduced Inequalities</h3><p class="muted">No-resume mode lets capable students without polished CVs be seen.</p></div>
  </div>
</section>

<!-- Pillars -->
<section class="section">
  <div class="section-label">The Lumina engine</div>
  <div class="pillars">
    <?= lumina_chip('Living Portfolio','indigo') ?>
    <?= lumina_chip('Smart Matching','gold') ?>
    <?= lumina_chip('Learning Velocity','green') ?>
    <?= lumina_chip('Adaptive Readiness','teal') ?>
    <?= lumina_chip('University Intelligence','violet') ?>
  </div>
</section>
<?= $this->endSection() ?>
