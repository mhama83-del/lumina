<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php helper('ui'); $p = $persona ?? []; ?>
<section class="hero">
  <div class="section-label">Candidate · Stage <?= esc(session('stage') ?? '19-22') ?></div>
  <h1>Welcome, <?= esc($p['name'] ?? 'there') ?>.</h1>
  <p class="lead">Your sample profile is loaded. In the next phase you'll build your Living Portfolio and see your readiness move.</p>
  <div class="row" style="margin-top:16px">
    <a class="btn btn-gold btn-lg" href="<?= base_url('start') ?>">Build my Living Portfolio →</a>
  </div>
</section>
<section class="section">
  <div class="grid grid-3">
    <div class="card"><div class="donut-wrap"><?= lumina_donut((int)($p['readiness'] ?? 72),'Adaptive Readiness','var(--indigo)') ?></div></div>
    <div class="card">
      <h3>Living Portfolio</h3>
      <p class="muted"><?= esc($p['university'] ?? 'USM') ?> · <?= esc($p['programme'] ?? 'Computer Science') ?></p>
      <?php foreach (($p['skills'] ?? ['Python'=>'stated','Teamwork'=>'stated']) as $s=>$src): ?>
        <?= lumina_skill($s, $src) ?>
      <?php endforeach; ?>
    </div>
    <?= lumina_kpi(($p['workAnimal'] ?? 'The Owl'),'Work Animal','self-discovery') ?>
  </div>
  <p class="purpose" style="margin-top:14px">Fasa 3 akan isi: Work Animal onboarding, evidence input, dan Living Portfolio penuh.</p>
</section>
<?= $this->endSection() ?>
