<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
helper('ui');
function heatColor($v){ return $v >= 75 ? 'var(--ok)' : ($v >= 50 ? 'var(--nudge)' : 'var(--risk)'); }
?>
<section class="hero">
  <div class="section-label">For Universities · Graduate Outcomes</div>
  <h1>Which talent is almost ready — and what unlocks them.</h1>
  <p class="purpose">Live cohort readiness across your faculties. <?= (int)$total ?> students · <strong class="gold"><?= (int)($noResumePct ?? 0) ?>% have no resume yet</strong> (<?= (int)($noResumeCount ?? 0) ?> students).</p>
  <div class="row" style="margin-top:12px">
    <a class="btn btn-gold btn-lg" href="<?= base_url('university/interventions') ?>">Generate Intervention Plan →</a>
  </div>
</section>

<!-- KPI cards -->
<section class="section">
  <div class="grid grid-4">
    <?php foreach ($kpis as $k): ?>
      <?= lumina_kpi($k['n'], $k['l'], $k['t']) ?>
    <?php endforeach; ?>
  </div>
</section>

<!-- Faculty bar + segmentation -->
<section class="section">
  <div class="grid grid-2">
    <div class="card">
      <div class="section-label">Employability by faculty</div>
      <canvas id="facChart" height="170"></canvas>
    </div>
    <div class="card">
      <div class="section-label">Student segmentation</div>
      <canvas id="segChart" height="170"></canvas>
      <div class="row" style="justify-content:center;gap:14px;margin-top:10px">
        <span class="pill ok">On track <?= (int)$bands['On track'] ?></span>
        <span class="pill nudge">Needs a nudge <?= (int)$bands['Needs a nudge'] ?></span>
        <span class="pill risk">At risk <?= (int)$bands['At risk'] ?></span>
      </div>
    </div>
  </div>
</section>

<!-- Work Animal distribution + Top skill gaps -->
<section class="section">
  <div class="grid grid-2">
    <div class="card">
      <div class="section-label">Work Animal distribution · cohort</div>
      <canvas id="animalChart" height="180"></canvas>
    </div>
    <div class="card">
      <div class="section-label">Top skill gaps · across cohort</div>
      <?php foreach (($topGaps ?? []) as $g): ?>
        <div style="margin:8px 0">
          <div class="row" style="justify-content:space-between"><span><?= esc($g['label']) ?></span><span class="muted"><?= (int)$g['count'] ?> students</span></div>
          <div style="height:8px;background:rgba(255,255,255,.06);border-radius:6px;overflow:hidden;margin-top:4px">
            <div style="height:100%;width:<?= $total? (int)round(100*$g['count']/$total):0 ?>%;background:var(--gold)"></div>
          </div>
        </div>
      <?php endforeach; ?>
      <?php if (empty($topGaps)): ?><p class="muted">No gaps detected.</p><?php endif; ?>
    </div>
  </div>
</section>

<!-- Heatmap + intervention -->
<section class="section">
  <div class="grid grid-2">
    <div class="card">
      <div class="section-label">Outcome heatmap · by programme</div>
      <div class="table-wrap"><table style="width:100%;border-collapse:collapse;font-size:13px;margin-top:8px">
        <thead><tr>
          <th style="text-align:left;padding:8px;color:var(--muted)">Programme</th>
          <th style="padding:8px;color:var(--muted)">Career-ready</th>
          <th style="padding:8px;color:var(--muted)">Industry</th>
          <th style="padding:8px;color:var(--muted)">High-income</th>
        </tr></thead>
        <tbody>
          <?php foreach ($heat as $row): ?>
            <tr>
              <td style="padding:8px"><?= esc($row['programme']) ?></td>
              <?php foreach (['ready','industry','highincome'] as $col): $v=(int)$row[$col]; ?>
                <td style="padding:6px;text-align:center">
                  <span style="display:inline-block;min-width:38px;padding:4px 8px;border-radius:8px;font-weight:700;
                    color:#0B1220;background:<?= heatColor($v) ?>"><?= $v ?></span>
                </td>
              <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($heat)): ?><tr><td colspan="4" class="muted" style="padding:8px">No data.</td></tr><?php endif; ?>
        </tbody>
      </table></div>
    </div>
    <div class="card">
      <div class="section-label">Recommended intervention</div>
      <h3 style="margin:6px 0"><?= esc($intervention) ?></h3>
      <p class="purpose">Based on the most common skill gap across the cohort.</p>
      <a class="btn btn-ghost" href="<?= base_url('university/interventions') ?>" style="margin-top:6px">See full intervention plan →</a>
      <?php if (!empty($employers)): ?>
        <div class="section-label" style="margin-top:16px">Top employers hiring your graduates</div>
        <?php foreach ($employers as $e): ?>
          <div class="ev"><strong><?= esc($e['employer']) ?></strong> — satisfaction <?= (int)$e['satisfaction'] ?>%</div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- Students needing support + gaps by programme -->
