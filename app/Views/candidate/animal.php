<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<section class="hero">
  <div class="section-label">Self-discovery · not a test</div>
  <h1>Discover your work style.</h1>
  <p class="lead">A few quick taps. This is a discovery tool to suggest a direction — not a hiring filter.</p>
</section>

<section class="section">
  <form method="post" action="<?= base_url('onboard/animal') ?>">
    <?php foreach ($questions as $qi => $q): ?>
      <div class="card q" style="margin-bottom:14px">
        <div class="q-title"><?= esc($q['q']) ?></div>
        <div class="opts">
          <?php foreach ($q['opts'] as $oi => $opt): ?>
            <label class="opt">
              <input type="radio" name="a[<?= $qi ?>]" value="<?= $qi ?>:<?= $oi ?>" <?= $oi === 0 ? 'checked' : '' ?>>
              <span class="opt-box"><?= esc($opt['t']) ?></span>
            </label>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
    <div class="row">
      <button class="btn btn-gold btn-lg" type="submit">See my work style →</button>
      <a class="btn btn-ghost" href="<?= base_url('onboard/input') ?>">Skip</a>
    </div>
  </form>
</section>
<?= $this->endSection() ?>
