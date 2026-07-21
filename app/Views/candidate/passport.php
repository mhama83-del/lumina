<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
helper('ui');
$p = $profile;
$riskClass = $risk === 'On track' ? 'ok' : ($risk === 'Needs a nudge' ? 'nudge' : 'risk');
$evidenceLines = array_filter(array_map('trim', preg_split('/[;\n]+|\.(?=\s|$)/', $p['evidence_text'] ?? '')), fn($l) => $l !== '' && !preg_match('/^\d+$/', $l));
$skillTotal = count($p['skills'] ?? []);
$skillInferred = 0; foreach (($p['skills'] ?? []) as $s) { if (($s['source'] ?? '') === 'inferred') $skillInferred++; }
$rc = [
  ['Skill coverage', (int)$readiness['coverage'], 40],
  ['Evidence strength', (int)$readiness['evidence'], 25],
  ['Activity signals', (int)$readiness['activity'], 20],
  ['Learning pace', (int)$readiness['pace'], 15],
];
$pName = $p['name'] ?? null;
$parser = new \App\Services\ResumeParserService();
$txt = $p['evidence_text'] ?? '';
$cvProjects   = $txt ? $parser->detectProjects($txt) : [];
$cvLeadership = $txt ? $parser->detectLeadership($txt) : [];
$topSignals = [];
if (!empty($potentialProfile['top_strengths'])) $topSignals = array_slice($potentialProfile['top_strengths'], 0, 2);
?>
<section class="hero" id="passportHeader">
  <div class="section-label">Prepare · Your career evidence</div>
  <h1><?= esc($pName ?: 'Your profile') ?></h1>
  <p class="muted" style="margin:2px 0 0">
    <?php
      $bits = array_filter([
        $p['programme']  ?? null,
        $p['university'] ?? null,
        $p['study_year'] ?? 'Study year not stated',
      ]);
      echo esc(implode(' · ', $bits));
    ?>
  </p>
  <p class="muted" style="font-size:13px;margin:4px 0 0">
    <?php if (!empty($p['cgpa'])): ?>CGPA <?= esc($p['cgpa']) ?> · <?php endif; ?>
    Career target: <strong style="color:var(--text)"><?= esc($role['title']) ?></strong>
    <?php if (!empty($p['last_updated'])): ?> · Last updated <?= esc($p['last_updated']) ?><?php endif; ?>
  </p>
  <?php if (empty($p['programme']) || empty($p['university']) || empty($p['cgpa'])): ?>
    <p class="muted" style="font-size:12px;margin-top:8px">Add profile details — programme, university, study year and CGPA — to complete this header.</p>
  <?php endif; ?>
</section>

