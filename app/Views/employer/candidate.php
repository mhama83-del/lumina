<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
helper('ui');
$shortlist = $shortlist ?? [];
$bandCls = $band==='On track'?'ok':($band==='Needs a nudge'?'nudge':'risk');
$isShort = in_array((int)$s['id'], $shortlist, true);
$backUrl = $role ? base_url('employer/role/'.(int)$role['id']) : base_url('employer');
?>
<section class="hero">
  <div class="section-label">Candidate Brief<?= $role ? ' · for ' . esc($role['role_title']) : '' ?></div>
  <h1><?= esc($s['name']) ?></h1>
  <p class="purpose"><?= esc($s['university']) ?> · <?= esc($s['programme']) ?><?= !empty($s['faculty']) ? ' · ' . esc($s['faculty']) : '' ?> · target: <?= esc($s['target_domain']) ?><?= (isset($s['cgpa']) && is_numeric($s['cgpa'])) ? ' · CGPA ' . esc($s['cgpa']) : '' ?></p>
  <div class="row" style="margin-top:10px;gap:8px">
    <a class="btn btn-ghost" href="<?= $backUrl ?>">← Back</a>
    <a class="btn <?= $isShort?'btn-gold':'btn-ghost' ?>" href="<?= base_url('employer/shortlist?id='.(int)$s['id'].($role?'&role_id='.(int)$role['id']:'')) ?>"><?= $isShort?'★ Shortlisted':'☆ Shortlist' ?></a>
  </div>
</section>

<section class="section">
  <div class="grid grid-3">
    <div class="card"><div class="donut-wrap"><?= lumina_donut((int)$readiness, 'Readiness', 'var(--indigo)') ?></div>
      <div style="text-align:center;margin-top:6px"><span class="pill <?= $bandCls ?>"><?= esc($band) ?></span></div>
    </div>
    <div class="card">
      <div class="section-label">Work Animal · from evidence</div>
      <div style="margin-bottom:6px">
        <span class="skill"><?= esc($animal['primary']['label']) ?> <span class="conf">primary</span></span>
        <span class="skill"><?= esc($animal['secondary']['label']) ?> <span class="conf">secondary</span></span>
        <span class="skill"><?= esc($animal['growth']['label']) ?> <span class="conf">growth</span></span>
      </div>
      <p class="muted" style="font-size:12px"><?= esc($animal['primary']['role'] ?? '') ?> · confidence <?= (int)($animal['confidence'] ?? 0) ?>%</p>
    </div>
    <div class="card">
      <div class="section-label">Evidence signals</div>
      <p class="muted" style="font-size:13px"><?= count($projects) ?> project(s) · <?= count($leadership) ?> leadership signal(s) · <?= count($explained) ?> skills detected</p>
      <p class="muted" style="font-size:12px;margin-top:6px">Has resume: <?= !empty($s['has_resume']) ? 'Yes' : 'No — profile built from evidence' ?></p>
    </div>
  </div>
</section>

<section class="section">
  <div class="grid grid-2">
    <div class="card">
      <div class="section-label">Evidence on record · candidate's own words</div>
      <p style="font-size:14px;line-height:1.6"><?= esc($s['evidence_text'] ?: '—') ?></p>
      <?php if ($projects): ?><div class="section-label" style="margin-top:10px">Projects detected</div><ul class="muted" style="font-size:13px;padding-left:18px;margin:0"><?php foreach ($projects as $x): ?><li><?= esc($x) ?></li><?php endforeach; ?></ul><?php endif; ?>
      <?php if ($leadership): ?><div class="section-label" style="margin-top:10px">Leadership detected</div><ul class="muted" style="font-size:13px;padding-left:18px;margin:0"><?php foreach ($leadership as $x): ?><li><?= esc($x) ?></li><?php endforeach; ?></ul><?php endif; ?>
    </div>
    <div class="card">
      <div class="section-label">Skills detected</div>
      <div>
        <?php foreach ($explained as $code => $sk): ?>
          <?= lumina_skill(\App\Libraries\Catalog::label($code), $sk['source'], $sk['confidence'] ?? 1) ?>
        <?php endforeach; ?>
        <?php if (empty($explained)): ?><span class="muted">No skills detected yet.</span><?php endif; ?>
      </div>
      <p class="muted" style="font-size:12px;margin-top:10px">Solid chips = stated · dashed = inferred by Lumina from evidence (with confidence).</p>
    </div>
  </div>
