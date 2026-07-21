<?= $this->extend('continuum/_layout') ?>
<?= $this->section('content') ?>
<div class="pagehead"><div class="eyebrow">Scenario switcher · demo</div><h1>Choose a persona</h1>
  <p class="sub">Enter any workspace to see their view. Switching swaps context only — it is never an authorisation mechanism.</p></div>
<?php foreach (['candidate'=>'Candidates','employer'=>'Employers','university'=>'University','operator'=>'Talentbank operator'] as $roleVal=>$grp): ?>
  <div class="card"><h3><?= esc($grp) ?></h3>
    <div class="persona-grid">
    <?php foreach ($identities as $key=>$p): if ($p['role']->value!==$roleVal) continue;
      $ini = strtoupper(substr($p['name'],0,1) . substr(strrchr($key,'_') ?: '_x',1,1)); ?>
      <a class="persona-card" href="/demo/scenarios/<?= esc($key) ?>">
        <span class="avatar"><?= esc($ini) ?></span>
        <span class="who"><b><?= esc($p['name']) ?></b><span><?= esc($key) ?> · <?= esc($p['discipline']) ?></span></span>
      </a>
    <?php endforeach; ?>
    </div>
  </div>
<?php endforeach; ?>
<?= $this->endSection() ?>
