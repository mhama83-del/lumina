<?= $this->extend('continuum/_layout') ?>
<?= $this->section('content') ?>
<div class="pagehead"><div class="eyebrow">Candidate · quick reflection</div><h1>Quick reflection</h1>
  <p class="sub"><?= esc($cfg->intro) ?></p></div>

<?php if (! empty($demoMode)): ?>
<div class="card card-quiet" style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
  <div class="small muted">Demo shortcut — fill every answer instantly instead of tapping 15 times.</div>
  <button type="button" class="cta secondary" onclick="continuumAutofill()">⚡ Auto-answer (demo)</button>
</div>
<?php endif; ?>

<form method="post" action="/candidate/survey" id="surveyForm">
  <?= csrf_field() ?>
  <?php foreach ($cfg->questions as $n => $q):
    $preIdx = null;
    if (isset($existing[$q['key']]['reflection_choice'])) {
        $preIdx = array_search($existing[$q['key']]['reflection_choice'], $q['options'], true);
    } ?>
    <div class="card" style="margin:12px 0">
      <div style="font-weight:600;color:var(--ink);margin-bottom:10px">
        <span class="mono faint" style="margin-right:8px"><?= $n+1 ?>.</span><?= esc($q['prompt']) ?></div>
      <div style="display:flex;flex-direction:column;gap:6px">
        <?php foreach ($q['options'] as $oi => $opt): ?>
          <label class="opt">
            <input type="radio" name="q_<?= esc($q['key']) ?>" value="<?= $oi ?>" <?= ($preIdx === $oi)?'checked':'' ?>>
            <span><?= esc($opt) ?></span>
          </label>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endforeach; ?>
  <button class="cta" type="submit">Save &amp; see my map →</button>
  <a class="cta secondary" href="/candidate/evidence">Cancel</a>
</form>

<style>
.opt{display:flex;align-items:center;gap:10px;padding:9px 12px;border:1px solid var(--border);border-radius:8px;cursor:pointer;font-size:.9rem;color:var(--body);transition:border-color .1s,background .1s}
.opt:hover{border-color:var(--brand-line);background:var(--surface-2)}
.opt input{width:auto;margin:0;accent-color:var(--brand)}
.opt:has(input:checked){border-color:var(--brand);background:var(--brand-wash);color:var(--ink);font-weight:500}
</style>
<script>
function continuumAutofill(){
  var form=document.getElementById('surveyForm');
  var groups={};
  form.querySelectorAll('input[type=radio]').forEach(function(r){ (groups[r.name]=groups[r.name]||[]).push(r); });
  Object.values(groups).forEach(function(opts){ opts[Math.floor(Math.random()*opts.length)].checked=true; });
  form.submit();
}
</script>
<?= $this->endSection() ?>
