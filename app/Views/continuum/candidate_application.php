<?= $this->extend('continuum/_layout') ?>
<?= $this->section('content') ?>
<div class="pagehead"><div class="eyebrow">Candidate · application</div><h1>Application timeline</h1></div>
<div class="card">
  <div style="display:flex;flex-wrap:wrap;gap:20px">
    <div><div class="small faint">Status</div><strong><?= esc($appView['state_label']) ?></strong></div>
    <div><div class="small faint">Last verified action</div><strong><?= esc($appView['last_verified_action']) ?></strong></div>
    <div><div class="small faint">Next owner</div><strong><?= esc($appView['next_owner'] ?? '—') ?></strong></div>
    <div><div class="small faint">Expected update</div><strong class="mono"><?= esc($appView['expected_update_at'] ?? '—') ?></strong></div>
  </div>
</div>
<?php if ($appView['state'] === 'clarification_requested'): ?>
<div class="card"><h3>Action needed</h3><p class="muted" style="margin-top:0">The employer asked a question about your evidence.</p>
<form method="post" action="/candidate/applications/<?= esc($appId) ?>/respond"><?= csrf_field() ?>
<button class="cta" type="submit">Respond to clarification</button></form></div>
<?php endif; ?>
<div class="card"><h3>History</h3>
<ul class="timeline">
<?php foreach ($appView['timeline'] as $e): ?>
  <li><strong><?= esc(ucwords(str_replace('_',' ',$e['type']))) ?></strong>
      <div class="small muted"><span class="mono"><?= esc($e['occurred_at']) ?></span> — <?= esc($e['note']) ?></div></li>
<?php endforeach; ?>
</ul></div>
<?= $this->endSection() ?>
