<?= $this->extend('continuum/_layout') ?>
<?= $this->section('content') ?>
<div class="pagehead"><div class="eyebrow">Employer · review queue</div><h1><?= esc($role['title']) ?></h1>
  <p class="sub">Labels are gates, not a ranking. Consent &amp; availability are gates, never points.</p></div>
<div class="card">
<?php if (! $rows): ?><div class="empty">No applications yet.</div><?php endif; ?>
<?php foreach ($rows as $row): ?>
  <div class="req-row"><div class="lead">
    <strong>Application <span class="mono">#<?= esc($row['application_id']) ?></span></strong>
    <?php if (! empty($row['blocked'])): ?><div class="small muted">Consent absent or expired — candidate action needed.</div><?php endif; ?></div>
    <div style="display:flex;align-items:center;gap:10px">
      <span class="chip gate-<?= esc($row['queue_label']) ?>"><span class="dot"></span><?= esc($row['queue_label_text']) ?></span>
      <?php if (empty($row['blocked'])): ?><a class="cta secondary" href="/employer/review/<?= esc($row['application_id']) ?>">Open →</a><?php endif; ?>
    </div></div>
<?php endforeach; ?></div>
<?= $this->endSection() ?>
