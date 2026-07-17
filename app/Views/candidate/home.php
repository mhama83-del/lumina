<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php helper('ui'); ?>
<section class="hero">
  <h1>Welcome.</h1>
  <p class="lead">This is your candidate hub. Analyse a resume, build one from scratch even with no experience, then see your readiness and the roles you fit.</p>
  <div class="row" style="margin-top:16px;gap:10px;flex-wrap:wrap">
    <a class="btn btn-primary btn-lg" href="<?= base_url('resume') ?>">Analyze my resume →</a>
    <a class="btn btn-ghost" href="<?= base_url('onboard/input') ?>">No resume? Build one →</a>
  </div>
</section>

<?php if (!empty($profile)):
    $svc  = new \App\Services\ScoreService();
    $dom  = $profile['target_domain'] ?? 'Data';
    $cand = [
        'skills'     => $profile['skills'] ?? [],
        'top_domain' => $dom,
        'verified'   => $profile['verified'] ?? 0,
        'projects'   => 1, 'activities' => 1, 'pace' => 'Steady',
    ];
    $role = match ($dom) {
        'Engineering' => ['title' => 'Backend Engineer',  'domain' => 'Engineering', 'required' => ['software', 'cloud', 'python', 'communication']],
        'Business'    => ['title' => 'Product Executive', 'domain' => 'Business',    'required' => ['stakeholder_mgmt', 'communication', 'leadership']],
        'Design'      => ['title' => 'UX Designer',       'domain' => 'Design',      'required' => ['ui_ux', 'figma', 'design_thinking', 'communication']],
        default       => ['title' => 'Data Analyst',      'domain' => 'Data',        'required' => ['sql', 'dashboarding', 'python', 'data_analysis']],
    };
    $readiness = $svc->readiness($cand, $role);
    $animal    = ! empty($profile['animal']) ? \App\Libraries\WorkAnimal::get($profile['animal']) : null;
?>
<section class="section">
  <div class="section-label">Your Living Portfolio · from your last analysis</div>
  <div class="grid grid-3">
    <div class="card"><div class="donut-wrap"><?= lumina_donut((int) $readiness['score'], 'Adaptive Readiness', 'var(--indigo)') ?></div></div>
    <div class="card">
      <h3>Living Portfolio</h3>
      <p class="muted"><?= esc($dom) ?> track</p>
      <?php foreach (array_slice($profile['skills'] ?? [], 0, 4, true) as $code => $s): ?>
        <?= lumina_skill(\App\Libraries\Catalog::label($code), $s['source'] ?? 'inferred') ?>
      <?php endforeach; ?>
      <div style="margin-top:10px"><a class="btn btn-ghost" href="<?= base_url('passport') ?>">Open portfolio →</a></div>
    </div>
    <?= lumina_kpi($animal['label'] ?? '—', 'Work Animal', 'from your evidence') ?>
  </div>
</section>
<?php endif; ?>

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
