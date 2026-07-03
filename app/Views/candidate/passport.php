<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
helper('ui');
$p = $profile;
$riskClass = $risk === 'On track' ? 'ok' : ($risk === 'Needs a nudge' ? 'nudge' : 'risk');
$evidenceLines = array_filter(array_map('trim', preg_split('/[;.]/', $p['evidence_text'] ?? '')));
$skillTotal = count($p['skills'] ?? []);
$skillInferred = 0; foreach (($p['skills'] ?? []) as $s) { if (($s['source'] ?? '') === 'inferred') $skillInferred++; }
?>

<section class="hero">
  <div class="section-label">Living Portfolio · Stage <?= esc($p['stage'] ?? '19-22') ?></div>
  <h1><?= esc($p['name'] ?? 'You') ?>'s Living Portfolio</h1>
  <p class="purpose">This is your living portfolio — built from what you've already done.<?php if (!empty($p['animalLabel'])): ?> Work style: <strong class="gold"><?= esc($p['animalLabel']) ?></strong>.<?php endif; ?></p>
</section>

<section class="section" style="padding-top:6px">
  <?= lumina_journey('portfolio') ?>
  <?= lumina_note("We read your evidence and found {$skillTotal} skills — {$skillInferred} you didn't even list.") ?>
  <div class="grid grid-3">

    <!-- Readiness -->
    <div class="card">
      <div class="section-label">Adaptive Readiness</div>
      <div class="donut-wrap"><?= lumina_donut($readiness['score'], $role['title'], $role['color']) ?></div>
      <div class="row" style="justify-content:center;margin-top:8px">
        <span class="pill <?= $riskClass ?>"><?= esc($risk) ?></span>
      </div>
      <div class="row" style="justify-content:center;margin-top:12px">
        <button class="btn btn-ghost" data-why="1">Why this score?</button>
      </div>
    </div>

    <!-- Skills -->
    <div class="card">
      <div class="section-label">Skill signals</div>
      <h3 style="margin-bottom:6px">Inferred + stated</h3>
      <p class="purpose">Dashed = inferred by Lumina from your evidence.</p>
      <div>
        <?php foreach (($p['skills'] ?? []) as $code => $s):
            echo lumina_skill(ucwords(str_replace('_', ' ', $code)), $s['source'], $s['confidence']);
        endforeach; ?>
      </div>
    </div>

    <!-- Evidence + match preview -->
    <div class="card">
      <div class="section-label">Evidence</div>
      <?php foreach ($evidenceLines as $line): ?>
        <div class="ev">• <?= esc($line) ?></div>
      <?php endforeach; ?>
      <div style="margin-top:14px">
        <div class="section-label">Smart Matching preview</div>
        <p class="muted"><strong style="color:var(--text)"><?= esc($role['title']) ?></strong> — <?= (int)$match['matchScore'] ?>% match (<?= esc($match['label']) ?>).
        <?php if (!empty($match['gap'])): ?> Gap: <?= esc(implode(', ', array_map(fn($g)=>ucwords(str_replace('_',' ',$g)),$match['gap']))) ?>.<?php endif; ?></p>
      </div>
    </div>

  </div>

  <div class="row" style="margin-top:18px">
    <a class="btn btn-gold btn-lg" href="<?= base_url('compass') ?>">See my career paths →</a>
    <a class="btn btn-ghost" href="<?= base_url('start') ?>">Rebuild</a>
  </div>
</section>

<!-- Why drawer -->
<div class="drawer-backdrop" id="whyBackdrop"></div>
<aside class="drawer" id="whyDrawer" aria-label="Why this score">
  <div class="row" style="justify-content:space-between">
    <h3>Why this readiness?</h3>
    <button class="btn btn-ghost" data-close="1">✕</button>
  </div>
  <p class="muted"><?= esc($whyText) ?></p>
  <div style="margin-top:10px">
    <div class="subscore"><span class="muted">Skill coverage (40%)</span><strong><?= (int)$readiness['coverage'] ?></strong></div>
    <div class="subscore"><span class="muted">Evidence strength (25%)</span><strong><?= (int)$readiness['evidence'] ?></strong></div>
    <div class="subscore"><span class="muted">Activity signals (20%)</span><strong><?= (int)$readiness['activity'] ?></strong></div>
    <div class="subscore"><span class="muted">Learning pace (15%)</span><strong><?= (int)$readiness['pace'] ?></strong></div>
  </div>
  <p class="purpose" style="margin-top:14px">Decision support only. The number rises as you close gaps — try it in Career Compass.</p>
</aside>

<?= $this->endSection() ?>
