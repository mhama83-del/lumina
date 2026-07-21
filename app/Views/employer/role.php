<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
helper('ui');
$shortlist = $shortlist ?? [];
$progs = json_decode($role['suitable_programmes_json'] ?? '[]', true) ?: [];
$resp  = json_decode($role['responsibilities_json'] ?? '[]', true) ?: [];
$ev    = json_decode($role['evidence_required_json'] ?? '[]', true) ?: [];
$req = array_filter($role['skills'] ?? [], fn($s)=>$s['importance']==='required');
$pref= array_filter($role['skills'] ?? [], fn($s)=>$s['importance']==='preferred');
$soft= array_filter($role['skills'] ?? [], fn($s)=>$s['skill_category']==='Soft Skill');
$af  = $role['animal'] ?? [];
$accept = json_decode($af['acceptable_animals_json'] ?? '[]', true) ?: [];
$pillFor = fn($v)=> $v>=75?'ok':($v>=50?'nudge':'risk');
?>
<section class="hero">
  <div class="section-label"><?= esc($role['company_name']) ?> · <?= esc($role['emp_country']) ?> · <?= esc($role['sector']) ?></div>
  <h1><?= esc($role['role_title']) ?></h1>
  <p class="purpose"><?= esc($role['role_summary']) ?></p>
  <div class="row" style="margin-top:10px">
    <span class="pill nudge"><?= esc($role['target_domain']) ?></span>
    <span class="skill"><?= esc($role['role_level']) ?></span>
    <span class="skill"><?= esc($role['work_arrangement']) ?></span>
    <span class="skill"><?= esc($role['salary_band']) ?></span>
    <span class="skill">Velocity: <?= esc($role['learning_velocity_need']) ?></span>
  </div>
  <div style="margin-top:10px"><a class="btn btn-ghost" href="<?= base_url('employer') ?>">← Back to roles</a></div>
</section>

