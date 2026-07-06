<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php helper('ui'); $pill = fn($v)=> $v>=75?'ok':($v>=50?'nudge':'risk');
  $lblCls = function($l){ $l=strtolower($l); return str_contains($l,'strong')?'ok':((str_contains($l,'good')||str_contains($l,'potential'))?'nudge':'risk'); };
?>
<section class="hero">
  <div class="section-label">Compare candidates</div>
  <h1>Side by side for <?= esc($role['role_title']) ?>.</h1>
  <p class="purpose"><?= esc($role['company_name']) ?> · <?= esc($role['target_domain']) ?>. Talent Match Signal (40/20/20/10/5/5) — <em>decision support only.</em></p>
  <div class="row" style="margin-top:12px"><a class="btn btn-ghost" href="<?= base_url('employer/role/'.(int)$role['id']) ?>">← Back to role</a></div>
</section>

<section class="section">
  <?php if (empty($cands)): ?>
    <div class="card"><p class="muted">Select 2–4 candidates from the role page to compare.</p></div>
  <?php else: ?>
  <div class="card" style="overflow-x:auto">
    <table style="width:100%;border-collapse:collapse;min-width:<?= 200 + count($cands)*190 ?>px">
      <thead><tr>
        <th style="text-align:left;padding:10px;min-width:170px"></th>
        <?php foreach ($cands as $c): ?>
          <th style="text-align:left;padding:10px;border-bottom:1px solid var(--line)">
            <strong><?= esc($c['name']) ?></strong>
            <div class="muted" style="font-size:12px"><?= esc($c['university']) ?></div>
            <div class="muted" style="font-size:12px"><?= esc($c['programme']) ?></div>
          </th>
        <?php endforeach; ?>
      </tr></thead>
      <tbody>
        <?php
        $rs='<tr><td style="padding:10px;color:var(--muted);font-size:13px;border-top:1px solid var(--line)">';
        $cs='<td style="padding:10px;border-top:1px solid var(--line)">';
        $rows = [
          'Talent Match'      => fn($c)=> '<span class="pill '.$lblCls($c['fit_label']).'">'.$c['match_score'].'%</span>',
          'Fit label'         => fn($c)=> '<span class="muted" style="font-size:12px">'.esc($c['fit_label']).'</span>',
          'Skill match'       => fn($c)=> '<span class="pill '.$pill($c['skill_match_score']).'">'.$c['skill_match_score'].'%</span>',
          'Evidence strength' => fn($c)=> '<span class="pill '.$pill($c['evidence_strength_score']).'">'.$c['evidence_strength_score'].'</span>',
          'Learning velocity' => fn($c)=> '<span class="pill '.$pill($c['learning_velocity_score']).'">'.$c['learning_velocity_score'].'</span>',
          'Work Animal'       => fn($c)=> '<span class="skill">'.esc($c['animal']).'</span>',
          'Animal fit'        => fn($c)=> '<span class="pill '.$pill($c['animal_fit_score']).'">'.$c['animal_fit_score'].'</span>',
          'Domain fit'        => fn($c)=> '<span class="pill '.$pill($c['domain_fit_score']).'">'.$c['domain_fit_score'].'</span>',
          'Academic fit'      => fn($c)=> '<span class="pill '.$pill($c['academic_fit_score']).'">'.$c['academic_fit_score'].'</span>',
        ];
        foreach ($rows as $label=>$fn): ?>
          <?= $rs.$label ?></td><?php foreach ($cands as $c): ?><?= $cs.$fn($c) ?></td><?php endforeach; ?></tr>
        <?php endforeach; ?>
        <?= $rs ?>Missing skills</td>
        <?php foreach ($cands as $c): ?><?= $cs ?><?php if($c['missing_skills']): foreach(array_slice($c['missing_skills'],0,4) as $g): ?><span class="skill inferred" style="font-size:11px"><?= esc($g) ?></span> <?php endforeach; else: ?><span class="muted" style="font-size:12px">none</span><?php endif; ?></td><?php endforeach; ?></tr>
        <?= $rs ?>Why</td>
        <?php foreach ($cands as $c): ?><?= $cs ?><span class="muted" style="font-size:12px"><?= esc($c['explanation']) ?></span></td><?php endforeach; ?></tr>
      </tbody>
    </table>
    <p class="purpose" style="margin-top:12px">Every number traces to skills, evidence, trajectory, animal fit, domain and CGPA — no black box.</p>
  </div>
  <?php endif; ?>
</section>
<?= $this->endSection() ?>
