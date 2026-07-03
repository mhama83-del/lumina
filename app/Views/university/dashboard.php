<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
helper('ui');
function heatColor($v){ return $v >= 75 ? 'var(--ok)' : ($v >= 50 ? 'var(--nudge)' : 'var(--risk)'); }
?>

<section class="hero">
  <div class="section-label">For Universities · Graduate Outcomes</div>
  <h1>Which talent is almost ready — and what unlocks them.</h1>
  <p class="purpose">Live cohort readiness across your faculties. <?= (int)$total ?> students in this view.</p>
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

<!-- KPI heatmap + intervention -->
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

      <?php if (!empty($employers)): ?>
        <div class="section-label" style="margin-top:16px">Top employers hiring your graduates</div>
        <?php foreach ($employers as $e): ?>
          <div class="ev"><strong><?= esc($e['employer']) ?></strong> — satisfaction <?= (int)$e['satisfaction'] ?>%</div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const FAC = <?= json_encode($faculty) ?>;
const BANDS = <?= json_encode(array_values($bands)) ?>;

new Chart(document.getElementById('facChart'), {
  type:'bar',
  data:{ labels:Object.keys(FAC),
    datasets:[{ data:Object.values(FAC), backgroundColor:'#6D5DFB', borderRadius:6 }] },
  options:{ indexAxis:'y', plugins:{legend:{display:false}},
    scales:{ x:{min:0,max:100,grid:{color:'rgba(255,255,255,.06)'},ticks:{color:'#9AA4B8'}},
             y:{grid:{display:false},ticks:{color:'#F5F7FA'}} } }
});
new Chart(document.getElementById('segChart'), {
  type:'doughnut',
  data:{ labels:['On track','Needs a nudge','At risk'],
    datasets:[{ data:BANDS, backgroundColor:['#2E9E5B','#E0A82E','#E0526B'], borderColor:'#111A2E', borderWidth:3 }] },
  options:{ plugins:{legend:{display:false}}, cutout:'62%' }
});
</script>
<?= $this->endSection() ?>
