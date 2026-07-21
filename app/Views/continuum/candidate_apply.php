<?= $this->extend('continuum/_layout') ?>
<?= $this->section('content') ?>
<div class="pagehead"><div class="eyebrow">Candidate · apply</div><h1>Apply &amp; consent</h1>
  <p class="sub">This is <strong>exactly</strong> what the employer will see. Raw survey answers are never shared.</p></div>
<div class="card"><h3>Employer preview</h3>
<?php if (! $preview): ?><div class="empty">No shareable evidence yet. Add source-backed evidence before applying.</div><?php endif; ?>
<?php foreach ($preview as $p): ?>
  <div class="req-row"><div class="lead"><strong><?= esc($p['claim_text']) ?></strong>
    <?php if ($p['source_excerpt']): ?><div class="small muted">Source: <?= esc($p['source_excerpt']) ?></div><?php endif; ?></div>
    <?= $this->include('continuum/_label_chip', ['label'=>$p['label']]) ?></div>
<?php endforeach; ?>
<form method="post" action="/candidate/roles/data-analyst/apply" style="margin-top:16px">
  <?= csrf_field() ?>
  <p class="small muted">Availability: <strong>Actively available</strong> · consent expires in 30 days · role-version bound.</p>
  <button class="cta" type="submit">Submit application with consent</button>
</form></div>
<?= $this->endSection() ?>
