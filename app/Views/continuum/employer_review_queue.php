<?= $this->extend('continuum/_layout') ?>
<?= $this->section('content') ?>
<div class="decision"><h2>Review queue — <?= esc($role['title']) ?></h2>
<p class="small">Labels are <strong>gates</strong>, not a ranking. Consent &amp; availability are gates, never points.</p></div>
<div class="card">
<?php if (! $rows): ?><div class="empty">No applications yet.</div><?php endif; ?>
<?php foreach ($rows as $row): ?>
  <div class="req-row">
    <div><strong>Application #<?= esc($row['application_id']) ?></strong>
      <?php if (! empty($row['blocked'])): ?><div class="small muted">Consent absent/expired — candidate action needed.</div><?php endif; ?></div>
    <div>
      <span class="chip gate-<?= esc($row['queue_label']) ?>"><?= esc($row['queue_label_text']) ?></span>
      <?php if (empty($row['blocked'])): ?><a class="cta secondary" href="/employer/review/<?= esc($row['application_id']) ?>">Open →</a><?php endif; ?>
    </div>
  </div>
<?php endforeach; ?></div>
<?= $this->endSection() ?>
