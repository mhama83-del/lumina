<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php helper('ui'); $shortlist = $shortlist ?? []; ?>
<section class="hero">
  <div class="section-label">For Employers</div>
  <h1>Hire on fit, not on guesswork.</h1>
  <p class="purpose">Pick a role — Lumina ranks candidates with the reasons why. Select 2–4 to compare side by side. <em>Decision support only — the recruiter decides.</em></p>
</section>

<!-- KPI row -->
<section class="section">
  <div class="grid grid-4">
    <?= lumina_kpi(count($ranked), 'Candidates in pipeline') ?>
    <?= lumina_kpi((string) count($shortlist), 'Shortlisted') ?>
    <?= lumina_kpi('21d', 'Avg time-to-hire') ?>
    <?= lumina_kpi('86%', 'Offer acceptance') ?>
  </div>
</section>

<!-- Role selector + ranked list -->
<section class="section">
  <div class="card">
    <div class="row" style="justify-content:space-between;flex-wrap:wrap;gap:10px">
      <div>
        <div class="section-label">Ranked candidates</div>
        <h3 style="margin:0"><?= esc($selected['title']) ?> · <?= esc($selected['company']) ?></h3>
      </div>
      <form method="get" action="<?= base_url('employer') ?>">
        <select class="stage-select" name="role" onchange="this.form.submit()">
          <?php foreach ($roles as $r): ?>
            <option value="<?= esc($r['key']) ?>" <?= $r['key'] === $selected['key'] ? 'selected' : '' ?>>
              <?= esc($r['title']) ?> — <?= esc($r['company']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </form>
    </div>

    <!-- Compare form wraps the list -->
    <form method="get" action="<?= base_url('employer/compare') ?>" id="cmpForm">
      <input type="hidden" name="role" value="<?= esc($selected['key']) ?>">
      <div class="row" style="justify-content:space-between;align-items:center;margin-top:12px;flex-wrap:wrap;gap:8px">
        <span class="muted" style="font-size:13px">Tick 2–4 candidates, then compare.</span>
        <button class="btn btn-gold" type="submit" id="cmpBtn" disabled>Compare selected (<span id="cmpN">0</span>) →</button>
      </div>

      <div class="stack" style="margin-top:12px">
        <?php foreach ($ranked as $i => $c):
          $qs = '<ol style="margin:6px 0 0 18px;padding:0">';
          foreach ($c['questions'] as $q) { $qs .= '<li style="margin:4px 0">' . esc($q) . '</li>'; }
          $qs .= '</ol>';
          $body = '<p class="muted">' . esc($c['reason']) . '</p>'
                . '<div class="section-label" style="margin-top:12px">Evidence</div><p>' . esc($c['evidence']) . '</p>'
                . '<div class="section-label" style="margin-top:10px">Suggested interview questions</div>' . $qs
                . '<p class="purpose" style="margin-top:12px">Decision support only — the recruiter decides.</p>';
          $isShort = in_array($c['id'], $shortlist, true);
        ?>
          <div class="card card-tight" style="display:flex;align-items:center;gap:12px">
            <input type="checkbox" class="cmpChk" name="ids[]" value="<?= (int)$c['id'] ?>" title="Select to compare" style="width:18px;height:18px;flex:0 0 auto">
            <div class="ring <?= $i === 0 ? 'gold' : '' ?>"><?= (int)$c['match'] ?></div>
            <div style="flex:1;min-width:0">
              <strong><?= esc($c['name']) ?></strong>
              <span class="pill <?= $c['label']==='best'?'ok':($c['label']==='growth'?'nudge':'risk') ?>" style="margin-left:6px"><?= esc(ucfirst($c['label'])) ?></span>
              <div class="muted" style="font-size:13px"><?= esc($c['university']) ?> · <?= esc($c['programme']) ?>
                <?php if ($c['gap']): ?> · gap: <?= esc(implode(', ', $c['gap'])) ?><?php endif; ?>
              </div>
            </div>
            <button class="btn btn-ghost" type="button" data-drawer="1"
              data-title="<?= esc($c['name'] . ' — why this candidate', 'attr') ?>"
              data-body="<?= esc($body, 'attr') ?>">Why?</button>
            <a class="btn <?= $isShort ? 'btn-gold' : 'btn-ghost' ?>" href="<?= base_url('employer/shortlist?id=' . (int)$c['id'] . '&role=' . esc($selected['key'], 'url')) ?>"><?= $isShort ? '★ Shortlisted' : '☆ Shortlist' ?></a>
          </div>
        <?php endforeach; ?>
        <?php if (empty($ranked)): ?>
          <p class="muted">No candidates found. Import seed data (database/seed_sample.sql).</p>
        <?php endif; ?>
      </div>
    </form>
  </div>
</section>

<script>
(function(){
  var chks = document.querySelectorAll('.cmpChk');
  var btn = document.getElementById('cmpBtn'), n = document.getElementById('cmpN');
  function upd(){
    var sel = [].filter.call(chks, function(c){ return c.checked; });
    n.textContent = sel.length;
    btn.disabled = (sel.length < 2 || sel.length > 4);
    // cap at 4
    if (sel.length >= 4) { [].forEach.call(chks, function(c){ if(!c.checked) c.disabled = true; }); }
    else { [].forEach.call(chks, function(c){ c.disabled = false; }); }
  }
  [].forEach.call(chks, function(c){ c.addEventListener('change', upd); });
})();
</script>
<?= $this->endSection() ?>
