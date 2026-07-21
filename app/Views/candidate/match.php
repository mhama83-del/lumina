<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
helper('ui');
$fitClass = ['Best fit' => 'ok', 'Growth fit' => 'nudge', 'Stretch fit' => 'risk'];
?>
<section class="hero">
  <div class="section-label">Apply · Match with intention</div>
  <h1>Opportunities that fit where you're going.</h1>
  <p class="purpose">Matched by readiness and trajectory — not just keywords. <em>Example roles inspired by market patterns — synthetic demo data, not live postings.</em></p>
</section>
<section class="section" style="padding-top:6px">
  <?= lumina_career_journey('apply', 1) ?>
  <?= lumina_note("Your target is Data Analyst. Roles are ordered by your current fit — by readiness and trajectory, not keywords.") ?>
</section>
<section class="section">
  <div class="grid grid-3" id="smartMatchResults">
    <?php foreach ($opps as $i => $o):
      $body = '<p class="muted">' . esc($o['reason']) . '</p>'
            . '<div class="section-label" style="margin-top:12px">Matched skills</div><p>' . esc(implode(', ', $o['matched']) ?: '—') . '</p>'
            . ($o['gap'] ? '<div class="section-label" style="margin-top:10px">To close</div><p>' . esc(implode(', ', $o['gap'])) . '</p>' : '')
            . '<p class="purpose" style="margin-top:12px">Decision support only. Improve your match in Career Compass.</p>';
      $prepBody = '<p class="muted" style="margin-bottom:8px">Practice answering these before you apply or interview:</p><ol style="padding-left:18px;font-size:14px">'
                . implode('', array_map(fn ($q) => '<li style="margin-bottom:8px">' . esc($q) . '</li>', $o['interview_prep'] ?? []))
                . '</ol><p class="purpose" style="margin-top:12px">Use the STAR method: Situation, Task, Action, Result.</p>';
    ?>
      <div class="card" style="--pc:<?= $o['color'] ?>">
        <div class="row" style="justify-content:space-between">
          <span class="pill <?= $fitClass[$o['fit']] ?? 'ok' ?>"><?= esc($o['fit']) ?></span>
          <span style="font-family:var(--font-head);font-weight:800;font-size:22px;color:var(--text)"><?= (int)$o['match'] ?>%</span>
        </div>
        <h3 style="margin:10px 0 2px"><?= esc($o['title']) ?></h3>
        <p class="muted" style="margin:0"><?= esc($o['company']) ?> · <?= esc($o['location']) ?> · <?= esc($o['salary']) ?></p>
        <p class="muted" style="font-size:10px;margin:3px 0 0;opacity:.75">Example role · synthetic demo data</p>
        <p class="muted" style="margin:10px 0 0">
          <?php if ($o['gap']): ?>Gap: <?= esc(implode(', ', $o['gap'])) ?><?php else: ?>Strong match — no gaps.<?php endif; ?>
        </p>
        <div style="margin-top:12px;display:flex;gap:8px;flex-wrap:wrap">
          <button class="btn btn-ghost" data-drawer="1" data-title="<?= esc($o['title'] . ' — why this match', 'attr') ?>" data-body="<?= esc($body, 'attr') ?>">Why this match?</button>
          <button class="btn btn-primary" type="button" data-apply="1" data-role="<?= esc($o['title'], 'attr') ?>" data-company="<?= esc($o['company'], 'attr') ?>">Apply</button>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Perform View (Fasa 6.2 L3) -->
  <section class="section" id="interviewPreparation" style="display:none;padding-top:8px">
    <div class="section-label">Perform · Prepare to show your evidence</div>
    <h2 style="margin:4px 0 4px">If you apply — what to expect.</h2>
    <p class="purpose" style="margin:0 0 14px">Review the fit and the likely questions, so you can decide whether to apply.</p>
    <div class="row" id="perfTabs" style="gap:8px;flex-wrap:wrap;margin-bottom:14px">
      <?php foreach ($opps as $i => $o): ?>
        <button class="btn <?= $i === 0 ? 'btn-primary' : 'btn-ghost' ?>" type="button" data-perf-tab="<?= $i ?>"><?= esc($o['title']) ?></button>
      <?php endforeach; ?>
    </div>
    <?php foreach ($opps as $i => $o): ?>
      <div class="card perf-panel" data-perf-panel="<?= $i ?>" style="--pc:<?= $o['color'] ?>;<?= $i > 0 ? 'display:none' : '' ?>">
        <div class="row" style="justify-content:space-between;flex-wrap:wrap;gap:8px">
          <div>
            <h3 style="margin:0"><?= esc($o['title']) ?></h3>
            <p class="muted" style="margin:2px 0 0"><?= esc($o['company']) ?> · <?= esc($o['location']) ?> · <?= esc($o['salary']) ?></p>
          </div>
          <span class="pill <?= $fitClass[$o['fit']] ?? 'ok' ?>"><?= esc($o['fit']) ?> · <?= (int)$o['match'] ?>%</span>
        </div>
        <p class="muted" style="font-size:10px;margin:6px 0 0;opacity:.75">Example role · synthetic demo data</p>
        <div class="section-label" style="margin-top:14px">Why this match</div>
        <p class="muted" style="margin:2px 0 0"><?= esc($o['reason']) ?></p>
        <div class="section-label" style="margin-top:12px">Matched skills</div>
        <p style="margin:2px 0 0"><?= esc(implode(', ', $o['matched']) ?: '—') ?></p>
        <?php if ($o['gap']): ?>
          <div class="section-label" style="margin-top:12px">To close</div>
          <p style="margin:2px 0 0"><?= esc(implode(', ', $o['gap'])) ?></p>
        <?php endif; ?>
        <?php if (! empty($o['full'])): $f = $o['full'];
              $resp = json_decode($f['responsibilities_json'] ?? '[]', true) ?: [];
              $req  = array_filter($f['skills'] ?? [], fn($sk) => ($sk['importance'] ?? '') === 'required');
        ?>
          <div style="border-top:1px solid var(--line);margin-top:16px;padding-top:14px">
            <div class="section-label">Full job description</div>
            <?php if (! empty($f['role_summary'])): ?><p class="muted" style="margin:4px 0 0"><?= esc($f['role_summary']) ?></p><?php endif; ?>
            <?php if ($resp): ?>
              <div class="section-label" style="margin-top:12px">The role</div>
              <ul style="padding-left:18px;font-size:14px;margin:4px 0 0">
                <?php foreach ($resp as $rItem): ?><li style="margin-bottom:4px"><?= esc(is_array($rItem) ? ($rItem['text'] ?? implode(' ', $rItem)) : $rItem) ?></li><?php endforeach; ?>
              </ul>
            <?php endif; ?>
            <?php if ($req): ?>
              <div class="section-label" style="margin-top:12px">Required skills</div>
              <div class="row" style="flex-wrap:wrap;gap:6px;margin-top:4px">
                <?php foreach ($req as $sk): ?><span class="skill"><?= esc($sk['skill_name'] ?? $sk['skill'] ?? '') ?></span><?php endforeach; ?>
              </div>
            <?php endif; ?>
            <?php if (! empty($f['synthetic_jd_text'])): ?>
              <div class="section-label" style="margin-top:12px">Synthetic JD</div>
              <p class="muted" style="font-size:13px;margin:4px 0 0"><?= esc($f['synthetic_jd_text']) ?></p>
              <p class="purpose" style="font-size:12px;margin-top:6px">Synthetic listing generated for Lumina matching · not a real advertisement.</p>
            <?php endif; ?>
          </div>
        <?php endif; ?>
        <div class="section-label" style="margin-top:14px">If you apply — what to expect</div>
        <p class="muted" style="margin:2px 0 6px;font-size:13px">Practise answering these before you apply or interview:</p>
        <ol style="padding-left:18px;font-size:14px;margin:0">
          <?php foreach (($o['interview_prep'] ?? []) as $q): ?><li style="margin-bottom:8px"><?= esc($q) ?></li><?php endforeach; ?>
        </ol>
        <p class="purpose" style="margin-top:10px">Use the STAR method: Situation, Task, Action, Result.</p>
      </div>
    <?php endforeach; ?>
  </section>

  <div class="row" style="margin-top:18px">
    <a id="mNextBtn" class="btn btn-primary btn-lg" href="<?= base_url('match') ?>#interviewPreparation">Next: if you apply →</a>
    <a class="btn btn-ghost" href="<?= base_url('passport') ?>">← Back to portfolio</a>
  </div>
