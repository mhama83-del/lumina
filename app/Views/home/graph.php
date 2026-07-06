<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php helper('ui'); ?>
<section class="hero">
  <div class="section-label">Lumina Graph · self-growing taxonomy</div>
  <h1>A knowledge graph that learns.</h1>
  <p class="lead">Every resume and job description Lumina reads grows this graph — canonical skills, how they relate, and the profile patterns behind them. New skills it has never seen are added automatically.</p>
</section>

<section class="section">
  <div class="grid grid-3">
    <?= lumina_kpi(number_format($stats['skills']), 'Skills in the graph', 'canonical + learned') ?>
    <?= lumina_kpi(number_format($stats['patterns']), 'Profile patterns', 'domain × programme') ?>
    <?= lumina_kpi(number_format($stats['profiles_learned']), 'Profiles learned', 'and counting') ?>
  </div>
</section>

<?php if (!empty($newest)): ?>
<section class="section">
  <div class="card" style="border-left:3px solid var(--gold)">
    <div class="section-label">Most recently learned</div>
    <div style="margin-top:6px">
      <?php foreach ($newest as $s): ?><span class="skill inferred"><?= esc($s['label']) ?> <span class="conf">new</span></span> <?php endforeach; ?>
    </div>
    <p class="muted" style="font-size:12px;margin-top:8px">These were added as candidates were analysed. Paste a resume with a new tool at <a href="<?= base_url('resume') ?>" class="gold">/resume</a> and watch the graph grow.</p>
  </div>
</section>
<?php endif; ?>

<section class="section">
  <div class="grid grid-2">
    <div class="card">
      <div class="section-label">Top skills · and what they connect to</div>
      <div class="stack" style="margin-top:6px">
        <?php foreach ($topSkills as $s): $rel = json_decode($s['related_skills_json'] ?? '[]', true) ?: []; ?>
          <div class="card card-tight">
            <div class="row" style="justify-content:space-between">
              <strong><?= esc($s['label']) ?></strong>
              <span class="muted" style="font-size:12px"><?= (int)$s['frequency'] ?> uses</span>
            </div>
            <?php if ($rel): ?><div class="muted" style="font-size:12px;margin-top:4px">→ <?= esc(implode(' · ', array_map(fn($c)=>ucwords(str_replace('_',' ',$c)), array_slice($rel,0,5)))) ?></div><?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="card">
      <div class="section-label">Profile patterns · learned from the cohort</div>
      <div class="table-wrap"><table style="width:100%;border-collapse:collapse;font-size:13px;margin-top:6px">
        <thead><tr><th style="text-align:left;padding:8px;color:var(--muted)">Pattern</th><th style="padding:8px;color:var(--muted)">Profiles</th></tr></thead>
        <tbody>
          <?php foreach ($topPatterns as $p): $ts = json_decode($p['typical_skills_json'] ?? '[]', true) ?: []; ?>
            <tr>
              <td style="padding:8px">
                <strong><?= esc($p['pattern_key']) ?></strong>
                <div class="muted" style="font-size:12px"><?= esc(implode(', ', array_map(fn($c)=>ucwords(str_replace('_',' ',$c)), array_slice($ts,0,5)))) ?></div>
              </td>
              <td style="padding:8px;text-align:center"><?= (int)$p['sample_count'] ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table></div>
    </div>
  </div>
</section>

<section class="section">
  <div class="card">
    <p class="muted" style="font-size:13px">How it works: each new profile is matched to the nearest pattern (Jaccard skill overlap + domain/programme). Known skills strengthen the graph; unknown ones are added. Deterministic today — designed to swap in embeddings/NER later.</p>
    <div class="row" style="margin-top:10px">
      <a class="btn btn-gold" href="<?= base_url('resume') ?>">Grow the graph — analyse a resume →</a>
      <a class="btn btn-ghost" href="<?= base_url('how-it-works') ?>">System design</a>
    </div>
  </div>
</section>
<?= $this->endSection() ?>
