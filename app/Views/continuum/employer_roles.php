<?= $this->extend('continuum/_layout') ?>
<?= $this->section('content') ?>
<div class="pagehead"><div class="eyebrow">Employer</div><h1>Roles &amp; review queues</h1>
  <p class="sub">Review applicants humanely — labels are gates, never a candidate leaderboard.</p></div>
<div class="card">
<?php if (! $roles): ?><div class="empty">No roles yet.</div><?php endif; ?>
<?php foreach ($roles as $r): ?>
  <div class="req-row"><div class="lead"><strong><?= esc($r['title']) ?></strong>
    <div class="small muted">Lifecycle: <?= esc($r['lifecycle_status']) ?></div></div>
    <a class="cta secondary" href="/employer/roles/<?= esc($r['id']) ?>/review">Open review queue →</a></div>
<?php endforeach; ?></div>
<?= $this->endSection() ?>
