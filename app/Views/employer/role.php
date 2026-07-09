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

    <!-- Animal fit + JD text -->
    <div class="card">
      <div class="section-label">Work Animal fit</div>
      <div style="margin-bottom:8px">
        <span class="skill"><?= esc($af['preferred_primary_animal'] ?? '—') ?> <span class="conf">primary</span></span>
        <span class="skill"><?= esc($af['preferred_secondary_animal'] ?? '—') ?> <span class="conf">secondary</span></span>
        <?php foreach ($accept as $a): ?><span class="skill inferred"><?= esc($a) ?></span> <?php endforeach; ?>
      </div>
      <p class="muted" style="font-size:13px"><strong>Team fit:</strong> <?= esc($af['team_fit_note'] ?? '') ?></p>
      <p class="muted" style="font-size:13px"><strong>Consideration:</strong> <?= esc($af['poor_fit_risk'] ?? '') ?></p>
      <div class="section-label" style="margin-top:10px">Synthetic JD</div>
      <p class="muted" style="font-size:13px"><?= esc($role['synthetic_jd_text']) ?></p>
      <p class="purpose" style="font-size:12px;margin-top:8px">Source: <?= esc($role['source_reference']) ?> · synthetic listing.</p>
    </div>
  </div>
</section>

<section class="section">
  <div class="card">
    <div class="section-label">Ranked candidates · Talent Match Signal</div>
    <div style="display:flex;flex-wrap:wrap;gap:6px;margin:2px 0 8px">
      <span class="skill" style="font-size:11px">Skill 40%</span>
      <span class="skill" style="font-size:11px">Evidence 20%</span>
      <span class="skill" style="font-size:11px">Learning velocity 20%</span>
      <span class="skill" style="font-size:11px">Work-Animal fit 10%</span>
      <span class="skill" style="font-size:11px">Domain 5%</span>
      <span class="skill" style="font-size:11px">CGPA 5%</span>
    </div>
    <p class="muted" style="font-size:12px;margin:0 0 12px">Each candidate's <strong>“Why?”</strong> shows this exact breakdown. Bands: 85+ Strong · 70–84 Good · 55–69 Potential · 40–54 Needs Development · below 40 Weak.</p>
    <form method="get" action="<?= base_url('employer/compare') ?>" id="cmpForm">
      <input type="hidden" name="role_id" value="<?= (int)$role['id'] ?>">
      <div class="row" style="justify-content:space-between;align-items:center;margin:8px 0;flex-wrap:wrap;gap:8px">
        <span class="muted" style="font-size:13px">Tick 2–4 candidates, then compare.</span>
        <button class="btn btn-gold" type="submit" id="cmpBtn" disabled>Compare selected (<span id="cmpN">0</span>) →</button>
      </div>
      <div class="stack">
        <?php foreach ($ranked as $i => $c):
          $lab = strtolower($c['fit_label']); $cls = str_contains($lab,'strong')?'ok':(str_contains($lab,'good')||str_contains($lab,'potential')?'nudge':'risk');
          $wrows = [
            ['Skill', (int)$c['skill_match_score'], 40],
            ['Evidence', (int)$c['evidence_strength_score'], 20],
            ['Learning velocity', (int)$c['learning_velocity_score'], 20],
            ['Work-Animal fit', (int)$c['animal_fit_score'], 10],
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
          <div class="card card-tight" style="display:flex;align-items:center;gap:12px">
            <input type="checkbox" class="cmpChk" name="ids[]" value="<?= (int)$c['id'] ?>" style="width:18px;height:18px;flex:0 0 auto">
            <div class="ring <?= $i===0?'gold':'' ?>"><?= (int)$c['match_score'] ?></div>
            <div style="flex:1;min-width:0">
              <strong><?= esc($c['name']) ?></strong>
              <span class="pill <?= $cls ?>" style="margin-left:6px"><?= esc($c['fit_label']) ?></span>
              <div class="muted" style="font-size:13px"><?= esc($c['university']) ?> · <?= esc($c['programme']) ?> · <?= esc($c['animal']) ?>
                <?php if ($c['missing_skills']): ?> · gap: <?= esc(implode(', ', array_slice($c['missing_skills'],0,3))) ?><?php endif; ?>
              </div>
            </div>
            <a class="btn btn-ghost" href="<?= base_url('employer/candidate/'.(int)$c['id'].'?role_id='.(int)$role['id']) ?>">Profile</a>
            <button class="btn btn-ghost" type="button" data-drawer="1" data-title="<?= esc($c['name'].' — why', 'attr') ?>" data-body="<?= esc($body,'attr') ?>">Why?</button>
            <a class="btn <?= $isShort?'btn-gold':'btn-ghost' ?>" href="<?= base_url('employer/shortlist?id='.(int)$c['id'].'&role_id='.(int)$role['id']) ?>"><?= $isShort?'★':'☆' ?></a>
          </div>
        <?php endforeach; ?>
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
