<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<section class="hero">
  <div class="section-label">Candidate · Stage <?= esc(session('stage') ?? '19-22') ?></div>
  <h1>Where would you like to start?</h1>
  <p class="lead">Lumina builds your Living Portfolio from whatever you already have.</p>
</section>

<section class="section">
  <div class="grid grid-3">
    <a class="card" href="<?= base_url('resume') ?>" style="text-decoration:none">
      <h3>I have a resume</h3>
      <p class="muted">Paste it and see the evidence Lumina can read.</p>
    </a>
    <a class="card" href="<?= base_url('onboard/animal') ?>" style="text-decoration:none">
      <h3>I don't have a resume yet</h3>
      <p class="muted">Start from your activities, projects and experience.</p>
    </a>
    <a class="card" href="<?= base_url('start/sample') ?>" style="text-decoration:none">
      <h3>Show me a completed example</h3>
      <p class="muted">Explore a preloaded candidate journey.</p>
    </a>
  </div>
  <p class="muted" style="font-size:13px;margin-top:14px">Not sure? Start without a resume—your experience is enough to begin.</p>
</section>
<?= $this->endSection() ?>
