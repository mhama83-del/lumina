<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php helper('ui'); ?>

<section class="hero">
  <div class="section-label">Starter Living Portfolio · No-Resume</div>
  <h1>Here's your starting point.</h1>
  <p class="purpose">Built from your answers — a readiness baseline, your Work Animal, a resume draft, and the next steps that move your number.</p>
</section>

<section class="section">
  <div class="grid grid-2">
    <!-- Layer 1: readiness + animal -->
    <div class="card">
      <div class="section-label">Career readiness baseline</div>
      <?= lumina_donut($readiness['score'], 'Readiness · ' . esc($bestRole['title']), $bestRole['color'] ?? 'var(--indigo)') ?>
      <div style="text-align:center;margin-top:8px">
        <?php $bc = $band==='On track'?'ok':($band==='Needs a nudge'?'nudge':'risk'); ?>
        <span class="pill <?= $bc ?>"><?= esc($band) ?></span>
        <span class="muted" style="font-size:12px">employability band</span>
      </div>
      <p class="muted" style="text-align:center;margin-top:8px">Career cluster: <strong class="gold"><?= esc($cluster) ?></strong></p>

      <div class="section-label" style="margin-top:16px">Suggested Work Animal · from your answers</div>
      <div class="card card-tight">
        <div style="margin-bottom:8px">
          <span class="skill"><?= esc($animal['primary']['label']) ?> <span class="conf">primary</span></span>
          <span class="skill"><?= esc($animal['secondary']['label']) ?> <span class="conf">secondary</span></span>
          <span class="skill"><?= esc($animal['growth']['label']) ?> <span class="conf">growth</span></span>
        </div>
        <p class="muted" style="font-size:13px"><?= esc($animal['line']) ?> <span class="gold">Confidence <?= (int)$animal['confidence'] ?>%</span></p>
        <p class="muted" style="font-size:12px;margin-top:6px">Traits: <?= esc(implode(' · ', $animal['primary']['traits'] ?? [])) ?></p>
        <?php if (!empty($animal['growthAdvice'])): ?><p class="muted" style="font-size:12px;margin-top:4px">Growth (<?= esc($animal['growth']['label'] ?? '') ?>): <?= esc($animal['growthAdvice']) ?></p><?php endif; ?>
      </div>
    </div>

    <!-- Layer 2: potential skills + resume draft -->
    <div class="card">
      <div class="section-label">Potential skills detected</div>
      <div style="margin-bottom:6px">
        <?php foreach ($skills as $s): ?>
          <?= lumina_skill($s['label'], $s['source'], ($s['conf'] ?? 100) / 100) ?>
        <?php endforeach; ?>
        <?php if (empty($skills)): ?><span class="muted">Add a few more details to surface skills.</span><?php endif; ?>
      </div>
      <?php if (!empty($gapLabels)): ?>
        <p class="muted" style="font-size:13px">To strengthen your best-fit role (<strong><?= esc($bestRole['title']) ?></strong>), grow: <strong style="color:var(--gold)"><?= esc(implode(', ', $gapLabels)) ?></strong>.</p>
      <?php endif; ?>

      <div class="section-label" style="margin-top:14px">Suggested resume draft</div>
      <textarea readonly style="min-height:220px;font-family:ui-monospace,Menlo,Consolas,monospace;font-size:12.5px"><?= esc($resumeDraft) ?></textarea>
      <p class="muted" style="font-size:12px;margin-top:6px">Copy this, refine it, then run it through Resume Analysis for a full score.</p>
    </div>
  </div>
</section>

<section class="section">
  <div class="grid grid-3">
    <div class="card">
      <div class="section-label">Your first portfolio project</div>
      <p><?= esc($firstProject) ?></p>
    </div>
    <div class="card">
      <div class="section-label">Recommended activities</div>
      <ul class="muted" style="font-size:13px;padding-left:18px;margin:0">
        <?php foreach ($activitiesRec as $a): ?><li><?= esc($a) ?></li><?php endforeach; ?>
      </ul>
    </div>
    <div class="card">
      <div class="section-label">Micro-courses to close gaps</div>
      <?php if (empty($courses)): ?>
        <p class="muted" style="font-size:13px">No major gaps — keep building evidence.</p>
      <?php else: ?>
        <div class="muted" style="font-size:13px">
          <?php foreach ($courses as $c): ?><div>• <strong><?= esc($c['skill']) ?></strong> — <?= esc($c['course']) ?></div><?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="section">
  <div class="card">
    <?php if (!empty($graphNew)): ?><?= lumina_note('New skills learned into the graph: ' . esc(implode(', ', $graphNew))) ?><?php endif; ?>
    <div class="section-label">Related skills · from the Lumina Graph</div>
    <div style="margin-bottom:6px">
      <?php foreach (($graphRelated ?? []) as $g): ?><span class="skill inferred"><?= esc($g['label']) ?> <span class="conf">from graph</span></span> <?php endforeach; ?>
      <?php if (empty($graphRelated)): ?><span class="muted">No adjacent skills surfaced yet.</span><?php endif; ?>
    </div>
    <p class="muted" style="font-size:12px;margin:0">Based on similar profiles, people like you often also develop these — worth adding to your plan.<?php if (!empty($graphStats)): ?> Lumina Graph now knows <strong><?= (int)$graphStats['skills'] ?></strong> skills · <strong><?= (int)$graphStats['patterns'] ?></strong> patterns · <strong><?= (int)$graphStats['profiles_learned'] ?></strong> profiles — and it grew from your entry.<?php endif; ?></p>
  </div>
</section>

<section class="section">
  <div class="card">
    <?= lumina_note('Next best action: ' . esc($nextAction)) ?>
    <div class="row">
      <a class="btn btn-primary" href="<?= base_url('passport') ?>">Open my Living Portfolio →</a>
      <a class="btn btn-ghost" href="<?= base_url('compass') ?>">See career paths</a>
      <a class="btn btn-ghost" href="<?= base_url('resume') ?>">Refine as a resume</a>
    </div>
  </div>
</section>
<?= $this->endSection() ?>
