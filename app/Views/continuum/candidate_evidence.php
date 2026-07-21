<?= $this->extend('continuum/_layout') ?>
<?= $this->section('content') ?>
<div class="decision"><h2>My Evidence</h2>
<p class="small">The dashed line is what you've reflected on. The filled shape is what you've backed with a source.</p></div>
<?= $this->include('continuum/_meridian', ['map'=>$map]) ?>
<div class="card"><h3>Evidence items</h3>
<?php if (! $claims): ?><div class="empty">No evidence yet. Add an example to get started.</div><?php endif; ?>
<?php foreach ($claims as $c): ?>
  <div class="req-row">
    <div><strong><?= esc($c['claim_text']) ?></strong>
      <div class="small muted"><?= esc(ucwords(str_replace('_',' ',$c['signal']))) ?></div></div>
    <?= $this->include('continuum/_label_chip', ['label'=>$c['label']]) ?>
  </div>
<?php endforeach; ?>
<a class="cta" href="/candidate/roles/data-analyst">Review a role's requirements →</a></div>
<?= $this->endSection() ?>
