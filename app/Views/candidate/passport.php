<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
helper('ui');
$p = $profile;
$riskClass = $risk === 'On track' ? 'ok' : ($risk === 'Needs a nudge' ? 'nudge' : 'risk');
$evidenceLines = array_filter(array_map('trim', preg_split('/[;.\n]+/', $p['evidence_text'] ?? '')));
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
<section class="hero">
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

  <div class="grid grid-3">
    <!-- ===== LEFT 8/12 : digital CV ===== -->
    <div style="grid-column:span 2;display:flex;flex-direction:column;gap:16px">

      <div class="card">
        <div class="section-label">Profile summary</div>
        <p style="margin:6px 0 0"><?= esc($pName ?: 'This candidate') ?> is working toward <strong style="color:var(--text)"><?= esc($role['title']) ?></strong>.
        Lumina read <?= (int)$skillTotal ?> skills from the evidence shared<?php if ($skillInferred): ?>, including <?= (int)$skillInferred ?> not listed directly<?php endif; ?>.</p>
        <?php if (!empty($p['animalLabel'])): ?>
          <p class="muted" style="font-size:13px;margin-top:8px">Work style: <strong class="gold"><?= esc($p['animalLabel']) ?></strong><?php if (!empty($p['traits'])): ?> — <?= esc(implode(' · ', $p['traits'])) ?><?php endif; ?>. A lightweight signal, not a fixed label.</p>
        <?php endif; ?>
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
        <div>
          <?php foreach (($p['skills'] ?? []) as $code => $s):
              $evLabel = $s['evidence_label'] ?? (($s['source'] ?? '') === 'stated' ? 'Stated' : 'Inferred');
              $evTitle = !empty($s['from']) ? "{$evLabel} \xc2\xb7 where we found it: {$s['from']}" : $evLabel;
              echo '<span title="' . esc($evTitle, 'attr') . '">' . lumina_skill(ucwords(str_replace('_', ' ', $code)), $s['source'], $s['confidence']) . '</span>';
          endforeach; ?>
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

    <!-- ===== RIGHT 4/12 : Lumina insights ===== -->
    <div style="display:flex;flex-direction:column;gap:16px">
      <div class="card">
        <div class="section-label">Lumina insights</div>
        <h3 style="margin:6px 0 2px">Your readiness today</h3>
        <p class="muted" style="font-size:13px;margin:0 0 8px">Based on the skills, evidence and activity you have shared.</p>
        <div class="donut-wrap"><?= lumina_donut($readiness['score'], $role['title'], $role['color']) ?></div>
        <div class="row" style="justify-content:center;margin-top:8px">
          <span class="pill <?= $riskClass ?>"><?= esc($risk) ?></span>
        </div>
        <div class="row" style="justify-content:center;margin-top:8px">
          <button class="btn btn-ghost" data-why="1">Why this score?</button>
        </div>
      </div>

      <?php if (!empty($potentialProfile) && !empty($potentialProfile['has_quiz_data'])): ?>
      <div class="card">
        <div class="section-label">Lumina EDGE Profile</div>
        <p class="muted" style="font-size:13px;margin:4px 0 8px">A clear view of your strengths, growth areas and work signals.</p>
        <p class="muted" style="font-size:12px;margin:0 0 8px">Your strongest signals are in <?= esc(implode(' and ', array_slice($potentialProfile['top_strengths'], 0, 2))) ?>.</p>
        <canvas id="ppRadar" height="200"></canvas>
        <h3 style="margin:12px 0 6px"><?= esc($potentialProfile['thinking_style']) ?></h3>
        <?php if (!empty($potentialProfile['animal'])): $ab = $potentialProfile['animal']; ?>
          <p class="muted" style="font-size:12px;margin:0"><?= esc($ab['line']) ?></p>
        <?php endif; ?>
        <div class="row" style="flex-wrap:wrap;gap:6px;margin-top:10px">
          <?php foreach ($potentialProfile['top_strengths'] as $s): ?><?= lumina_chip($s, 'indigo') ?><?php endforeach; ?>
        </div>
        <div class="section-label" style="margin-top:12px">Growing areas</div>
        <div class="row" style="flex-wrap:wrap;gap:6px">
          <?php foreach ($potentialProfile['growing_areas'] as $g): ?><?= lumina_chip($g, 'teal') ?><?php endforeach; ?>
        </div>
        <div class="section-label" style="margin-top:12px">Build next</div>
        <div class="row" style="flex-wrap:wrap;gap:6px">
          <?php foreach ($potentialProfile['build_next'] as $b): ?><?= lumina_chip($b, 'violet') ?><?php endforeach; ?>
        </div>
        <p class="muted" style="font-size:11px;margin-top:10px"><?= esc($potentialProfile['disclaimer']) ?></p>
      </div>
      <?php else: ?>
      <div class="card">
        <div class="section-label">Lumina EDGE Profile</div>
        <p class="muted" style="font-size:13px;margin:4px 0 10px">Not ready yet — your work-style signals come from a short set of questions.</p>
        <a class="btn btn-primary" href="<?= base_url('onboard/animal') ?>">Discover your work-style signals</a>
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

      <div class="card">
        <div class="section-label">Smart Matching</div>
        <p class="muted" style="font-size:13px;margin:4px 0 8px">See which roles fit you now—and what to build next.</p>
        <p class="muted" style="margin:0"><strong style="color:var(--text)"><?= esc($role['title']) ?></strong> — <?= (int)$match['matchScore'] ?>% match (<?= esc($match['label']) ?>).
        <?php if (!empty($match['gap'])): ?> Gap: <?= esc(implode(', ', array_map(fn($g)=>ucwords(str_replace('_',' ',$g)),$match['gap']))) ?>.<?php endif; ?></p>
      </div>

      <div class="card">
        <div class="section-label">Next action</div>
        <a class="btn btn-primary btn-lg" href="<?= base_url('compass') ?>" style="width:100%;justify-content:center">Explore Career Compass</a>
        <p class="muted" style="font-size:12px;margin:10px 0 0">Choose a direction worth exploring.</p>
      </div>
    </div>
  </div>

  <div class="row" style="margin-top:18px">
    <a class="btn btn-ghost" href="<?= base_url('start') ?>">Rebuild</a>
  </div>
</section>

<?php if (!empty($potentialProfile) && !empty($potentialProfile['has_quiz_data'])): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
  var domains = <?= json_encode($potentialProfile['domains']) ?>;
  var labels = Object.keys(domains).map(function (k) { return k.charAt(0).toUpperCase() + k.slice(1); });
  var data = Object.values(domains);
  var css = getComputedStyle(document.documentElement);
  var indigo = (css.getPropertyValue('--indigo') || '#6C5CE7').trim();
  var ctx = document.getElementById('ppRadar');
  if (ctx && window.Chart) {
    new Chart(ctx, {
      type: 'radar',
      data: { labels: labels, datasets: [{ label: 'EDGE Signals', data: data,
        backgroundColor: 'rgba(108,92,231,.15)', borderColor: indigo, pointBackgroundColor: indigo }] },
      options: { scales: { r: { min: 0, max: 100, ticks: { display: false },
          grid: { color: 'rgba(255,255,255,.08)' }, angleLines: { color: 'rgba(255,255,255,.08)' },
          pointLabels: { color: '#9aa4b2', font: { size: 10 } } } },
        plugins: { legend: { display: false } } }
    });
  }
})();
</script>
<?php endif; ?>

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
<?= $this->endSection() ?>
