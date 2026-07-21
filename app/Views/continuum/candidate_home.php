<?= $this->extend('continuum/_layout') ?>
<?= $this->section('content') ?>
<div class="pagehead"><div class="eyebrow">Candidate</div>
  <h1>Hi <?= esc($candidate['display_name'] ?? 'there') ?></h1>
  <p class="sub">Here's what to do next.</p></div>

<?php if ($appView): ?>
<div class="card">
  <h3>Your active application</h3>
  <div class="req-row"><div class="lead">
    <strong><?= esc($appView['state_label']) ?></strong>
    <div class="small muted">Next owner: <?= esc($appView['next_owner'] ?? '—') ?> · expected update by <?= esc($appView['expected_update_at'] ?? '—') ?></div>
  </div><a class="cta secondary" href="/candidate/applications/<?= esc($appId) ?>">View timeline →</a></div>
</div>
<?php else: ?>
<div class="card card-quiet"><p style="margin:0">Start by reviewing your evidence, then open a role to see how it maps.</p></div>
<?php endif; ?>

<div class="card">
  <div class="req-row"><div class="lead"><strong>Availability</strong>
    <div class="small muted">Employers only see this as an active/stale gate — never a score.</div></div>
    <span class="chip lbl-supported"><span class="dot"></span><?= esc(ucfirst(str_replace('_',' ',$candidate['availability_state'] ?? 'unknown'))) ?></span></div>
</div>
<a class="cta" href="/candidate/evidence">Review my evidence →</a>
<?= $this->endSection() ?>
