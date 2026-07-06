<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php helper('ui'); $p = $persona ?? []; ?>
<section class="hero">
  <div class="section-label">Candidate · Stage <?= esc(session('stage') ?? '19-22') ?></div>
  <h1>Welcome<?= !empty($p['name']) ? ', ' . esc($p['name']) : '' ?>.</h1>
  <p class="lead">This is your candidate hub. Analyse a resume, build one from scratch even with no experience, then see your readiness and the roles you fit.</p>
  <div class="row" style="margin-top:16px;gap:10px;flex-wrap:wrap">
    <a class="btn btn-gold btn-lg" href="<?= base_url('resume') ?>">Analyze my resume →</a>
    <a class="btn btn-ghost" href="<?= base_url('onboard/input') ?>">No resume? Build one →</a>
  </div>
</section>

<section class="section">
  <div class="grid grid-3">
    <div class="card"><div class="donut-wrap"><?= lumina_donut((int)($p['readiness'] ?? 72), 'Adaptive Readiness', 'var(--indigo)') ?></div></div>
    <div class="card">
      <h3>Living Portfolio</h3>
      <p class="muted"><?= esc($p['university'] ?? 'USM') ?> · <?= esc($p['programme'] ?? 'Computer Science') ?></p>
      <?php foreach (($p['skills'] ?? ['Python' => 'stated', 'Teamwork' => 'stated']) as $s => $src): ?>
        <?= lumina_skill($s, $src) ?>
      <?php endforeach; ?>
      <div style="margin-top:10px"><a class="btn btn-ghost" href="<?= base_url('passport') ?>">Open portfolio →</a></div>
    </div>
    <?= lumina_kpi(($p['workAnimal'] ?? 'The Owl'), 'Work Animal', 'from your evidence') ?>
  </div>
</section>

<section class="section">
  <div class="section-label">What you can do</div>
  <div class="grid grid-3">
    <a class="card" href="<?= base_url('resume') ?>" style="text-decoration:none">
      <h3>Analyze my resume <span class="gold">· AI</span></h3>
      <p class="muted">Paste a resume — Lumina extracts skills, your 12-type Work Animal, readiness, role matches, feedback and the next best action.</p>
    </a>
    <a class="card" href="<?= base_url('onboard/input') ?>" style="text-decoration:none">
      <h3>No resume? Build one</h3>
      <p class="muted">Answer a short guided form → a Starter Living Portfolio, a suggested resume draft, and a first project to ship.</p>
    </a>
    <a class="card" href="<?= base_url('passport') ?>" style="text-decoration:none">
      <h3>My Living Portfolio</h3>
      <p class="muted">Your evidence, inferred skills and readiness in one view — with a transparent "why" behind every score.</p>
    </a>
    <a class="card" href="<?= base_url('compass') ?>" style="text-decoration:none">
      <h3>Career Compass</h3>
      <p class="muted">See realistic career paths, how ready you are for each, and the exact gap that moves your number.</p>
    </a>
    <a class="card" href="<?= base_url('match') ?>" style="text-decoration:none">
      <h3>Smart Matches</h3>
      <p class="muted">Opportunities ranked by fit and trajectory — each with the reason why, not just a keyword match.</p>
    </a>
    <a class="card" href="<?= base_url('start') ?>" style="text-decoration:none">
      <h3>Discover my Work Animal</h3>
      <p class="muted">Not sure where to start? Take the quick tap-quiz to find your work style, then build from there.</p>
    </a>
  </div>
</section>
<?= $this->endSection() ?>
