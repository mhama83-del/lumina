<?= $this->extend('continuum/_layout') ?>
<?= $this->section('content') ?>
<div class="decision"><h2>Apply &amp; consent</h2>
<p class="small">This is <strong>exactly</strong> what the employer will see. Raw survey answers are never shared.</p></div>
<div class="card"><h3>Employer preview</h3>
<?php if (! $preview): ?><div class="empty">No shareable evidence yet.</div><?php endif; ?>
<?php foreach ($preview as $p): ?>
  <div class="req-row">
    <div><strong><?= esc($p['claim_text']) ?></strong>
      <?php if ($p['source_excerpt']): ?><div class="small muted">Source: <?= esc($p['source_excerpt']) ?></div><?php endif; ?></div>
    <?= $this->include('continuum/_label_chip', ['label'=>$p['label']]) ?>
  </div>
<?php endforeach; ?>
<form method="post" action="/candidate/roles/data-analyst/apply">
  <?= csrf_field() ?>
  <p class="small">Availability: <strong>Actively available</strong> · Consent expires in 30 days.</p>
  <button class="cta" type="submit">Submit application with consent</button>
</form></div>
<?= $this->endSection() ?>
