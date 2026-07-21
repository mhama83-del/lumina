<?= $this->extend('continuum/_layout') ?>
<?= $this->section('content') ?>
<div class="decision"><h2>Scenario Switcher</h2>
<p class="small">Demo only. Choose a persona to view their workspace. This swaps context, not permissions.</p></div>
<?php foreach (['candidate'=>'Candidates','employer'=>'Employers','university'=>'University','operator'=>'Talentbank operator'] as $roleVal=>$grp): ?>
  <div class="card"><h3><?= esc($grp) ?></h3>
  <?php foreach ($identities as $key=>$p): if ($p['role']->value!==$roleVal) continue; ?>
    <a class="cta secondary" style="margin:4px" href="/demo/scenarios/<?= esc($key) ?>">
      <?= esc($key) ?> · <?= esc($p['name']) ?> <span class="muted small">(<?= esc($p['discipline']) ?>)</span></a>
  <?php endforeach; ?></div>
<?php endforeach; ?>
<?= $this->endSection() ?>
