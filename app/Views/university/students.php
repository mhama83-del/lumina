<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php helper('ui');
  $ctx = [];
  if (!empty($f['band'])) $ctx[] = 'band: ' . $f['band'];
  if (!empty($f['gap'])) $ctx[] = 'missing: ' . ucwords(str_replace('_',' ', $f['gap']));
  if (!empty($f['programme'])) $ctx[] = 'programme: ' . $f['programme'];
  if (!empty($f['metric'])) { $ml=['ready'=>'career-ready','transfer'=>'transferable skills','industry'=>'industry exposure','highinc'=>'high-income potential','jobcreator'=>'job-creator potential','matched'=>'opportunity match']; $ctx[] = $ml[$f['metric']] ?? $f['metric']; }
  if (!empty($f['uni'])) $ctx[] = $f['uni'];
  $backUni = !empty($f['uni']) ? '?uni=' . urlencode($f['uni']) : '';
?>
<section class="hero">
  <div class="section-label">University · Students<?= $ctx ? ' · ' . esc(implode(' · ', $ctx)) : '' ?></div>
  <h1><?= count($list) ?> students<?= count($list) >= 300 ? '+' : '' ?></h1>
  <p class="purpose">Click any student to see the evidence and the exact reason behind their readiness. Sorted most-at-risk first.</p>
  <div class="row" style="margin-top:10px"><a class="btn btn-ghost" href="<?= base_url('university') . $backUni ?>">← Back to dashboard</a></div>
</section>
<section class="section">
  <div class="stack">
    <?php foreach ($list as $c):
      $cls = $c['band']==='On track'?'ok':($c['band']==='Needs a nudge'?'nudge':'risk'); ?>
      <a class="card card-tight" href="<?= base_url('university/student/'.(int)$c['id']) ?>" style="display:flex;align-items:center;gap:12px;text-decoration:none">
        <div class="ring <?= $cls ?>"><?= (int)$c['readiness'] ?></div>
        <div style="flex:1;min-width:0">
          <strong><?= esc($c['name']) ?></strong>
          <span class="pill <?= $cls ?>" style="margin-left:6px"><?= esc($c['band']) ?></span>
          <div class="muted" style="font-size:13px"><?= esc($c['university']) ?> · <?= esc($c['programme']) ?> · <?= esc($c['faculty']) ?>
            <?php if ($c['gaps']): ?> · gap: <?= esc(implode(', ', $c['gaps'])) ?><?php endif; ?>
          </div>
        </div>
        <span class="muted">→</span>
      </a>
    <?php endforeach; ?>
    <?php if (empty($list)): ?><p class="muted">No students match this segment.</p><?php endif; ?>
  </div>
</section>
<?= $this->endSection() ?>
