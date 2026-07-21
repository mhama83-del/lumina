<?= $this->extend('continuum/_layout') ?>
<?= $this->section('content') ?>
<div class="pagehead"><div class="eyebrow">Employer · candidate review</div><h1>Application <span class="mono">#<?= esc($view['application_id']) ?></span></h1></div>
<div class="card">
  <div style="display:flex;flex-wrap:wrap;gap:14px;align-items:center">
    <span class="chip gate-<?= esc($view['queue_label']) ?>"><span class="dot"></span><?= esc($view['queue_label_text']) ?></span>
    <span class="small muted">Availability: <strong><?= esc($view['availability']) ?></strong></span>
    <span class="small muted">Consent valid until: <strong class="mono"><?= esc($view['consent_valid_until'] ?? '—') ?></strong></span>
  </div>
</div>

<div class="card"><h3>Requirement coverage</h3>
<?php foreach ($view['requirements'] as $b):
  $pct=($b['max']>0)?round($b['sufficiency']/$b['max']*100):0; $tone=$pct>=67?'ok':($pct>=34?'warn':'low'); ?>
  <div class="req-row"><div class="lead">
    <strong><?= esc($b['requirement']) ?> <span class="pill <?= esc($b['importance']) ?>"><?= esc(ucfirst($b['importance'])) ?></span></strong>
    <div class="small muted"><?= esc($b['explanation']) ?></div>
    <div class="bar <?= $tone ?>"><span style="width:<?= $pct ?>%"></span></div></div>
    <div class="suff"><?= esc($b['sufficiency']) ?>/<?= esc($b['max']) ?></div></div>
<?php endforeach; ?></div>

<?php if ($view['questions_to_confirm']): ?>
<div class="card"><h3>Questions to confirm</h3>
<?php foreach ($view['questions_to_confirm'] as $q): ?>
  <div class="req-row"><div class="lead"><span class="muted small"><?= esc($q['question']) ?></span></div></div>
<?php endforeach; ?></div>
<?php endif; ?>

<div class="card"><h3>Consented evidence</h3>
<?php foreach ($view['evidence_summary'] as $e): ?>
  <div class="req-row"><div class="lead"><strong><?= esc($e['claim_text']) ?></strong>
    <?php if ($e['source_excerpt']): ?><div class="small muted">Source: <?= esc($e['source_excerpt']) ?></div><?php endif; ?></div>
    <?= $this->include('continuum/_label_chip', ['label'=>$e['label']]) ?></div>
<?php endforeach; ?>
<p class="small faint" style="margin-top:12px">Raw survey answers are not included and are never shared.</p></div>

<div class="card"><h3>Update this application</h3>
<form method="post" action="/employer/review/<?= esc($view['application_id']) ?>/status" style="display:inline">
  <?= csrf_field() ?><input type="hidden" name="to" value="under_review">
  <button class="cta secondary" type="submit">Start / continue review</button></form>
<form method="post" action="/employer/review/<?= esc($view['application_id']) ?>/clarify" style="display:inline">
  <?= csrf_field() ?><input type="hidden" name="question" value="Please add a source for your SQL example.">
  <button class="cta" type="submit">Request clarification</button></form>
</div>
<?= $this->endSection() ?>
