<?= $this->extend('continuum/_layout') ?>
<?= $this->section('content') ?>
<div class="decision"><h2>Cohort — <?= esc($cohort['programme']) ?> (<?= esc($cohort['intake']) ?>)</h2>
<p class="small">Aggregate only. No individual student list, no "at risk" ranking.</p></div>
<div class="card"><h3>Aggregate signal</h3>
<table><tr><th>Metric</th><th>Value</th></tr>
<tr><td>Cohort size</td><td><?= esc($agg['cohort_size']) ?></td></tr>
<tr><td>Source-backed SQL evidence rate</td><td><?= esc(round($agg['source_backed_sql_rate']*100)) ?>%</td></tr></table>
<p class="small muted">Source: <?= esc($agg['source']) ?> · Refreshed: <?= esc($agg['refresh_date']) ?> · Confidence: <?= esc($agg['confidence']) ?></p></div>
<div class="card"><h3>Interventions</h3>
<?php foreach ($interventions as $i): ?>
  <div class="req-row"><div><strong><?= esc($i['signal']) ?></strong>
    <div class="small muted">Owner: <?= esc($i['owner']) ?> · Metric: <?= esc($i['outcome_metric']) ?></div></div>
    <span class="chip"><?= esc($i['status']) ?></span></div>
<?php endforeach; ?>
<form method="post" action="/university/cohorts/<?= esc($cohort['id']) ?>/intervene">
  <?= csrf_field() ?>
  <input name="signal" placeholder="Cohort signal" style="padding:8px;width:100%;margin:4px 0">
  <input name="plan" placeholder="Intervention plan" style="padding:8px;width:100%;margin:4px 0">
  <input name="metric" placeholder="Outcome metric" style="padding:8px;width:100%;margin:4px 0">
  <button class="cta" type="submit">Create intervention</button></form></div>
<?= $this->endSection() ?>
