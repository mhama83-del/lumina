<?= $this->extend('continuum/_layout') ?>
<?= $this->section('content') ?>
<div class="pagehead"><div class="eyebrow">University</div><h1><?= esc($cohort['programme']) ?> <span class="faint mono" style="font-size:1rem">· <?= esc($cohort['intake']) ?></span></h1>
  <p class="sub">Aggregate signal only — no individual student list, no "at risk" ranking.</p></div>
<div class="card"><h3>Cohort signal</h3>
<div class="statwrap">
  <div><div class="stat"><?= esc($agg['cohort_size']) ?></div><div class="small faint" style="text-align:center">students</div></div>
  <div style="margin-left:8px"><div class="stat"><?= esc(round($agg['source_backed_sql_rate']*100)) ?><span class="unit">%</span></div>
    <div class="stat-note" style="padding-bottom:0">have source-backed SQL evidence — the cohort's clearest gap to close.</div></div>
</div>
<p class="small faint" style="margin-top:14px">Source: <?= esc($agg['source']) ?> · refreshed <?= esc($agg['refresh_date']) ?> · confidence <?= esc($agg['confidence']) ?></p></div>
<div class="card"><h3>Interventions</h3>
<?php foreach ($interventions as $i): ?>
  <div class="req-row"><div class="lead"><strong><?= esc($i['signal']) ?></strong>
    <div class="small muted">Owner: <?= esc($i['owner']) ?> · outcome metric: <?= esc($i['outcome_metric']) ?></div></div>
    <span class="pill"><?= esc($i['status']) ?></span></div>
<?php endforeach; ?>
<form method="post" action="/university/cohorts/<?= esc($cohort['id']) ?>/intervene" style="margin-top:14px">
  <?= csrf_field() ?>
  <input name="signal" placeholder="Cohort signal (e.g. low source-backed SQL)">
  <input name="plan" placeholder="Intervention plan">
  <input name="metric" placeholder="Outcome metric">
  <button class="cta" type="submit" style="margin-top:8px">Create intervention</button></form></div>
<?= $this->endSection() ?>