</section>
<script>
(function(){
  var order = {prepare:0, apply:1, perform:2, progress:3};
  function setStage(stage, done){
    document.querySelectorAll('.career-journey .jstep').forEach(function(a){
      var s = a.getAttribute('data-journey-stage'), i = order[s], dot = a.querySelector('.jdot');
      a.classList.remove('active','done'); a.removeAttribute('aria-current');
      if (i < done){ a.classList.add('done'); if(dot) dot.innerHTML='&#10003;'; }
      else { if(dot) dot.textContent=(i+1); }
      if (s === stage){ a.classList.add('active'); a.setAttribute('aria-current','step'); }
    });
  }
  function applyPerform(){
    var on = (location.hash === '#interviewPreparation');
    var d = document.getElementById('gDrawer');
    var nb = document.getElementById('mNextBtn');
    var sec = document.getElementById('interviewPreparation');
    if (on){
      setStage('perform', 2);
      if (nb){ nb.textContent = 'Build your next proof \u2192'; nb.setAttribute('href', "<?= base_url('compass') ?>#growthPathway"); }
      if (sec){ sec.style.display = ''; sec.scrollIntoView({behavior:'smooth', block:'start'}); }
    } else {
      setStage('apply', 1);
      if (nb){ nb.textContent = 'Next: if you apply \u2192'; nb.setAttribute('href', "<?= base_url('match') ?>#interviewPreparation"); }
      if (sec){ sec.style.display = 'none'; }
    }
  }
  document.querySelectorAll('[data-perf-tab]').forEach(function(t){
    t.onclick = function(){
      var idx = t.getAttribute('data-perf-tab');
      document.querySelectorAll('[data-perf-tab]').forEach(function(x){
        x.classList.toggle('btn-primary', x === t); x.classList.toggle('btn-ghost', x !== t);
      });
      document.querySelectorAll('[data-perf-panel]').forEach(function(p){
        p.style.display = (p.getAttribute('data-perf-panel') === idx) ? '' : 'none';
      });
    };
  });
  window.addEventListener('hashchange', applyPerform);
  applyPerform();
})();
</script>
<!-- Apply consent modal -->
<div id="applyBackdrop" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:200"></div>
<div id="applyModal" role="dialog" aria-modal="true" style="display:none;position:fixed;left:50%;top:50%;transform:translate(-50%,-50%);z-index:201;width:min(440px,92vw);background:var(--bg-card,#12161f);border:1px solid var(--line);border-radius:14px;padding:22px">
  <h3 id="applyTitle" style="margin:0 0 8px">Apply to this role?</h3>
  <p class="muted" style="font-size:14px;margin:0 0 6px" id="applyDesc">You are about to apply.</p>
  <div style="background:rgba(108,92,231,.06);border-radius:10px;padding:12px 14px;margin:12px 0">
    <div style="font-size:11px;font-weight:700;color:var(--indigo);letter-spacing:.04em;margin-bottom:6px">WHAT YOU'LL SHARE</div>
    <p class="muted" style="font-size:13px;margin:0">Your profile summary, skills, readiness, and EDGE evidence summary — so the company can review your fit. They review; they don't auto-decide, sort, or filter by EDGE.</p>
  </div>
  <label style="display:flex;align-items:flex-start;gap:9px;font-size:13px;cursor:pointer;margin-bottom:14px">
    <input type="checkbox" id="applyConsent" style="width:17px;height:17px;margin-top:1px;accent-color:var(--indigo)">
    <span>I agree to apply and share my Lumina profile with this company for review.</span>
  </label>
  <div style="display:flex;gap:8px;justify-content:flex-end">
    <button type="button" class="btn btn-ghost" id="applyCancel">Cancel</button>
    <button type="button" class="btn btn-primary" id="applyConfirm" disabled>Confirm application</button>
  </div>
  <p id="applyDone" style="display:none;color:var(--teal);font-size:13px;margin:12px 0 0;text-align:center">✓ Application sent. The company can now review your profile. (Demo)</p>
</div>
<script>
(function(){
  var bd=document.getElementById('applyBackdrop'), md=document.getElementById('applyModal');
  var ttl=document.getElementById('applyTitle'), dsc=document.getElementById('applyDesc');
  var chk=document.getElementById('applyConsent'), cfm=document.getElementById('applyConfirm');
  var cxl=document.getElementById('applyCancel'), done=document.getElementById('applyDone');
  function open(role,co){ ttl.textContent='Apply to '+role+'?'; dsc.textContent=role+' · '+co; chk.checked=false; cfm.disabled=true; done.style.display='none'; bd.style.display='block'; md.style.display='block'; }
  function close(){ bd.style.display='none'; md.style.display='none'; }
  document.querySelectorAll('[data-apply]').forEach(function(b){ b.addEventListener('click',function(){ open(b.getAttribute('data-role'), b.getAttribute('data-company')); }); });
  chk.addEventListener('change',function(){ cfm.disabled=!chk.checked; });
  cxl.addEventListener('click',close);
  bd.addEventListener('click',close);
  cfm.addEventListener('click',function(){ done.style.display='block'; cfm.disabled=true; chk.disabled=true; setTimeout(close,1800); });
})();
</script>
<?= $this->endSection() ?>
