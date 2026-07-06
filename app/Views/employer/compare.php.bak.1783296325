<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
helper('ui');
$pill = function ($v) { return $v >= 75 ? 'ok' : ($v >= 50 ? 'nudge' : 'risk'); };
?>
<section class="hero">
  <div class="section-label">Compare candidates</div>
  <h1>Side by side for <?= esc($role['title']) ?>.</h1>
  <p class="purpose"><?= esc($role['company']) ?> · <?= esc($role['domain']) ?>. Explainable metrics — <em>decision support only, the recruiter decides.</em></p>
  <div class="row" style="margin-top:12px">
    <a class="btn btn-ghost" href="<?= base_url('employer?role=' . esc($role['key'], 'url')) ?>">← Back to ranked list</a>
  </div>
</section>

<section class="section">
  <?php if (empty($cands)): ?>
    <div class="card"><p class="muted">Select 2–4 candidates from the ranked list to compare.</p></div>
  <?php else: ?>
  <div class="card" style="overflow-x:auto">
    <table class="cmp" style="width:100%;border-collapse:collapse;min-width:<?= 220 + count($cands)*200 ?>px">
      <thead>
        <tr>
          <th style="text-align:left;padding:10px;vertical-align:bottom;min-width:180px"></th>
          <?php foreach ($cands as $c): ?>
            <th style="text-align:left;padding:10px;border-bottom:1px solid var(--line)">
              <strong><?= esc($c['name']) ?></strong>
              <div class="muted" style="font-size:12px"><?= esc($c['university']) ?></div>
              <div class="muted" style="font-size:12px"><?= esc($c['programme']) ?></div>
            </th>
          <?php endforeach; ?>
        </tr>
      </thead>
      <tbody>
        <?php
        $rowStart = '<tr><td style="padding:10px;color:var(--muted);font-size:13px;border-top:1px solid var(--line)">';
        $cellStart = '<td style="padding:10px;border-top:1px solid var(--line)">';
        ?>
        <!-- Match -->
        <?= $rowStart ?>Match score</td>
        <?php foreach ($cands as $c): ?><?= $cellStart ?><span class="pill <?= $c['label']==='best'?'ok':($c['label']==='growth'?'nudge':'risk') ?>"><?= (int)$c['match'] ?>%</span></td><?php endforeach; ?></tr>
        <!-- Readiness -->
        <?= $rowStart ?>Readiness</td>
        <?php foreach ($cands as $c): ?><?= $cellStart ?><span class="pill <?= $pill($c['readiness']) ?>"><?= (int)$c['readiness'] ?>%</span></td><?php endforeach; ?></tr>
        <!-- Learning velocity -->
        <?= $rowStart ?>Learning velocity</td>
        <?php foreach ($cands as $c): ?><?= $cellStart ?><strong><?= (int)$c['velocity'] ?></strong> <span class="muted" style="font-size:12px"><?= esc($c['velBand']) ?></span></td><?php endforeach; ?></tr>
        <!-- Work animal -->
        <?= $rowStart ?>Work Animal</td>
        <?php foreach ($cands as $c): ?><?= $cellStart ?><span class="skill"><?= esc($c['animal']) ?></span></td><?php endforeach; ?></tr>
        <!-- Technical fit -->
        <?= $rowStart ?>Technical fit</td>
        <?php foreach ($cands as $c): ?><?= $cellStart ?><span class="pill <?= $pill($c['techFit']) ?>"><?= (int)$c['techFit'] ?>%</span></td><?php endforeach; ?></tr>
        <!-- Leadership fit -->
        <?= $rowStart ?>Leadership fit</td>
        <?php foreach ($cands as $c): ?><?= $cellStart ?><span class="pill <?= $pill($c['leadFit']) ?>"><?= (int)$c['leadFit'] ?>%</span></td><?php endforeach; ?></tr>
        <!-- Evidence richness -->
        <?= $rowStart ?>Evidence richness</td>
        <?php foreach ($cands as $c): ?><?= $cellStart ?><span class="pill <?= $pill($c['evidence']) ?>"><?= (int)$c['evidence'] ?></span></td><?php endforeach; ?></tr>
        <!-- Missing skills -->
        <?= $rowStart ?>Missing skills</td>
        <?php foreach ($cands as $c): ?><?= $cellStart ?><?php if (!empty($c['missing'])): foreach ($c['missing'] as $g): ?><span class="skill inferred" style="font-size:11px"><?= esc($g) ?></span> <?php endforeach; else: ?><span class="muted" style="font-size:12px">none</span><?php endif; ?></td><?php endforeach; ?></tr>
        <!-- Risk -->
        <?= $rowStart ?>Risk band</td>
        <?php foreach ($cands as $c): ?><?= $cellStart ?><span class="pill <?= $c['risk']==='On track'?'ok':($c['risk']==='Needs a nudge'?'nudge':'risk') ?>"><?= esc($c['risk']) ?></span></td><?php endforeach; ?></tr>
        <!-- Best-fit role -->
        <?= $rowStart ?>Best-fit role</td>
        <?php foreach ($cands as $c): ?><?= $cellStart ?><?= esc($c['bestRole']) ?></td><?php endforeach; ?></tr>
        <!-- Why hire -->
        <?= $rowStart ?>Why hire</td>
        <?php foreach ($cands as $c): ?><?= $cellStart ?><span class="muted" style="font-size:12.5px"><?= esc($c['why']) ?></span></td><?php endforeach; ?></tr>
      </tbody>
    </table>
    <p class="purpose" style="margin-top:12px">Simulated, explainable scoring for the demo — every number traces back to skills, evidence, and trajectory.</p>
  </div>
  <?php endif; ?>
</section>
<?= $this->endSection() ?>