<section class="section">
  <div class="grid grid-2">
    <!-- JD detail -->
    <div class="card">
      <div class="section-label">The role</div>
      <ul class="muted" style="font-size:14px;padding-left:18px;margin:0 0 12px">
        <?php foreach ($resp as $x): ?><li><?= esc($x) ?></li><?php endforeach; ?>
      </ul>
      <div class="section-label">Required skills</div>
      <div style="margin-bottom:10px"><?php foreach ($req as $s): ?><span class="skill"><?= esc($s['skill_name']) ?></span> <?php endforeach; ?></div>
      <div class="section-label">Preferred</div>
      <div style="margin-bottom:10px"><?php foreach ($pref as $s): ?><span class="skill inferred"><?= esc($s['skill_name']) ?></span> <?php endforeach; ?></div>
      <div class="section-label">Suitable programmes</div>
      <p class="muted" style="font-size:13px"><?= esc(implode(', ', $progs)) ?></p>
      <div class="section-label" style="margin-top:8px">Evidence Lumina looks for</div>
      <ul class="muted" style="font-size:13px;padding-left:18px;margin:0"><?php foreach ($ev as $x): ?><li><?= esc($x) ?></li><?php endforeach; ?></ul>
    </div>

    <!-- Role Context Card (Fasa 4) -->
    <div class="card">
      <div class="section-label">Role context</div>
      <p class="muted" style="font-size:12px;margin:2px 0 10px">Describes what the job demands — not the candidate's personality.</p>

      <div style="font-size:11px;font-weight:700;color:var(--indigo);letter-spacing:.04em;margin-bottom:3px">ROLE PURPOSE</div>
      <p style="font-size:14px;margin:0 0 12px"><?= esc($roleContext['purpose'] ?? $role['synthetic_jd_text']) ?></p>

      <?php if (!empty($roleContext['core_skills'])): ?>
      <div style="font-size:11px;font-weight:700;color:var(--indigo);letter-spacing:.04em;margin-bottom:4px">CORE SKILLS</div>
      <div style="margin-bottom:12px">
        <?php foreach ($roleContext['core_skills'] as $ck): ?><span class="skill"><?= esc(ucwords(str_replace(['_','-'],' ',$ck))) ?></span><?php endforeach; ?>
      </div>
      <?php endif; ?>

      <?php if (!empty($roleContext['edge_relevance'])): ?>
      <div style="font-size:11px;font-weight:700;color:var(--indigo);letter-spacing:.04em;margin-bottom:4px">RELEVANT EDGE SIGNALS <span style="font-weight:400;color:var(--muted)">· how this role tends to work</span></div>
      <div style="margin-bottom:12px">
        <?php foreach ($roleContext['edge_relevance'] as $er): ?><span class="skill" style="border-color:rgba(108,92,231,.4)"><?= esc($er) ?></span><?php endforeach; ?>
      </div>
      <?php endif; ?>

      <?php if (!empty($roleContext['work_context'])): ?>
      <div style="font-size:11px;font-weight:700;color:var(--indigo);letter-spacing:.04em;margin-bottom:4px">CRITICAL WORK CONTEXT</div>
      <ul style="margin:0 0 12px;padding-left:18px">
        <?php foreach (array_slice($roleContext['work_context'],0,4) as $wc): ?><li class="muted" style="font-size:13px;margin-bottom:3px"><?= esc($wc) ?></li><?php endforeach; ?>
      </ul>
      <?php endif; ?>

      <?php if (!empty($roleContext['evidence_ex'])): ?>
      <div style="font-size:11px;font-weight:700;color:var(--indigo);letter-spacing:.04em;margin-bottom:4px">EVIDENCE EXAMPLES <span style="font-weight:400;color:var(--muted)">· what a candidate could show</span></div>
      <ul style="margin:0 0 12px;padding-left:18px">
        <?php foreach ($roleContext['evidence_ex'] as $ee): ?><li class="muted" style="font-size:13px;margin-bottom:3px"><?= esc($ee) ?></li><?php endforeach; ?>
      </ul>
      <?php endif; ?>

      <div class="section-label" style="margin-top:6px">Team & fit notes</div>
      <p class="muted" style="font-size:13px"><strong>Team fit:</strong> <?= esc(preg_replace('/\s*\((?:Lion|Eagle|Wolf|Owl|Dolphin|Peacock|Elephant|Horse|Ant|Cheetah|Fox|Octopus)\)/i', '', $af['team_fit_note'] ?? '')) ?></p>
      <p class="muted" style="font-size:13px"><strong>Consideration:</strong> <?= esc(preg_replace('/\s*\((?:Lion|Eagle|Wolf|Owl|Dolphin|Peacock|Elephant|Horse|Ant|Cheetah|Fox|Octopus)\)/i', '', $af['poor_fit_risk'] ?? '')) ?></p>

      <div class="section-label" style="margin-top:10px">Synthetic JD</div>
      <p class="muted" style="font-size:13px"><?= esc($role['synthetic_jd_text']) ?></p>
      <p class="purpose" style="font-size:12px;margin-top:8px">Source: <?= esc($role['source_reference']) ?> · synthetic listing · reviewed <?= date('M Y') ?>.</p>
    </div>
  </div>
</section>

