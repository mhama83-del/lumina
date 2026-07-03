<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php helper('ui'); ?>
<section class="hero">
  <div class="section-label">For Employers</div>
  <h1>Hire on fit, not on guesswork.</h1>
  <p class="lead">Pick a role and see ranked candidates with the reasons why — coming in Fasa 5.</p>
</section>
<section class="section"><div class="grid grid-4">
  <?= lumina_kpi('126','Candidates in pipeline') ?>
  <?= lumina_kpi('9','Interviews this week') ?>
  <?= lumina_kpi('21d','Avg time-to-hire') ?>
  <?= lumina_kpi('86%','Offer acceptance') ?>
</div></section>
<?= $this->endSection() ?>