<section class="section" style="padding-top:6px">
  <?= lumina_career_journey('prepare') ?>
  <p class="muted" style="font-size:13px;margin:-6px 0 14px">Prepare — make your evidence readable before choosing a direction.</p>

  <div style="display:flex;flex-direction:column;gap:16px">
    <!-- ===== Profil (turun bawah) ===== -->
    <div style="order:2;display:flex;flex-direction:column;gap:16px">

      <div class="card">
        <div class="section-label">Profile summary</div>
        <p style="margin:6px 0 0"><?= esc($pName ?: 'This candidate') ?> is working toward <strong style="color:var(--text)"><?= esc($role['title']) ?></strong>.
        Lumina read <?= (int)$skillTotal ?> skills from the evidence shared<?php if ($skillInferred): ?>, including <?= (int)$skillInferred ?> not listed directly<?php endif; ?>.</p>
        <?php if ($topSignals): ?>
          <p class="muted" style="font-size:13px;margin-top:6px">Strongest work-style signals: <?= esc(implode(' · ', $topSignals)) ?>.</p>
        <?php endif; ?>
      </div>

      <div class="card">
        <div class="section-label">Education</div>
        <?php if (!empty($p['programme']) || !empty($p['university'])): ?>
          <p style="margin:6px 0 0"><strong style="color:var(--text)"><?= esc($p['programme'] ?? 'Programme not stated') ?></strong></p>
          <p class="muted" style="margin:2px 0 0">
            <?= esc($p['university'] ?? 'University not stated') ?>
            <?php if (!empty($p['cgpa'])): ?> · CGPA <?= esc($p['cgpa']) ?><?php endif; ?>
          </p>
        <?php else: ?>
          <p class="muted" style="margin:6px 0 0">No education details detected yet. Add them to strengthen your profile.</p>
        <?php endif; ?>
      </div>

      <div class="card">
        <div class="section-label">Experience, activities &amp; leadership</div>
        <?php if ($cvLeadership): ?>
          <?php foreach ($cvLeadership as $line): ?><div class="ev">• <?= esc($line) ?></div><?php endforeach; ?>
        <?php else: ?>
          <p class="muted" style="margin:6px 0 0">No leadership or activity detected yet — a club role, volunteer work or internship counts.</p>
        <?php endif; ?>
      </div>

      <div class="card">
        <div class="section-label">Projects / portfolio</div>
        <?php if ($cvProjects): ?>
          <?php foreach ($cvProjects as $line): ?><div class="ev">• <?= esc($line) ?></div><?php endforeach; ?>
        <?php else: ?>
          <p class="muted" style="margin:6px 0 0">No projects detected yet — your next proof point could start here.</p>
        <?php endif; ?>
      </div>

      <div class="card" id="cvEvidenceCheck">
        <div class="section-label">Skills &amp; Evidence Check</div>
        <p class="muted" style="font-size:13px;margin:4px 0 10px">See what supports each skill.</p>
        <div style="display:flex;gap:6px;flex-wrap:wrap;align-items:center;font-size:11px;margin-bottom:10px" class="muted">
          <span>Stated</span><span>&rarr;</span><span>Inferred</span><span>&rarr;</span><span>Supported</span>
          <span style="margin-left:8px">·</span><span>Needs Evidence</span>
          <button class="btn btn-ghost" style="padding:4px 9px;font-size:11px" data-drawer="1"
            data-title="How to read this"
            data-body="&lt;p class=&quot;muted&quot;&gt;Stated: you listed it. Inferred: Lumina read it from your evidence. Supported: strongly backed by the evidence you shared. Needs Evidence: not yet proven — this is your next proof point.&lt;/p&gt;&lt;p class=&quot;muted&quot;&gt;Needs Evidence is not a weakness—it is your next proof point.&lt;/p&gt;">How to read this</button>
        </div>
        <div style="display:flex;gap:6px;flex-wrap:wrap;align-items:center;font-size:11px;margin-bottom:12px" class="muted">
          <span style="display:inline-flex;align-items:center;gap:4px"><span style="width:10px;height:10px;border-radius:3px;background:#4ade80;display:inline-block"></span>Supported</span>
          <span style="display:inline-flex;align-items:center;gap:4px"><span style="width:10px;height:10px;border-radius:3px;background:var(--indigo);display:inline-block"></span>Inferred</span>
          <span style="display:inline-flex;align-items:center;gap:4px"><span style="width:10px;height:10px;border-radius:3px;background:#7dd3fc;display:inline-block"></span>Stated</span>
          <span style="display:inline-flex;align-items:center;gap:4px"><span style="width:10px;height:10px;border-radius:3px;background:rgba(255,255,255,.12);display:inline-block"></span>Needs evidence</span>
        </div>
        <div>
          <?php foreach (($p['skills'] ?? []) as $code => $s):
            $src  = $s['source'] ?? 'stated';
            $conf = (float)($s['confidence'] ?? 1);
            if ($src === 'inferred' && $conf >= 0.75) { $stat='Supported'; $col='#4ade80'; $pct=100; }
            elseif ($src === 'inferred') { $stat='Inferred'; $col='var(--indigo)'; $pct=max(40,(int)round($conf*100)); }
            elseif ($src === 'stated' && $conf < 0.4) { $stat='Needs evidence'; $col='rgba(255,255,255,.12)'; $pct=12; }
            else { $stat='Stated'; $col='#7dd3fc'; $pct=60; }
            $lab = ucwords(str_replace('_',' ',$code));
            $from = !empty($s['from']) ? $s['from'] : '';
          ?>
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:5px;font-size:12px" title="<?= esc($from ? "Found in: $from" : $stat, 'attr') ?>">
            <span style="width:120px;flex-shrink:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= esc($lab) ?></span>
            <span style="flex:1;height:9px;background:rgba(255,255,255,.05);border-radius:5px;overflow:hidden"><span style="display:block;height:100%;width:<?= $pct ?>%;background:<?= $col ?>"></span></span>
            <span style="width:98px;flex-shrink:0;text-align:right;color:var(--muted);font-size:11px"><?= $stat ?></span>
          </div>
          <?php endforeach; ?>
        </div>

        <p class="muted" style="font-size:12px;margin-top:10px">Needs Evidence is not a weakness—it is your next proof point.</p>

      </div>

      <?php if ($evidenceLines): ?>
      <div class="card">
        <div class="section-label">Your original evidence</div>
        <?php foreach ($evidenceLines as $line): ?><div class="ev">• <?= esc($line) ?></div><?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <!-- ===== Insights (naik atas) ===== -->
    <div style="order:1;display:flex;flex-direction:column;gap:16px">
      <div class="card">
        <div class="section-label">Lumina insights</div>
        <h3 style="margin:6px 0 2px">Your readiness today</h3>
        <p class="muted" style="font-size:13px;margin:0 0 14px">Based on the skills, evidence and activity you have shared.</p>
        <div class="grid grid-2" style="align-items:start;gap:0">
          <div style="padding-right:20px;border-right:1px solid var(--line)">
            <div class="section-label" style="text-align:center;margin-bottom:6px">Readiness</div>
            <div class="donut-wrap"><?= lumina_donut($readiness['score'], $role['title'], $role['color']) ?></div>
            <div class="row" style="justify-content:center;margin-top:8px">
              <span class="pill <?= $riskClass ?>"><?= esc($risk) ?></span>
            </div>
                    <div style="margin-top:14px">
              <?php
                $rbreak = [
                  ['Skill coverage', (int)($readiness['coverage'] ?? 0), 40, 'var(--indigo)'],
                  ['Evidence', (int)($readiness['evidence'] ?? 0), 25, 'var(--teal)'],
                  ['Activity', (int)($readiness['activity'] ?? 0), 20, '#38BDF8'],
                  ['Learning pace', (int)($readiness['pace'] ?? 0), 15, '#FB923C'],
                ];
              ?>
              <?php foreach ($rbreak as [$lab,$val,$w,$col]): ?>
              <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;font-size:12px">
                <span style="width:88px;color:var(--muted);flex-shrink:0"><?= esc($lab) ?></span>
                <span style="flex:1;height:8px;background:rgba(255,255,255,.06);border-radius:5px;overflow:hidden"><span style="display:block;height:100%;width:<?= max(2,min(100,$val)) ?>%;background:<?= $col ?>"></span></span>
                <span style="width:30px;text-align:right;flex-shrink:0"><strong><?= $val ?></strong></span>
                <span style="width:34px;text-align:right;color:var(--muted);flex-shrink:0;font-size:11px">×<?= $w ?>%</span>
              </div>
              <?php endforeach; ?>
              <p class="muted" style="font-size:11px;margin:8px 0 0;text-align:center">No black box — each part is visible. You decide what to build next.</p>
            </div>
          </div>
          <?php if (!empty($edgeHas)): ?>
          <div style="padding-left:20px">
            <div class="section-label" style="text-align:center;margin-bottom:6px">EDGE work signals</div>
            <?= \App\Libraries\Edge::spiderDual($edgeSurvey ?? [], $edgeEvidence ?? []) ?>
            <div class="edge-legend" style="justify-content:center">
              <span class="edge-leg"><i class="edge-line-survey"></i>Survey</span>
              <span class="edge-leg"><i class="edge-line-evidence"></i>Evidence</span>
            </div>
          </div>
          <?php endif; ?>
        </div>
        <div class="row" style="justify-content:center;margin-top:8px">
          <button class="btn btn-ghost" data-why="1">Full formula</button>
        </div>
      </div>

      <?php if (!empty($edgeHas)): ?>
      <div class="card" style="border-left:3px solid var(--indigo)">
        <p class="muted" style="font-size:12px;margin:0">Three lenses, one story: <strong style="color:var(--text)">EDGE</strong> shows how many examples back each work signal (coverage, not a score) · <strong style="color:var(--text)">Readiness</strong> is how prepared you are for your target · <strong style="color:var(--text)">Smart Matching</strong> shows which roles fit.</p>
      </div>
      <div class="card">
        <div class="section-label">Lumina EDGE Profile</div>
        <p class="muted" style="font-size:13px;margin:4px 0 8px">A clear view of your strengths, growth areas and work signals.</p>
        <p class="muted" style="font-size:12px;margin:0 0 8px">Based on your answers and the evidence you chose to share. Add or confirm examples to strengthen each signal.</p>
        <div class="edge-tabs" style="display:flex;flex-wrap:wrap;gap:6px;margin:10px 0">
          <?php $__ti = 0; foreach (\App\Libraries\Edge::signals() as $__sk => $__sd): ?>
          <button type="button" class="edge-tab<?= $__ti===0?' active':'' ?>" data-tab="<?= esc($__sk) ?>" style="--tabc:<?= esc($__sd['hex']) ?>"><?= esc($__sd['name']) ?></button>
          <?php $__ti++; endforeach; ?>
        </div>
        <p class="muted" style="font-size:11px;margin:0 0 8px">Tap a signal to see how you approach it and the evidence behind it.</p>
        <?= \App\Libraries\Edge::cardsV2HTML($edgeSurvey ?? [], $edgeEvidence ?? [], $edgeQuotes ?? []) ?>
        <p class="muted" style="font-size:11px;margin-top:12px">This is decision support, not a personality test or hiring decision. You choose what to share.</p>
      </div>

      <div class="grid grid-2" style="align-items:start">
      <?php $edgeShared = session('edge_shared') ?? false; ?>
      <div class="card" style="border:1px solid rgba(108,92,231,.35)">
        <div class="section-label">Share with employers</div>
        <p class="muted" style="font-size:13px;margin:4px 0 10px">You decide whether an employer sees your EDGE evidence summary. Here is exactly what they would see — nothing more.</p>

        <div style="background:rgba(108,92,231,.06);border-radius:10px;padding:12px 14px;margin-bottom:12px">
          <div style="font-size:11px;font-weight:700;color:var(--indigo);letter-spacing:.04em;margin-bottom:6px">WHAT AN EMPLOYER WOULD SEE</div>
          <?php
            $shareSignals = [];
            foreach (($edgeEvidence ?? []) as $sig => $n) { if ($n > 0) $shareSignals[$sig] = $n; }
            arsort($shareSignals);
          ?>
          <?php if ($shareSignals): ?>
          <p class="muted" style="font-size:12px;margin:0 0 4px"><strong style="color:var(--text)">Signals with evidence on record:</strong></p>
          <div style="margin-bottom:8px">
            <?php foreach (array_slice(array_keys($shareSignals),0,3) as $sig): $sd = \App\Libraries\Edge::signals()[$sig] ?? []; ?><span class="skill" style="border-color:rgba(108,92,231,.4)"><?= esc($sd['name'] ?? $sig) ?> · <?= (int)$shareSignals[$sig] ?> example<?= $shareSignals[$sig]==1?'':'s' ?></span><?php endforeach; ?>
          </div>
          <?php endif; ?>
          <p class="muted" style="font-size:12px;margin:0">Plus your <strong style="color:var(--text)">Questions to Confirm</strong> — behavioural prompts an interviewer can ask. Employers never see your raw survey answers.</p>
        </div>

        <label style="display:flex;align-items:center;gap:10px;cursor:pointer;user-select:none;font-size:13px">
          <input type="checkbox" <?= $edgeShared ? 'checked' : '' ?> onchange="fetch('<?= base_url('passport/share-edge') ?>?on='+(this.checked?1:0),{method:'POST'}).then(()=>{document.getElementById('shareState').textContent=this.checked?'Shared for applications':'Not shared';})" style="width:18px;height:18px;accent-color:var(--indigo)">
          <span>Share my EDGE evidence summary for this application</span>
        </label>
        <p class="muted" style="font-size:11px;margin:8px 0 0">Status: <strong id="shareState" style="color:var(--text)"><?= $edgeShared ? 'Shared for applications' : 'Not shared' ?></strong> · Employers cannot reject, sort, or filter by EDGE — it is context for a conversation, not a gate.</p>
      </div>
      <?php else: ?>
      <div class="card">
        <div class="section-label">Lumina EDGE Profile</div>
        <p class="muted" style="font-size:13px;margin:4px 0 10px">Not ready yet — your work-style signals come from a short set of questions.</p>
        <a class="btn btn-primary" href="<?= base_url('onboard/edge') ?>">Discover your work approach</a>
      </div>
      <?php endif; ?>

      <?php if (!empty($p['consistency_flags'])): ?>
      <div class="card">
        <div class="section-label">Profile Consistency Check</div>
        <p class="muted" style="font-size:13px;margin:4px 0 8px">See where a clearer example could help.</p>
        <?php foreach ($p['consistency_flags'] as $f): ?>
          <div class="muted" style="font-size:12px;background:rgba(20,184,166,.08);border:1px solid rgba(20,184,166,.28);border-radius:8px;padding:7px 10px;margin-bottom:5px"><?= esc($f['message']) ?></div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
      </div>

      <div class="grid grid-2">
      <div class="card">
        <div class="section-label">Smart Matching</div>
        <p class="muted" style="font-size:13px;margin:4px 0 8px">See which roles fit you now—and what to build next.</p>
        <p class="muted" style="margin:0"><strong style="color:var(--text)"><?= esc($role['title']) ?></strong> — <?= (int)$match['matchScore'] ?>% match (<?= esc($match['label']) ?>).
        <?php if (!empty($match['gap'])): ?> Gap: <?= esc(implode(', ', array_map(fn($g)=>ucwords(str_replace('_',' ',$g)),$match['gap']))) ?>.<?php endif; ?></p>
      </div>

      <div class="card">
        <div class="section-label">Next action</div>
        <a class="btn btn-primary btn-lg" href="<?= base_url('match') ?>" style="width:100%;justify-content:center">Find opportunities</a>
        <p class="muted" style="font-size:12px;margin:10px 0 0">Choose a direction worth exploring.</p>
      </div>
      </div>
    </div>
  </div>

  <div class="row" style="margin-top:18px">
    <a class="btn btn-ghost" href="<?= base_url('start') ?>">Rebuild</a>
  </div>