<section class="section">
  <div class="card">
    <div class="section-label">Ranked candidates · Talent Match Signal</div>
    <div style="display:flex;flex-wrap:wrap;gap:6px;margin:2px 0 8px">
      <span class="skill" style="font-size:11px">Skill 40%</span>
      <span class="skill" style="font-size:11px">Evidence 30%</span>
      <span class="skill" style="font-size:11px">Learning velocity 20%</span>
      <span class="skill" style="font-size:11px">Domain 5%</span>
      <span class="skill" style="font-size:11px">CGPA 5%</span>
    </div>
    <p class="muted" style="font-size:12px;margin:0 0 12px">Each candidate's <strong>“Why?”</strong> shows this exact breakdown. Bands: 85+ Strong · 70–84 Good · 55–69 Emerging · 40–54 Developing · below 40 Early-stage.</p>
    <form method="get" action="<?= base_url('employer/compare') ?>" id="cmpForm">
      <input type="hidden" name="role_id" value="<?= (int)$role['id'] ?>">
      <div class="row" style="justify-content:space-between;align-items:center;margin:8px 0;flex-wrap:wrap;gap:8px">
        <span class="muted" style="font-size:13px">Tick 2–4 candidates, then compare.</span>
        <button class="btn btn-gold" type="submit" id="cmpBtn" disabled>Compare selected (<span id="cmpN">0</span>) →</button>
      </div>
      <div style="overflow-x:auto">
      <table style="width:100%;border-collapse:collapse;font-size:13px;min-width:560px">
        <thead><tr style="border-bottom:2px solid var(--line)">
          <th style="padding:6px 6px 6px 0"></th>
          <th style="padding:6px 6px;text-align:left;color:var(--muted);font-size:11px;font-weight:700">MATCH</th>
          <th style="padding:6px 6px;text-align:left;color:var(--muted);font-size:11px;font-weight:700">CANDIDATE</th>
          <th style="padding:6px 4px;text-align:center;color:var(--muted);font-size:11px;font-weight:700" title="Skill">SKL</th>
          <th style="padding:6px 4px;text-align:center;color:var(--muted);font-size:11px;font-weight:700" title="Evidence">EVD</th>
          <th style="padding:6px 4px;text-align:center;color:var(--muted);font-size:11px;font-weight:700" title="Velocity">VEL</th>
          <th style="padding:6px 6px;text-align:left;color:var(--muted);font-size:11px;font-weight:700">TOP GAP</th>
          <th style="padding:6px 0;text-align:right;color:var(--muted);font-size:11px;font-weight:700"></th>
        </tr></thead>
        <tbody>
        <?php foreach ($ranked as $i => $c):
          $lab = strtolower($c['fit_label']); $cls = str_contains($lab,'strong')?'ok':(str_contains($lab,'good')||str_contains($lab,'potential')?'nudge':'risk');
          $wrows = [
            ['Skill', (int)$c['skill_match_score'], 40],
            ['Evidence', (int)$c['evidence_strength_score'], 30],
            ['Learning velocity', (int)$c['learning_velocity_score'], 20],
            ['Domain', (int)$c['domain_fit_score'], 5],
            ['CGPA', (int)$c['academic_fit_score'], 5],
          ];
          $rowsHtml = '';
          foreach ($wrows as $wr) {
            $pts = (int) round($wr[1] * $wr[2] / 100);
            $rowsHtml .= '<tr><td style="padding:3px 10px 3px 0;color:var(--muted)">'.$wr[0].'</td>'
                       . '<td style="padding:3px 8px;text-align:right">'.$wr[1].'</td>'
                       . '<td style="padding:3px 8px;color:var(--muted)">&times;'.$wr[2].'%</td>'
                       . '<td style="padding:3px 0;text-align:right"><strong>'.$pts.'</strong></td></tr>';
          }
          $tm = $c['skill_overlap'][0] ?? $role['target_domain'];
          $mm = $c['missing_skills'][0] ?? null;
          $iq1 = 'Tell me about a time you used ' . $tm . ' to solve a real problem — what was the outcome?';
          $iq2 = $mm ? ('How would you get up to speed on ' . $mm . ' in your first 30 days?') : 'What would you improve first in this role?';
          $body = '<div style="font-size:16px;margin-bottom:8px"><strong>Talent Match '.(int)$c['match_score'].'%</strong> · '.esc($c['fit_label']).'</div>'
                . '<div class="section-label">How this score is built</div>'
                . '<table style="width:100%;border-collapse:collapse;font-size:13px;margin:4px 0 4px"><tbody>'.$rowsHtml
                . '<tr><td style="padding:6px 10px 0 0;border-top:1px solid var(--line)"><strong>Talent Match</strong></td><td style="border-top:1px solid var(--line)"></td><td style="border-top:1px solid var(--line)"></td><td style="padding:6px 0 0;text-align:right;border-top:1px solid var(--line)"><strong>'.(int)$c['match_score'].'</strong></td></tr>'
                . '</tbody></table>'
                . '<p class="muted" style="font-size:12px">Points = score &times; weight; they add up to the Talent Match. Every score is 0–100.</p>'
                . '<div class="section-label" style="margin-top:10px">Skill overlap</div><p style="font-size:13px">'.(esc(implode(', ', $c['skill_overlap'])) ?: '—').'</p>'
                . '<div class="section-label" style="margin-top:8px">Missing / to develop</div><p style="font-size:13px">'.(esc(implode(', ', $c['missing_skills'])) ?: 'none').'</p>'
                . '<div class="section-label" style="margin-top:8px">Suggested interview questions</div><ol style="font-size:13px;margin:4px 0 0 18px"><li>'.esc($iq1).'</li><li>'.esc($iq2).'</li></ol>'
                . '<p class="purpose" style="margin-top:10px">Decision support only — the recruiter decides.</p>';
          $isShort = in_array($c['id'], $shortlist, true);
        ?>
          <tr style="border-bottom:1px solid var(--line)">
            <td style="padding:8px 6px 8px 0;vertical-align:middle"><input type="checkbox" class="cmpChk" name="ids[]" value="<?= (int)$c['id'] ?>" style="width:17px;height:17px"></td>
            <td style="padding:8px 6px;vertical-align:middle"><div class="ring <?= $i===0?'gold':'' ?>" style="width:38px;height:38px;font-size:14px"><?= (int)$c['match_score'] ?></div></td>
            <td style="padding:8px 6px;vertical-align:middle;min-width:130px">
              <strong style="font-size:13px"><?= esc($c['name']) ?></strong>
              <span class="pill <?= $cls ?>" style="margin-left:4px;font-size:10px"><?= esc($c['fit_label']) ?></span>
              <div class="muted" style="font-size:11px"><?= esc($c['programme']) ?></div>
            </td>
            <?php
              $bars = [
                ['S', (int)$c['skill_match_score'], 'var(--indigo)'],
                ['E', (int)$c['evidence_strength_score'], 'var(--teal)'],
                ['V', (int)$c['learning_velocity_score'], '#38BDF8'],
              ];
              foreach ($bars as [$bl,$bv,$bc]):
            ?>
            <td style="padding:8px 4px;vertical-align:middle;text-align:center" title="<?= $bl==='S'?'Skill':($bl==='E'?'Evidence':'Velocity') ?>: <?= $bv ?>">
              <div style="width:40px;height:6px;background:rgba(255,255,255,.06);border-radius:4px;overflow:hidden;margin:0 auto 2px"><div style="height:100%;width:<?= max(3,min(100,$bv)) ?>%;background:<?= $bc ?>"></div></div>
              <span style="font-size:10px;color:var(--muted)"><?= $bv ?></span>
            </td>
            <?php endforeach; ?>
            <td style="padding:8px 6px;vertical-align:middle;font-size:11px;color:var(--muted);max-width:110px"><?= $c['missing_skills'] ? esc($c['missing_skills'][0]) : '—' ?></td>
            <td style="padding:8px 0 8px 6px;vertical-align:middle;white-space:nowrap">
              <button class="btn btn-ghost" type="button" data-drawer="1" style="padding:3px 8px;font-size:12px" data-title="<?= esc($c['name'].' — why', 'attr') ?>" data-body="<?= esc($body,'attr') ?>">Why?</button>
              <a class="btn btn-ghost" href="<?= base_url('employer/candidate/'.(int)$c['id'].'?role_id='.(int)$role['id']) ?>" style="padding:3px 8px;font-size:12px">Profile</a>
              <a class="btn <?= $isShort?'btn-gold':'btn-ghost' ?>" href="<?= base_url('employer/shortlist?id='.(int)$c['id'].'&role_id='.(int)$role['id']) ?>" style="padding:3px 7px;font-size:12px"><?= $isShort?'★':'☆' ?></a>
            </td>
          </tr>

        <?php endforeach; ?>
        </tbody>
      </table>
      </div>
    </form>
  </div>
</section>

<script>
(function(){
  var chks=document.querySelectorAll('.cmpChk'), btn=document.getElementById('cmpBtn'), n=document.getElementById('cmpN');
  function upd(){ var sel=[].filter.call(chks,function(c){return c.checked;}); n.textContent=sel.length; btn.disabled=(sel.length<2||sel.length>4);
    if(sel.length>=4){[].forEach.call(chks,function(c){if(!c.checked)c.disabled=true;});} else {[].forEach.call(chks,function(c){c.disabled=false;});} }
  [].forEach.call(chks,function(c){c.addEventListener('change',upd);});
})();
</script>
<?= $this->endSection() ?>
