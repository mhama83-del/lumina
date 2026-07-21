<?= $this->extend('continuum/_layout') ?>
<?= $this->section('content') ?>
<div class="decision"><h2>Candidate review</h2>
<p><span class="chip gate-<?= esc($view['queue_label']) ?>"><?= esc($view['queue_label_text']) ?></span>
   · Availability: <?= esc($view['availability']) ?>
   · Consent valid until: <?= esc($view['consent_valid_until'] ?? '—') ?></p></div>

<div class="card"><h3>Requirement coverage (explained)</h3>
<?php foreach ($view['requirements'] as $b): ?>
  <div class="req-row"><div><strong><?= esc($b['requirement']) ?></strong>
    <span class="chip"><?= esc(ucfirst($b['importance'])) ?></span>
    <div class="small muted"><?= esc($b['explanation']) ?></div></div>
    <div class="suff"><?= esc($b['sufficiency']) ?> / <?= esc($b['max']) ?></div></div>
<?php endforeach; ?></div>

<?php if ($view['questions_to_confirm']): ?>
<div class="card"><h3>Questions to Confirm</h3><ul>
<?php foreach ($view['questions_to_confirm'] as $q): ?><li><?= esc($q['question']) ?></li><?php endforeach; ?>
</ul></div>
<?php endif; ?>

<div class="card"><h3>Consented evidence summary</h3>
<?php foreach ($view['evidence_summary'] as $e): ?>
  <div class="req-row"><div><?= esc($e['claim_text']) ?>
    <?php if ($e['source_excerpt']): ?><div class="small muted">Source: <?= esc($e['source_excerpt']) ?></div><?php endif; ?></div>
    <?= $this->include('continuum/_label_chip', ['label'=>$e['label']]) ?></div>
<?php endforeach; ?>
<p class="small muted">Raw survey answers are not included and are never shared.</p></div>

<div class="card"><h3>Update / question</h3>
<form method="post" action="/employer/review/<?= esc($view['application_id']) ?>/status" style="display:inline">
  <?= csrf_field() ?><input type="hidden" name="to" value="under_review">
  <button class="cta secondary" type="submit">Start / continue review</button></form>
<form method="post" action="/employer/review/<?= esc($view['application_id']) ?>/clarify" style="display:inline">
  <?= csrf_field() ?><input type="hidden" name="question" value="Please add a source for your SQL example.">
  <button class="cta" type="submit">Request clarification</button></form>
</div>
<?= $this->endSection() ?>
