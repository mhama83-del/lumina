<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php helper('ui'); ?>

<section class="hero">
  <div class="section-label">Fasa 0 · Design System Check</div>
  <h1>Lumina style guide</h1>
  <p class="lead">If this page matches the demo look on desktop and mobile, Fasa 0 is done.</p>
</section>

<section class="section">
  <div class="section-label">Buttons</div>
  <div class="row">
    <a class="btn btn-primary">Primary</a>
    <a class="btn btn-gold">Gold CTA</a>
    <a class="btn btn-ghost">Ghost</a>
  </div>
</section>

<section class="section">
  <div class="section-label">Cards · Donut · KPI</div>
  <div class="grid grid-3">
    <div class="card"><div class="donut-wrap"><?= lumina_donut(72,'Readiness · Data Analyst','var(--indigo)') ?></div></div>
    <?= lumina_kpi('88.6%','Employability within 6 months','Graduate outcome') ?>
    <div class="card">
      <h3>Skills</h3>
      <?= lumina_skill('Python','stated') ?>
      <?= lumina_skill('Stakeholder Mgmt','inferred',0.7) ?>
      <?= lumina_skill('Budgeting','inferred',0.8) ?>
    </div>
  </div>
</section>

<section class="section">
  <div class="section-label">Pillar chips</div>
  <div class="grid" style="grid-template-columns:repeat(5,1fr); gap:12px">
    <?= lumina_chip('Living Portfolio','indigo') ?>
    <?= lumina_chip('Smart Matching','gold') ?>
    <?= lumina_chip('Learning Velocity','green') ?>
    <?= lumina_chip('Adaptive Readiness','teal') ?>
    <?= lumina_chip('University Intelligence','violet') ?>
  </div>
</section>

<section class="section">
  <div class="section-label">Status pills</div>
  <div class="row">
    <span class="pill ok">On track</span>
    <span class="pill nudge">Needs a nudge</span>
    <span class="pill risk">At risk</span>
  </div>
</section>

<?= $this->endSection() ?>
