<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php helper('ui');
  $cls = $band==='On track'?'ok':($band==='Needs a nudge'?'nudge':'risk');
  $rc = [
    ['Skill coverage', (int)$rd['coverage'], 40],
    ['Evidence strength', (int)$rd['evidence'], 25],
    ['Activity signals', (int)$rd['activity'], 20],
    ['Learning pace', (int)$rd['pace'], 15],
  ];
  $evLines = array_filter(array_map('trim', preg_split('/[;.\n]+/', $s['evidence_text'] ?? '')));
?>
<section class="hero">
  <div class="section-label">Student · <?= esc($s['university'] ?? '') ?></div>
  <h1><?= esc($s['name']) ?></h1>
  <p class="purpose"><?= esc($s['programme'] ?? '') ?><?= !empty($s['faculty']) ? ' · ' . esc($s['faculty']) : '' ?> · target: <?= esc($s['target_domain'] ?? '') ?> · <span class="gold"><?= empty($s['has_resume']) ? 'No resume — profile from evidence' : 'Has resume' ?></span></p>
  <div class="row" style="margin-top:10px"><a class="btn btn-ghost" href="<?= base_url('university') ?>">← Back</a></div>
</section>

<section class="section">
  <div class="grid grid-2">
    <div class="card">
      <div class="section-label">Readiness · why this score</div>
      <div class="donut-wrap"><?= lumina_donut((int)$ready, 'Employability', 'var(--indigo)') ?></div>
      <div style="text-align:center;margin:6px 0 10px"><span class="pill <?= $cls ?>"><?= esc($band) ?></span></div>
      <table style="width:100%;border-collapse:collapse;font-size:13px">
        <tbody>
        <?php foreach ($rc as $r): $pts=(int)round($r[1]*$r[2]/100); ?>
          <tr>
            <td style="padding:3px 10px 3px 0;color:var(--muted)"><?= $r[0] ?></td>
            <td style="padding:3px 8px;text-align:right"><?= $r[1] ?></td>
            <td style="padding:3px 8px;color:var(--muted)">&times;<?= $r[2] ?>%</td>
            <td style="padding:3px 0;text-align:right"><strong><?= $pts ?></strong></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <p class="muted" style="font-size:12px;margin-top:6px">Field-agnostic employability drives the band; the breakdown shows role readiness for context.</p>
    </div>
    <div class="card">
      <div class="section-label">Work Animal · from evidence</div>
      <div style="margin-bottom:8px">
        <span class="skill"><?= esc($animal['primary']['label'] ?? '—') ?> <span class="conf">primary</span></span>
        <span class="skill"><?= esc($animal['secondary']['label'] ?? '—') ?> <span class="conf">secondary</span></span>
      </div>
      <div class="section-label" style="margin-top:8px">Skills detected</div>
      <div style="margin-bottom:6px">
        <?php foreach ($explained as $code => $sk): ?><?= lumina_skill(\App\Libraries\Catalog::label($code), $sk['source'], $sk['confidence'] ?? 1) ?><?php endforeach; ?>
        <?php if (empty($explained)): ?><span class="muted">No skills detected yet.</span><?php endif; ?>
      </div>
      <?php if (!empty($gapLabels)): ?>
        <div class="section-label" style="margin-top:8px">Gaps to close</div>
        <p style="font-size:13px"><strong style="color:var(--gold)"><?= esc(implode(', ', $gapLabels)) ?></strong></p>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="section">
  <div class="card">
    <div class="section-label">Evidence on record</div>
    <ul class="muted" style="font-size:14px;padding-left:18px;margin:0 0 10px"><?php foreach ($evLines as $l): ?><li><?= esc($l) ?></li><?php endforeach; ?><?php if (empty($evLines)): ?><li>—</li><?php endif; ?></ul>
    <?= lumina_note('Recommended action: ' . esc($rec)) ?>
    <p class="purpose" style="font-size:12px">Demo cohort data — no real student record. Decision support only.</p>
  </div>
</section>
<?= $this->endSection() ?>
