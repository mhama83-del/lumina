<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php helper('ui'); ?>

<section class="hero">
  <div class="section-label">For Employers</div>
  <h1>Hire on fit, not on guesswork.</h1>
  <p class="purpose">Pick a role — Lumina ranks candidates with the reasons why. <em>Decision support only — the recruiter decides.</em></p>
</section>

<!-- KPI row -->
<section class="section">
  <div class="grid grid-4">
    <?= lumina_kpi(count($ranked), 'Candidates in pipeline') ?>
    <?= lumina_kpi('9', 'Interviews this week') ?>
    <?= lumina_kpi('21d', 'Avg time-to-hire') ?>
    <?= lumina_kpi('86%', 'Offer acceptance') ?>
  </div>
</section>

<!-- Role selector + ranked list -->
<section class="section">
  <div class="card">
    <div class="row" style="justify-content:space-between;flex-wrap:wrap;gap:10px">
      <div>
        <div class="section-label">Ranked candidates</div>
        <h3 style="margin:0"><?= esc($selected['title']) ?> · <?= esc($selected['company']) ?></h3>
      </div>
      <form method="get" action="<?= base_url('employer') ?>">
        <select class="stage-select" name="role" onchange="this.form.submit()">
          <?php foreach ($roles as $r): ?>
            <option value="<?= esc($r['key']) ?>" <?= $r['key'] === $selected['key'] ? 'selected' : '' ?>>
              <?= esc($r['title']) ?> — <?= esc($r['company']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </form>
    </div>

    <div class="stack" style="margin-top:14px">
      <?php foreach ($ranked as $i => $c):
        $qs = '<ol style="margin:6px 0 0 18px;padding:0">';
        foreach ($c['questions'] as $q) { $qs .= '<li style="margin:4px 0">' . esc($q) . '</li>'; }
        $qs .= '</ol>';
        $body = '<p class="muted">' . esc($c['reason']) . '</p>'
              . '<div class="section-label" style="margin-top:12px">Evidence</div><p>' . esc($c['evidence']) . '</p>'
              . '<div class="section-label" style="margin-top:10px">Suggested interview questions</div>' . $qs
              . '<p class="purpose" style="margin-top:12px">Decision support only — the recruiter decides.</p>';
      ?>
        <div class="card card-tight" style="display:flex;align-items:center;gap:14px">
          <div class="ring <?= $i === 0 ? 'gold' : '' ?>"><?= (int)$c['match'] ?></div>
          <div style="flex:1;min-width:0">
            <strong><?= esc($c['name']) ?></strong>
            <span class="pill <?= $c['label']==='best'?'ok':($c['label']==='growth'?'nudge':'risk') ?>" style="margin-left:6px"><?= esc(ucfirst($c['label'])) ?></span>
            <div class="muted" style="font-size:13px"><?= esc($c['university']) ?> · <?= esc($c['programme']) ?>
              <?php if ($c['gap']): ?> · gap: <?= esc(implode(', ', $c['gap'])) ?><?php endif; ?>
            </div>
          </div>
          <button class="btn btn-ghost" data-drawer="1"
            data-title="<?= esc($c['name'] . ' — why this candidate', 'attr') ?>"
            data-body="<?= esc($body, 'attr') ?>">Why this candidate?</button>
        </div>
      <?php endforeach; ?>
      <?php if (empty($ranked)): ?>
        <p class="muted">No candidates found. Import seed data (database/seed_sample.sql).</p>
      <?php endif; ?>
    </div>
  </div>
</section>
<?= $this->endSection() ?>
