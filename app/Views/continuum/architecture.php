<?= $this->extend('continuum/_layout') ?>
<?= $this->section('content') ?>
<div class="pagehead"><div class="eyebrow">Architecture</div><h1>How it works</h1>
  <p class="sub">A layer on top of the Career Passport — not a replacement for it.</p></div>
<div class="card">
  <div style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;font-size:.85rem" class="mono">
    <span class="pill">Passport / profile reference</span> →
    <span class="pill">Evidence profile</span> →
    <span class="pill">Role Context version</span> →
    <span class="pill">Consent snapshot</span> →
    <span class="pill">Application event stream</span> →
    <span class="pill">Review · outcome · intervention</span>
  </div>
  <p class="small muted" style="margin-top:16px">The Talentbank Passport integration is a mock adapter in this build.
     A production API is <strong>subject to Talentbank validation</strong>.</p>
</div>
<?= $this->endSection() ?>
