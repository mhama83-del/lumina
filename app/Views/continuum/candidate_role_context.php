<?= $this->extend('continuum/_layout') ?>
<?= $this->section('content') ?>
<div class="pagehead"><div class="eyebrow">Candidate · role context</div><h1><?= esc($role['title']) ?></h1>
  <p class="sub"><?= esc($version['summary']) ?></p></div>

<div class="card">
  <div class="statwrap">
    <div class="stat"><?= esc($readiness['rer']) ?><span class="unit">%</span></div>
    <div class="stat-note"><strong>Role Evidence Readiness</strong> — how much of this role's requirements
      your source-backed evidence covers. It is not a hiring score, and never ranks you against others.</div>
  </div>
</div>

<div class="card"><h3>Requirements &amp; your coverage</h3>
<?php foreach ($readiness['breakdown'] as $b):
  $pct = ($b['max']>0) ? round($b['sufficiency']/$b['max']*100) : 0;
  $tone = $pct>=67?'ok':($pct>=34?'warn':'low'); ?>
  <div class="req-row"><div class="lead">
    <strong><?= esc($b['requirement']) ?> <span class="pill <?= esc($b['importance']) ?>"><?= esc(ucfirst($b['importance'])) ?></span></strong>
    <div class="small muted"><?= esc($b['explanation']) ?></div>
    <div class="bar <?= $tone ?>"><span style="width:<?= $pct ?>%"></span></div>
  </div><div class="suff"><?= esc($b['sufficiency']) ?>/<?= esc($b['max']) ?></div></div>
<?php endforeach; ?></div>

<?php if ($readiness['questions']): ?>
<div class="card"><h3>Questions to confirm</h3>
<?php foreach ($readiness['questions'] as $q): ?>
  <div class="req-row"><div class="lead"><strong><?= esc($q['requirement']) ?></strong>
    <div class="small muted"><?= esc($q['question']) ?> <span class="faint">— <?= esc($q['reason']) ?></span></div></div></div>
<?php endforeach; ?></div>
<?php endif; ?>

<a class="cta" href="/candidate/roles/<?= esc($role['slug']) ?>/apply">Preview what the employer will see →</a>
<?= $this->endSection() ?>
