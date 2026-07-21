<?= $this->extend('continuum/_layout') ?>
<?= $this->section('content') ?>
<div class="decision"><h2>Control Tower — stale exceptions</h2>
<p class="small">Remind or escalate. You never decide who gets hired.</p></div>
<div class="card">
<?php if (! $exceptions): ?><div class="empty">No stale exceptions. 🎉</div><?php endif; ?>
<?php foreach ($exceptions as $e): ?>
  <div class="req-row">
    <div><strong>Application #<?= esc($e['application_id']) ?></strong> · <?= esc($e['candidate']) ?>
      <div class="small muted">State: <?= esc($e['state']) ?> · expected update <?= esc($e['expected_update_at']) ?> (overdue)</div>
      <div class="small muted">Candidate sees: "<?= esc($e['candidate_message']) ?>"</div></div>
    <form method="post" action="/operator/exceptions/<?= esc($e['application_id']) ?>/remind">
      <?= csrf_field() ?><button class="cta" type="submit">Remind current owner</button></form>
  </div>
<?php endforeach; ?></div>
<?= $this->endSection() ?>