</section>

<?php if ($role && $match): ?>
<section class="section">
  <div class="card">
    <div class="section-label">Match to <?= esc($role['role_title']) ?> · <?= esc($role['company_name']) ?></div>
    <?php
      $wrows = [
        ['Skill', (int)$match['skill_match_score'], 40],
        ['Evidence', (int)$match['evidence_strength_score'], 20],
        ['Learning velocity', (int)$match['learning_velocity_score'], 20],
        ['Work-Animal fit', (int)$match['animal_fit_score'], 10],
        ['Domain', (int)$match['domain_fit_score'], 5],
        ['CGPA', (int)$match['academic_fit_score'], 5],
      ];
      $tm = $match['skill_overlap'][0] ?? $role['target_domain'];
      $mm = $match['missing_skills'][0] ?? null;
    ?>
    <div class="row" style="align-items:center;gap:12px;margin-bottom:8px">
      <div class="ring gold"><?= (int)$match['match_score'] ?></div>
      <span class="pill <?= $match['match_score']>=70?'ok':($match['match_score']>=55?'nudge':'risk') ?>"><?= esc($match['fit_label']) ?></span>
    </div>
    <table style="width:100%;max-width:520px;border-collapse:collapse;font-size:13px;margin:4px 0">
      <tbody>
      <?php foreach ($wrows as $wr): $pts=(int)round($wr[1]*$wr[2]/100); ?>
        <tr>
          <td style="padding:3px 10px 3px 0;color:var(--muted)"><?= $wr[0] ?></td>
          <td style="padding:3px 8px;text-align:right"><?= $wr[1] ?></td>
          <td style="padding:3px 8px;color:var(--muted)">&times;<?= $wr[2] ?>%</td>
          <td style="padding:3px 0;text-align:right"><strong><?= $pts ?></strong></td>
        </tr>
      <?php endforeach; ?>
        <tr><td style="padding:6px 10px 0 0;border-top:1px solid var(--line)"><strong>Talent Match</strong></td><td style="border-top:1px solid var(--line)"></td><td style="border-top:1px solid var(--line)"></td><td style="padding:6px 0 0;text-align:right;border-top:1px solid var(--line)"><strong><?= (int)$match['match_score'] ?></strong></td></tr>
      </tbody>
    </table>
    <div class="grid grid-2" style="margin-top:8px">
      <div><div class="section-label">Skill overlap</div><p style="font-size:13px"><?= esc(implode(', ', $match['skill_overlap'])) ?: '—' ?></p></div>
      <div><div class="section-label">Missing / to develop</div><p style="font-size:13px"><?= esc(implode(', ', $match['missing_skills'])) ?: 'none' ?></p></div>
    </div>
    <div class="section-label" style="margin-top:8px">Questions to Confirm</div>
    <p class="muted" style="font-size:12px;margin:2px 0 6px">Use these questions to understand the candidate's experience and confirm the evidence shown.</p>
    <ol style="font-size:13px;margin:4px 0 0 18px">
      <li><strong>Strength to explore:</strong> Tell me about a time you used <?= esc($tm) ?> to solve a real problem — what was the outcome?</li>
      <?php if ($mm): ?>
        <li><strong>Skill to check:</strong> How would you get up to speed on <?= esc($mm) ?> in your first 30 days?</li>
      <?php else: ?>
        <li><strong>Suggested follow-up:</strong> What would you improve first in this role?</li>
      <?php endif; ?>
      <?php if (($match['evidence_strength_score'] ?? 100) < 60): ?>
        <li><strong>Evidence gap:</strong> Can you share a specific project outcome or metric that shows this in practice?</li>
      <?php endif; ?>
    </ol>
    <p class="purpose" style="margin-top:10px">Decision support only — the recruiter decides.</p>
  </div>
</section>
<?php endif; ?>

<section class="section">
  <div class="card card-tight" style="border-left:3px solid var(--gold)">
    <span class="muted" style="font-size:12px"><strong class="gold">Demo cohort data</strong> — no real student record is used. In production, student-level data is permission-based, anonymised for dashboards, and only authorised officers can view identifiable records (PDPA).</span>
  </div>
</section>
<?= $this->endSection() ?>
