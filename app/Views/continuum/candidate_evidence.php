<?= $this->extend('continuum/_layout') ?>
<?= $this->section('content') ?>
<div class="pagehead"><div class="eyebrow">Candidate · evidence</div><h1>My evidence</h1>
  <p class="sub">The dashed outline is what you've reflected on. The filled shape is what you've backed with a source.</p></div>
<?= $this->include('continuum/_meridian', ['map'=>$map]) ?>
<div class="card"><h3>Evidence items</h3>
<?php if (! $claims): ?><div class="empty">No evidence yet. Add an example with a source to start building your Meridian Map.</div><?php endif; ?>
<?php foreach ($claims as $c): ?>
  <div class="req-row"><div class="lead"><strong><?= esc($c['claim_text']) ?></strong>
    <div class="small muted"><?= esc(ucwords(str_replace('_',' ',$c['signal']))) ?></div></div>
    <?= $this->include('continuum/_label_chip', ['label'=>$c['label']]) ?></div>
<?php endforeach; ?>
</div>
<a class="cta" href="/candidate/roles/data-analyst">Review a role's requirements →</a>
<?= $this->endSection() ?>
