<?= $this->extend('continuum/_layout') ?>
<?= $this->section('content') ?>
<div class="decision">
  <h2><?= esc($product->productName) ?> — <?= esc($product->productDescriptor) ?></h2>
  <p><?= esc($product->productTagline) ?></p>
</div>
<div class="card">
  <p><strong>Career Passport stores the profile. <?= esc($product->productName) ?> turns approved
  evidence into clearer role actions and accountable outcomes.</strong></p>
  <ol>
    <li>Evidence, not personality or black-box ranking.</li>
    <li>Candidate-controlled sharing.</li>
    <li>Every application has a next owner and expected update.</li>
  </ol>
  <a class="cta" href="/demo/scenarios">Start guided demo</a>
  <a class="cta secondary" href="/how-it-works">How it works</a>
</div>
<?= $this->endSection() ?>
