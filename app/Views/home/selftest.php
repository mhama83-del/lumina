<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<section class="hero">
  <div class="section-label">Fasa 1 · ScoreService self-test</div>
  <h1>Engine check</h1>
  <p class="lead">Verifies inferSkills → readiness → match → what-if produce correct numbers for the Aiman persona.</p>
</section>
<section class="section"><div class="card"><pre style="white-space:pre-wrap;color:#cdd6e6;font-size:13px;margin:0"><?= esc($report) ?></pre></div></section>
<?= $this->endSection() ?>
