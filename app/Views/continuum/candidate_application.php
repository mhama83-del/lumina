<?= $this->extend('continuum/_layout') ?>
<?= $this->section('content') ?>
<div class="decision"><h2>Application timeline</h2>
<p><strong>Status:</strong> <?= esc($appView['state_label']) ?> ·
   <strong>Last verified:</strong> <?= esc($appView['last_verified_action']) ?> ·
   <strong>Next owner:</strong> <?= esc($appView['next_owner'] ?? '—') ?> ·
   <strong>Expected update:</strong> <?= esc($appView['expected_update_at'] ?? '—') ?></p></div>
<?php if ($appView['state'] === 'clarification_requested'): ?>
<div class="card"><h3>Action needed</h3><p>The employer asked a question.</p>
<form method="post" action="/candidate/applications/<?= esc($appId) ?>/respond"><?= csrf_field() ?>
<button class="cta" type="submit">Respond to clarification</button></form></div>
<?php endif; ?>
<div class="card"><h3>History</h3>
<ul class="timeline">
<?php foreach ($appView['timeline'] as $e): ?>
  <li><strong><?= esc(ucwords(str_replace('_',' ',$e['type']))) ?></strong>
      <div class="small muted"><?= esc($e['occurred_at']) ?> — <?= esc($e['note']) ?></div></li>
<?php endforeach; ?>
</ul></div>
<?= $this->endSection() ?>
