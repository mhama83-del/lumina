<?= $this->extend('continuum/_layout') ?>
<?= $this->section('content') ?>
<div class="decision">
  <h2>Hi <?= esc($candidate['display_name'] ?? 'there') ?> — here's what to do now</h2>
  <?php if ($appView): ?>
    <p><strong>Current status:</strong> <?= esc($appView['state_label']) ?> ·
       <strong>Next owner:</strong> <?= esc($appView['next_owner'] ?? '—') ?> ·
       <strong>Expected update:</strong> <?= esc($appView['expected_update_at'] ?? '—') ?></p>
    <a class="cta" href="/candidate/applications/<?= esc($appId) ?>">View application timeline</a>
  <?php else: ?>
    <p>Start by reviewing your evidence, then a role.</p>
  <?php endif; ?>
  <a class="cta secondary" href="/candidate/evidence">Review my evidence</a>
</div>
<div class="card"><h3>Availability</h3>
  <span class="chip"><span class="dot" style="background:var(--success)"></span>
  <?= esc(ucfirst(str_replace('_',' ',$candidate['availability_state'] ?? 'unknown'))) ?></span></div>
<?= $this->endSection() ?>
