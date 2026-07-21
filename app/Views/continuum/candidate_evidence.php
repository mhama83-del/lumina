<?= $this->extend('continuum/_layout') ?>
<?= $this->section('content') ?>
<div class="pagehead"><div class="eyebrow">Candidate · evidence</div><h1>My evidence</h1>
  <p class="sub">The dashed outline is what you've reflected on. The filled shape is what you've backed with a source.</p></div>
<div style="margin-bottom:6px"><a class="cta secondary" href="/candidate/survey">Take the guided survey →</a></div>
<?= $this->include('continuum/_meridian', ['map'=>$map]) ?>

<div class="card"><h3>Add an evidence example</h3>
<form method="post" action="/candidate/evidence/add">
  <?= csrf_field() ?>
  <label class="small muted" for="ev_signal">EDGE area</label>
  <select id="ev_signal" name="signal" style="width:100%;padding:9px 12px;border:1px solid var(--border-strong);border-radius:8px;margin:5px 0 8px">
    <option value="reasoning_judgement">Reasoning &amp; Judgement</option>
    <option value="delivery_reliability">Delivery &amp; Reliability</option>
    <option value="collaboration_communication">Collaboration &amp; Communication</option>
    <option value="learning_adaptation">Learning &amp; Adaptation</option>
    <option value="initiative_ownership">Initiative &amp; Ownership</option>
  </select>
  <input type="text" name="claim_text" placeholder="What did you actually do? (a real, specific example)">
  <input type="text" name="source_excerpt" placeholder="Optional: a source excerpt (adding one makes it Supported)">
  <button class="cta" type="submit" style="margin-top:8px">Add evidence</button>
</form></div>
<div class="card"><h3>Evidence items</h3>
<?php if (! $claims): ?><div class="empty">No evidence yet. Add an example with a source to start building your Meridian Map.</div><?php endif; ?>
<?php foreach ($claims as $c): ?>
  <div class="req-row"><div class="lead"><strong><?= esc($c['claim_text']) ?></strong>
    <div class="small muted"><?= esc(ucwords(str_replace('_',' ',$c['signal']))) ?></div></div>
    <?= $this->include('continuum/_label_chip', ['label'=>$c['label']]) ?></div>
<?php endforeach; ?>
</div>
<a class="cta" href="/candidate/roles/data-analyst">Review a role's requirements →</a>
<?= $this->endSection() ?>
