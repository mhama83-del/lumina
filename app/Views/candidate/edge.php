<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<section class="hero">
  <div class="section-label">Self-discovery · not a test</div>
  <h1>Discover your work approach.</h1>
  <p class="lead">13 short questions to help Lumina organise the evidence you share. This is not a personality test or a hiring decision.</p>
  <div class="row" style="margin-top:14px">
    <span class="muted" style="font-size:13px;align-self:center">Load a demo:</span>
    <button class="btn btn-ghost" type="button" data-edge-persona="aiman">Aiman · IT</button>
    <button class="btn btn-ghost" type="button" data-edge-persona="nurul">Nurul · Business</button>
    <button class="btn btn-ghost" type="button" data-edge-persona="weijie">Wei Jie · Engineering</button>
    <button class="btn btn-ghost" type="button" id="answerSelfBtn">Answer myself</button>
  </div>
  <p class="muted" style="font-size:12px;margin-top:8px"><?= count($items) ?> questions · about 6–8 minutes · not a diagnosis or fixed label</p>
</section>

<section class="section">
  <div id="edgeNote"></div>
  <div class="section-label" id="edgeCounter">0 of <?= count($items) ?> answered</div>
  <form method="post" action="<?= base_url('onboard/edge') ?>" id="edgeForm">
    <?php foreach ($items as $qi => $q): ?>
      <div class="card q" style="margin-bottom:14px">
        <div class="q-title"><?= esc($q['q']) ?></div>
        <div class="opts">
          <?php foreach ($q['opts'] as $oi => $opt): ?>
            <label class="opt">
              <input type="radio" name="a[<?= $qi ?>]" value="<?= $qi ?>:<?= $oi ?>" required>
              <span class="opt-box"><?= esc($opt['t']) ?></span>
            </label>
          <?php endforeach; ?>
          <label class="opt opt-skip">
            <input type="radio" name="a[<?= $qi ?>]" value="<?= $qi ?>:skip">
            <span class="opt-box" style="opacity:.75;font-style:italic">I have not experienced this yet</span>
          </label>
        </div>
      </div>
    <?php endforeach; ?>
    <div class="row">
      <button class="btn btn-primary btn-lg" type="submit">Next: add your evidence</button>
      <a class="btn btn-ghost" href="<?= base_url('onboard/input') ?>">Skip</a>
    </div>
  </form>
</section>
<script>
(function () {
  var PERSONA = <?= json_encode([
      'aiman'  => \App\Libraries\Edge::demoResponses('aiman'),
      'nurul'  => \App\Libraries\Edge::demoResponses('nurul'),
      'weijie' => \App\Libraries\Edge::demoResponses('weijie'),
  ]) ?>;
  var form = document.getElementById('edgeForm');
  var counter = document.getElementById('edgeCounter');
  var note = document.getElementById('edgeNote');
  var total = <?= count($items) ?>;

  function updateCounter() {
    var answered = form.querySelectorAll('input[type=radio]:checked').length;
    counter.textContent = answered + ' of ' + total + ' answered';
  }

  function fillPersona(key) {
    var ans = PERSONA[key] || [];
    ans.forEach(function (val) {
      var el = form.querySelector('input[value="' + val + '"]');
      if (el) el.checked = true;
    });
    note.innerHTML = '<div style="display:flex;gap:8px;align-items:center;background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.25);color:#4ade80;padding:9px 13px;border-radius:10px;font-size:13px;margin-bottom:12px"><span>&#10003;</span><span>Demo responses loaded · You can still change any answer.</span></div>';
    updateCounter();
  }

  document.querySelectorAll('[data-edge-persona]').forEach(function (b) {
    b.onclick = function () { fillPersona(b.getAttribute('data-edge-persona')); };
  });
  var selfBtn = document.getElementById('answerSelfBtn');
  if (selfBtn) selfBtn.onclick = function () {
    form.querySelectorAll('input[type=radio]:checked').forEach(function (r) { r.checked = false; });
    note.innerHTML = '';
    updateCounter();
    form.scrollIntoView({ behavior: 'smooth', block: 'start' });
  };
  form.addEventListener('change', updateCounter);
  updateCounter();
})();
</script>
<?= $this->endSection() ?>
