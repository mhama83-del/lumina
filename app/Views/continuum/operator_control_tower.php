<?= $this->extend('continuum/_layout') ?>
<?= $this->section('content') ?>
<div class="pagehead"><div class="eyebrow">Talentbank operator</div><h1>Control tower</h1>
  <p class="sub">Resolve stale applications by reminding or escalating. The operator never decides who gets hired.</p></div>
<div class="card">
<?php if (! $exceptions): ?><div class="empty">No stale exceptions right now — every active application is within its expected update window.</div><?php endif; ?>
<?php foreach ($exceptions as $e): ?>
  <div class="req-row"><div class="lead">
    <strong>Application <span class="mono">#<?= esc($e['application_id']) ?></span> · <?= esc($e['candidate']) ?></strong>
    <div class="small muted">State <?= esc($e['state']) ?> · expected update <span class="mono"><?= esc($e['expected_update_at']) ?></span> · <span style="color:var(--attention)">overdue</span></div>
    <div class="small faint">Candidate sees: "<?= esc($e['candidate_message']) ?>"</div></div>
    <form method="post" action="/operator/exceptions/<?= esc($e['application_id']) ?>/remind">
      <?= csrf_field() ?><button class="cta" type="submit">Remind current owner</button></form></div>
<?php endforeach; ?></div>
<?= $this->endSection() ?>
