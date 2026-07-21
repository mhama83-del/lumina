<?= $this->extend('continuum/_layout') ?>
<?= $this->section('content') ?>
<div class="decision"><h2>Your roles</h2><p class="small">Review applicants humanely — no candidate leaderboard.</p></div>
<div class="card">
<?php if (! $roles): ?><div class="empty">No roles yet.</div><?php endif; ?>
<?php foreach ($roles as $r): ?>
  <div class="req-row"><div><strong><?= esc($r['title']) ?></strong>
    <span class="small muted"><?= esc($r['lifecycle_status']) ?></span></div>
    <a class="cta secondary" href="/employer/roles/<?= esc($r['id']) ?>/review">Review queue →</a></div>
<?php endforeach; ?></div>
<?= $this->endSection() ?>
