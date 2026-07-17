<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php helper('ui'); ?>
<section class="hero">
  <div class="section-label">Building your Living Portfolio</div>
  <h1>Reading your evidence…</h1>
  <p class="purpose">Lumina is turning what you shared into your Living Portfolio.</p>
</section>
<section class="section">
  <div class="card" style="text-align:center;padding:32px 20px;animation:revealFadeIn .6s ease">
    <?= lumina_note("We read your evidence and found {$skillCount} skill" . ($skillCount === 1 ? '' : 's') . ($animalLabel ? " · Work style: {$animalLabel}" : '') . '.') ?>
    <div class="row" style="justify-content:center;margin-top:16px">
      <a class="btn btn-primary btn-lg" href="<?= base_url('passport') ?>">View My EDGE Profile →</a>
    </div>
  </div>
</section>
<style>
@keyframes revealFadeIn { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:translateY(0); } }
</style>
<script>
setTimeout(function(){ window.location.href = "<?= base_url('passport') ?>"; }, 1800);
</script>
<?= $this->endSection() ?>