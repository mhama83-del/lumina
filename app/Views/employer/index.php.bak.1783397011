<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php helper('ui'); $shortlist = $shortlist ?? []; $pages = (int) ceil($total / $perPage);
  $qs = function ($over = []) use ($filters, $page) {
      $p = array_merge(['domain'=>$filters['domain'],'level'=>$filters['level'],'country'=>$filters['country'],'sector'=>$filters['sector'],'q'=>$filters['q'],'page'=>$page], $over);
      return http_build_query(array_filter($p, fn($v)=>$v!=='' && $v!==null));
  };
?>
<section class="hero">
  <div class="section-label">For Employers</div>
  <h1>Browse 1,000 roles. Hire on fit, not guesswork.</h1>
  <p class="purpose">Filter the live JD database, open a role to rank candidates with explainable scores. <em>Decision support only — the recruiter decides.</em></p>
</section>

<section class="section">
  <div class="grid grid-4">
    <?= lumina_kpi(number_format($total), 'Roles matching filters') ?>
    <?= lumina_kpi('1,000', 'Total JD in database') ?>
    <?= lumina_kpi((string) count($shortlist), 'Shortlisted') ?>
    <?= lumina_kpi('40/20/20/10/5/5', 'Talent Match Signal') ?>
  </div>
</section>

<section class="section">
  <div class="card">
    <form method="get" action="<?= base_url('employer') ?>">
      <div class="grid grid-4" style="gap:10px">
        <div>
          <label class="fl">Domain</label>
          <select class="field" name="domain" onchange="this.form.submit()">
            <option value="">All domains</option>
            <?php foreach ($options['domains'] as $d): ?><option value="<?= $d ?>" <?= $filters['domain']===$d?'selected':'' ?>><?= $d ?></option><?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="fl">Role level</label>
          <select class="field" name="level" onchange="this.form.submit()">
            <option value="">All levels</option>
            <?php foreach ($options['levels'] as $d): ?><option value="<?= $d ?>" <?= $filters['level']===$d?'selected':'' ?>><?= $d ?></option><?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="fl">Country</label>
          <select class="field" name="country" onchange="this.form.submit()">
            <option value="">All countries</option>
            <?php foreach ($options['countries'] as $d): ?><option value="<?= esc($d,'attr') ?>" <?= $filters['country']===$d?'selected':'' ?>><?= esc($d) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="fl">Sector</label>
          <select class="field" name="sector" onchange="this.form.submit()">
            <option value="">All sectors</option>
            <?php foreach ($options['sectors'] as $d): ?><option value="<?= esc($d,'attr') ?>" <?= $filters['sector']===$d?'selected':'' ?>><?= esc($d) ?></option><?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="row" style="margin-top:10px;gap:8px">
        <input class="field" type="text" name="q" value="<?= esc($filters['q'],'attr') ?>" placeholder="Search role or company…" style="flex:1">
        <button class="btn btn-gold" type="submit">Search</button>
        <a class="btn btn-ghost" href="<?= base_url('employer') ?>">Reset</a>
      </div>
    </form>
  </div>
</section>

<section class="section">
  <div class="section-label"><?= number_format($total) ?> roles · page <?= $page ?> of <?= max(1,$pages) ?></div>
  <div class="grid grid-3">
    <?php foreach ($roles as $r):
      $col = ['Data'=>'var(--indigo)','Engineering'=>'var(--teal)','Design'=>'var(--violet)','Business'=>'var(--gold)'][$r['target_domain']] ?? 'var(--indigo)'; ?>
      <a class="card" href="<?= base_url('employer/role/' . $r['id']) ?>" style="text-decoration:none;border-left:3px solid <?= $col ?>">
        <div class="section-label"><?= esc($r['company_name']) ?> · <?= esc($r['country']) ?></div>
        <h3 style="margin:2px 0"><?= esc($r['role_title']) ?></h3>
        <div class="muted" style="font-size:13px"><?= esc($r['sector']) ?> · <?= esc($r['role_level']) ?></div>
        <div style="margin-top:8px">
          <span class="pill <?= $r['target_domain']==='Data'?'ok':($r['target_domain']==='Business'?'nudge':'') ?>"><?= esc($r['target_domain']) ?></span>
          <span class="skill" style="font-size:11px"><?= esc($r['work_arrangement']) ?></span>
          <span class="skill" style="font-size:11px"><?= esc($r['salary_band']) ?></span>
        </div>
      </a>
    <?php endforeach; ?>
    <?php if (empty($roles)): ?>
      <p class="muted">No roles match these filters. <a href="<?= base_url('employer') ?>" class="gold">Reset</a>.</p>
    <?php endif; ?>
  </div>

  <?php if ($pages > 1): ?>
  <div class="row" style="justify-content:center;gap:10px;margin-top:16px">
    <?php if ($page > 1): ?><a class="btn btn-ghost" href="?<?= $qs(['page'=>$page-1]) ?>">← Prev</a><?php endif; ?>
    <span class="muted" style="align-self:center">Page <?= $page ?> / <?= $pages ?></span>
    <?php if ($page < $pages): ?><a class="btn btn-ghost" href="?<?= $qs(['page'=>$page+1]) ?>">Next →</a><?php endif; ?>
  </div>
  <?php endif; ?>
</section>
<?= $this->endSection() ?>
