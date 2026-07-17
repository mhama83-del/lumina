<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
helper('ui');
$fitClass = ['Best fit' => 'ok', 'Growth fit' => 'nudge', 'Stretch fit' => 'risk'];
?>
<section class="hero">
  <div class="section-label">Smart Matching · <?= esc($profile['name'] ?? 'You') ?></div>
  <h1>Opportunities that fit where you're going.</h1>
  <p class="purpose">Matched by readiness and trajectory — not just keywords. <em>Example roles inspired by market patterns — synthetic demo data, not live postings.</em></p>
</section>
<section class="section" style="padding-top:6px">
  <?= lumina_journey('match') ?>
  <?= lumina_note("Matched to opportunities by readiness and trajectory — not keywords.") ?>
</section>
<section class="section">
  <div class="grid grid-3">
    <?php foreach ($opps as $o):
      $body = '<p class="muted">' . esc($o['reason']) . '</p>'
            . '<div class="section-label" style="margin-top:12px">Matched skills</div><p>' . esc(implode(', ', $o['matched']) ?: '—') . '</p>'
            . ($o['gap'] ? '<div class="section-label" style="margin-top:10px">To close</div><p>' . esc(implode(', ', $o['gap'])) . '</p>' : '')
            . '<p class="purpose" style="margin-top:12px">Decision support only. Improve your match in Career Compass.</p>';
      $prepBody = '<p class="muted" style="margin-bottom:8px">Practice answering these before you apply or interview:</p><ol style="padding-left:18px;font-size:14px">'
                . implode('', array_map(fn ($q) => '<li style="margin-bottom:8px">' . esc($q) . '</li>', $o['interview_prep'] ?? []))
                . '</ol><p class="purpose" style="margin-top:12px">Use the STAR method: Situation, Task, Action, Result.</p>';
    ?>
      <div class="card" style="--pc:<?= $o['color'] ?>">
        <div class="row" style="justify-content:space-between">
          <span class="pill <?= $fitClass[$o['fit']] ?? 'ok' ?>"><?= esc($o['fit']) ?></span>
          <span style="font-family:var(--font-head);font-weight:800;font-size:22px;color:var(--text)"><?= (int)$o['match'] ?>%</span>
        </div>
        <h3 style="margin:10px 0 2px"><?= esc($o['title']) ?></h3>
        <p class="muted" style="margin:0"><?= esc($o['company']) ?> · <?= esc($o['location']) ?> · <?= esc($o['salary']) ?></p>
        <p class="muted" style="font-size:10px;margin:3px 0 0;opacity:.75">Example role · synthetic demo data</p>
        <p class="muted" style="margin:10px 0 0">
          <?php if ($o['gap']): ?>Gap: <?= esc(implode(', ', $o['gap'])) ?><?php else: ?>Strong match — no gaps.<?php endif; ?>
        </p>
        <div style="margin-top:12px;display:flex;gap:8px;flex-wrap:wrap">
          <button class="btn btn-ghost" data-drawer="1" data-title="<?= esc($o['title'] . ' — why this match', 'attr') ?>" data-body="<?= esc($body, 'attr') ?>">Why this match?</button>
          <button class="btn btn-ghost" data-drawer="1" data-title="<?= esc($o['title'] . ' — interview prep', 'attr') ?>" data-body="<?= esc($prepBody, 'attr') ?>">Prepare for interview</button>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
  <div class="row" style="margin-top:18px">
    <a class="btn btn-gold btn-lg" href="<?= base_url('compass') ?>">Improve my match →</a>
    <a class="btn btn-ghost" href="<?= base_url('passport') ?>">← Back to portfolio</a>
  </div>
</section>
<?= $this->endSection() ?>
