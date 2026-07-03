<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php helper('ui'); ?>
<section class="hero">
  <div class="section-label">For Universities</div>
  <h1>Which talent is almost ready — and what unlocks them.</h1>
  <p class="lead">Graduate outcomes, faculty benchmarks and at-risk students — full dashboard in Fasa 6.</p>
</section>
<section class="section"><div class="grid grid-4">
  <?= lumina_kpi('64%','Career-ready','outcome') ?>
  <?= lumina_kpi('1,240','Students active') ?>
  <?= lumina_kpi('114','At risk') ?>
  <?= lumina_kpi('88.6%','Employability 6mo','outcome') ?>
</div></section>
<?= $this->endSection() ?>