</section>


<div class="drawer-backdrop" id="whyBackdrop"></div>
<aside class="drawer" id="whyDrawer" aria-label="Why this score">
  <div class="row" style="justify-content:space-between">
    <h3>Why this readiness?</h3>
    <button class="btn btn-ghost" data-close="1">✕</button>
  </div>
  <p class="muted"><?= esc($whyText) ?></p>
  <div class="section-label" style="margin-top:14px">How this score is built</div>
  <table style="width:100%;border-collapse:collapse;font-size:13px;margin:4px 0">
    <tbody>
      <?php foreach ($rc as $r): $pts=(int)round($r[1]*$r[2]/100); ?>
        <tr>
          <td style="padding:4px 10px 4px 0;color:var(--muted)"><?= $r[0] ?></td>
          <td style="padding:4px 8px;text-align:right"><?= $r[1] ?></td>
          <td style="padding:4px 8px;color:var(--muted)">&times;<?= $r[2] ?>%</td>
          <td style="padding:4px 0;text-align:right"><strong><?= $pts ?></strong></td>
        </tr>
      <?php endforeach; ?>
      <tr>
        <td style="padding:8px 10px 0 0;border-top:1px solid var(--line)"><strong>Adaptive Readiness</strong></td>
        <td style="border-top:1px solid var(--line)"></td><td style="border-top:1px solid var(--line)"></td>
        <td style="padding:8px 0 0;text-align:right;border-top:1px solid var(--line)"><strong><?= (int)$readiness['score'] ?></strong></td>
      </tr>
    </tbody>
  </table>
  <p class="muted" style="font-size:12px">Points = component &times; weight; they add up to your readiness. Every component is 0–100. 40% coverage · 25% evidence · 20% activity · 15% pace.</p>
  <p class="purpose" style="margin-top:12px">Decision support only. The number rises as you close gaps — try it in Career Compass.</p>
</aside>
<script>
(function(){
  var tabs = document.querySelectorAll('.edge-tab');
  var cards = document.querySelectorAll('.edge-card[data-sig]');
  if(!tabs.length || !cards.length) return;
  function show(sig){
    cards.forEach(function(c){ c.classList.toggle('edge-card-hidden', c.getAttribute('data-sig') !== sig); });
    tabs.forEach(function(t){ t.classList.toggle('active', t.getAttribute('data-tab') === sig); });
  }
  tabs.forEach(function(t){ t.addEventListener('click', function(){ show(t.getAttribute('data-tab')); }); });
  // Bonus: klik label paksi spider -> tunjuk kad signal itu
  var spider = document.querySelector('.edge-spider');
  if(spider){
    var order = Array.prototype.map.call(tabs, function(t){ return t.getAttribute('data-tab'); });
    spider.querySelectorAll('text').forEach(function(txt){
      var nm = (txt.textContent||'').trim().toLowerCase();
      tabs.forEach(function(t){ if((t.textContent||'').trim().toLowerCase() === nm){ txt.style.cursor='pointer'; txt.addEventListener('click', function(){ show(t.getAttribute('data-tab')); }); } });
    });
  }
})();
</script>
<?= $this->endSection() ?>
