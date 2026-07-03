<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<section class="hero">
  <div class="section-label">Candidate · Stage <?= esc(session('stage') ?? '19-22') ?></div>
  <h1>No resume? No problem.</h1>
  <p class="lead">Start any way you like — Lumina builds your Living Portfolio from whatever you already have.</p>
</section>

<section class="section">
  <div class="grid grid-3">
    <a class="card" href="<?= base_url('onboard/animal') ?>" style="text-decoration:none">
      <div class="section-label">Recommended</div>
      <h3>Discover your work style</h3>
      <p class="muted">Answer a few taps to find your Work Animal, then build from there.</p>
    </a>
    <a class="card" href="<?= base_url('onboard/input') ?>" style="text-decoration:none">
      <h3>Import / paste evidence</h3>
      <p class="muted">Paste your activities, or import a co-curricular transcript.</p>
    </a>
    <a class="card" href="<?= base_url('start/sample') ?>" style="text-decoration:none">
      <h3>Use a sample profile</h3>
      <p class="muted">Jump straight to a populated Living Portfolio (great for a quick look).</p>
    </a>
  </div>
</section>
<?= $this->endSection() ?>