<section class="section">
  <div class="grid grid-2">
    <div class="card">
      <div class="section-label">Students needing support · At risk</div>
      <div class="stack">
        <?php foreach (($needSupport ?? []) as $s): ?>
          <div class="card card-tight" style="display:flex;align-items:center;gap:12px">
            <div class="ring risk"><?= (int)$s['readiness'] ?></div>
            <div><strong><?= esc($s['name']) ?></strong><div class="muted" style="font-size:13px"><?= esc($s['programme']) ?> · <?= esc($s['faculty']) ?></div></div>
          </div>
        <?php endforeach; ?>
        <?php if (empty($needSupport)): ?><p class="muted">No at-risk students — great cohort health.</p><?php endif; ?>
      </div>
    </div>
    <div class="card">
      <div class="section-label">Top skill gap · by programme</div>
      <div class="table-wrap"><table style="width:100%;border-collapse:collapse;font-size:13px;margin-top:8px">
        <thead><tr><th style="text-align:left;padding:8px;color:var(--muted)">Programme</th><th style="padding:8px;color:var(--muted)">Gap</th><th style="padding:8px;color:var(--muted)">At risk</th></tr></thead>
        <tbody>
          <?php foreach (($gapsByProgramme ?? []) as $g): ?>
            <tr><td style="padding:8px"><?= esc($g['programme']) ?></td><td style="padding:8px;text-align:center"><span class="skill inferred"><?= esc($g['gap']) ?></span></td><td style="padding:8px;text-align:center"><?= (int)$g['atrisk'] ?>/<?= (int)$g['total'] ?></td></tr>
          <?php endforeach; ?>
          <?php if (empty($gapsByProgramme)): ?><tr><td colspan="3" class="muted" style="padding:8px">No data.</td></tr><?php endif; ?>
        </tbody>
      </table></div>
    </div>
  </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const FAC = <?= json_encode($faculty) ?>;
const BANDS = <?= json_encode(array_values($bands)) ?>;
const ANIMALS = <?= json_encode($animalDist ?? []) ?>;
new Chart(document.getElementById('facChart'), {
  type:'bar',
  data:{ labels:Object.keys(FAC), datasets:[{ data:Object.values(FAC), backgroundColor:'#6D5DFB', borderRadius:6 }] },
  options:{ indexAxis:'y', plugins:{legend:{display:false}},
    scales:{ x:{min:0,max:100,grid:{color:'rgba(255,255,255,.06)'},ticks:{color:'#9AA4B8'}}, y:{grid:{display:false},ticks:{color:'#F5F7FA'}} } }
});
new Chart(document.getElementById('segChart'), {
  type:'doughnut',
  data:{ labels:['On track','Needs a nudge','At risk'], datasets:[{ data:BANDS, backgroundColor:['#2E9E5B','#E0A82E','#E0526B'], borderColor:'#111A2E', borderWidth:3 }] },
  options:{ plugins:{legend:{display:false}}, cutout:'62%' }
});
new Chart(document.getElementById('animalChart'), {
  type:'bar',
  data:{ labels:Object.keys(ANIMALS), datasets:[{ data:Object.values(ANIMALS), backgroundColor:'#14B8A6', borderRadius:5 }] },
  options:{ plugins:{legend:{display:false}},
    scales:{ x:{grid:{display:false},ticks:{color:'#9AA4B8',maxRotation:60,minRotation:45}}, y:{grid:{color:'rgba(255,255,255,.06)'},ticks:{color:'#9AA4B8'}} } }
});
</script>
<?= $this->endSection() ?>
