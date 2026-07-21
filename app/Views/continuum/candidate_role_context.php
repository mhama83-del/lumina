<?= $this->extend('continuum/_layout') ?>
<?= $this->section('content') ?>
<div class="decision"><h2>Role Context — <?= esc($role['title']) ?></h2>
<p class="small"><?= esc($version['summary']) ?></p>
<p><strong>Role Evidence Readiness (RER): <?= esc($readiness['rer']) ?>%</strong>
<span class="muted small">— role-specific evidence coverage, not a hiring score.</span></p></div>

<div class="card"><h3>Requirements &amp; your evidence coverage</h3>
<?php foreach ($readiness['breakdown'] as $b): ?>
  <div class="req-row">
    <div><strong><?= esc($b['requirement']) ?></strong>
      <span class="chip gate-<?= $b['importance']==='critical'?'candidate_action_suggested':'review_with_questions' ?>"><?= esc(ucfirst($b['importance'])) ?></span>
      <div class="small muted"><?= esc($b['explanation']) ?></div></div>
    <div class="suff"><?= esc($b['sufficiency']) ?> / <?= esc($b['max']) ?></div>
  </div>
<?php endforeach; ?></div>

<?php if ($readiness['questions']): ?>
<div class="card"><h3>Questions to Confirm</h3>
<ul><?php foreach ($readiness['questions'] as $q): ?>
  <li><strong><?= esc($q['requirement']) ?>:</strong> <?= esc($q['question']) ?>
      <span class="small muted">(<?= esc($q['reason']) ?>)</span></li>
<?php endforeach; ?></ul></div>
<?php endif; ?>

<div class="card">
  <a class="cta" href="/candidate/roles/<?= esc($role['slug']) ?>/apply">Preview what the employer will see →</a>
</div>
<?= $this->endSection() ?>
