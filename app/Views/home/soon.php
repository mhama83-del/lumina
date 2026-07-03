<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<section class="hero">
  <div class="section-label"><?= esc($phase) ?></div>
  <h1><?= esc($name) ?></h1>
  <p class="lead"><?= esc($desc) ?></p>
  <div class="row" style="margin-top:16px">
    <a class="btn btn-ghost" href="<?= base_url('/') ?>">← Back to home</a>
  </div>
</section>
<?= $this->endSection() ?>
