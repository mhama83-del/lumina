<?= $this->extend('continuum/_layout') ?>
<?= $this->section('content') ?>
<div class="pagehead"><div class="eyebrow">Candidate · guided survey</div><h1>Guided Evidence Survey</h1>
  <p class="sub"><?= esc($cfg->intro) ?></p></div>
<form method="post" action="/candidate/survey">
  <?= csrf_field() ?>
  <?php $lastSig=null; foreach ($cfg->questions as $q):
    $pre = $existing[$q['key']] ?? null;
    if ($q['signal'] !== $lastSig): $lastSig=$q['signal']; ?>
      <h3 style="margin:22px 0 4px"><?= esc(ucwords(str_replace('_',' ',$q['signal']))) ?></h3>
    <?php endif; ?>
    <div class="card" style="margin:10px 0">
      <label for="ex_<?= esc($q['key']) ?>" style="display:block;font-weight:600;color:var(--ink);margin-bottom:6px"><?= esc($q['prompt']) ?></label>
      <input type="text" id="ex_<?= esc($q['key']) ?>" name="ex_<?= esc($q['key']) ?>"
             value="<?= esc($pre['short_example'] ?? '') ?>" placeholder="A short, real example (optional)">
      <label class="small muted" style="display:flex;align-items:center;gap:7px;margin-top:8px">
        <input type="checkbox" name="none_<?= esc($q['key']) ?>" value="1" style="width:auto;margin:0" <?= (isset($pre) && (int)($pre['has_experience'] ?? 1)===0)?'checked':'' ?>>
        <?= esc($cfg->skipCopy) ?></label>
    </div>
  <?php endforeach; ?>
  <button class="cta" type="submit">Save my reflections</button>
  <a class="cta secondary" href="/candidate/evidence">Back to evidence</a>
</form>
<?= $this->endSection() ?>
