<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<section class="hero">
  <div class="section-label">Self-discovery · not a test</div>
  <h1>Discover your work style.</h1>
  <p class="lead">A few quick taps. This is a discovery tool to suggest a direction — not a hiring filter.</p>
  <div class="row" style="margin-top:14px">
    <button class="btn btn-ghost" type="button" id="demoQuizBtn">Load demo responses</button>
    <button class="btn btn-ghost" type="button" id="answerSelfBtn">Answer myself</button>
  </div>
  <p class="muted" style="font-size:12px;margin-top:8px"><?= count($questions) ?> questions · Not a diagnosis or fixed label</p>
</section>

<section class="section">
  <div id="quizNote"></div>
  <div class="section-label" id="quizCounter">0 of <?= count($questions) ?> completed</div>
  <form method="post" action="<?= base_url('onboard/animal') ?>" id="animalForm">
    <?php foreach ($questions as $qi => $q): ?>
      <div class="card q" style="margin-bottom:14px">
        <div class="q-title"><?= esc($q['q']) ?></div>
        <div class="opts">
          <?php foreach ($q['opts'] as $oi => $opt): ?>
            <label class="opt">
              <input type="radio" name="a[<?= $qi ?>]" value="<?= $qi ?>:<?= $oi ?>" required>
              <span class="opt-box"><?= esc($opt['t']) ?></span>
            </label>
          <?php endforeach; ?>
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
  var DEMO = ["0:0","1:0","2:4","3:4","4:2","5:0","6:3","7:3","8:0","9:0","10:3","11:0","12:0"];
  var form = document.getElementById('animalForm');
  var counter = document.getElementById('quizCounter');
  var note = document.getElementById('quizNote');
  if (!form) return;
  var total = form.querySelectorAll('.card.q').length;
  function update() {
    var done = 0;
    for (var i = 0; i < total; i++) {
      if (form.querySelector('input[name="a[' + i + ']"]:checked')) done++;
    }
    counter.textContent = done + ' of ' + total + ' completed';
  }
  form.addEventListener('change', update);
  document.getElementById('demoQuizBtn').onclick = function () {
    DEMO.forEach(function (v) {
      var el = form.querySelector('input[type=radio][value="' + v + '"]');
      if (el) el.checked = true;
    });
    update();
    note.innerHTML = '<div style="display:flex;gap:8px;align-items:center;background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.25);color:#4ade80;padding:9px 13px;border-radius:10px;font-size:13px;margin:0 0 16px"><span>&#10003;</span><span>Demo responses loaded. You can still change any answer.</span></div>';
    counter.scrollIntoView({ behavior: 'smooth', block: 'center' });
  };
  document.getElementById('answerSelfBtn').onclick = function () {
    var first = form.querySelector('.card.q');
    if (first) first.scrollIntoView({ behavior: 'smooth', block: 'center' });
  };
  update();
})();
</script>
<?= $this->endSection() ?>
