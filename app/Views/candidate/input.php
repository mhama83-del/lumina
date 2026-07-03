<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $profile = session('profile') ?? []; ?>
<section class="hero">
  <div class="section-label">Build your Living Portfolio</div>
  <h1>Tell Lumina what you've done.</h1>
  <p class="lead">Lumina reads it and infers your skills — even from a short paragraph.<?php if (!empty($profile['animalLabel'])): ?> You're <strong class="gold"><?= esc($profile['animalLabel']) ?></strong>.<?php endif; ?></p>
</section>

<section class="section">
  <div class="card" data-tabs>
    <div class="tabs">
      <div class="tab active" data-tab="panel-paste">Paste activities</div>
      <div class="tab" data-tab="panel-transcript">Import transcript</div>
      <div class="tab" data-tab="panel-5q">5 questions</div>
    </div>

    <form method="post" action="<?= base_url('onboard/input') ?>">
      <!-- Paste -->
      <div class="tabpanel active" id="panel-paste">
        <label class="fl">Paste your clubs, projects, or experience (1–3 sentences is enough)</label>
        <textarea id="evidenceText" name="evidence_text" placeholder="e.g. Treasurer of the Robotics Club; built an attendance app; led a data project."></textarea>
        <div style="margin-top:12px"><button class="btn btn-gold" type="submit" name="method" value="paste">Build my Living Portfolio →</button></div>
      </div>

      <!-- Transcript -->
      <div class="tabpanel" id="panel-transcript">
        <label class="fl">Co-curricular transcript (MyCSD) — demo sample</label>
        <div class="card card-tight" style="background:var(--card-2)"><span class="muted"><?= esc($sample) ?></span></div>
        <p class="purpose" style="margin-top:8px">Lumina reads verified activities and translates them into skills.</p>
        <button class="btn btn-gold" type="submit" name="method" value="transcript">Import &amp; build →</button>
      </div>

      <!-- 5 questions -->
      <div class="tabpanel" id="panel-5q">
        <label class="fl">What field interests you most?</label>
        <select class="field" name="q_interest">
          <option>Data</option><option>Engineering</option><option>Business</option><option>Design</option>
        </select>
        <label class="fl">Which subject are you strongest in?</label>
        <input class="field" type="text" name="q_subject" placeholder="e.g. Mathematics, Computing, Business">
        <label class="fl">One activity or thing you've done?</label>
        <input class="field" type="text" name="q_activity" placeholder="e.g. Led a club project">
        <div style="margin-top:12px"><button class="btn btn-gold" type="submit" name="method" value="fiveq">Build my Living Portfolio →</button></div>
      </div>
    </form>
  </div>
</section>
<?= $this->endSection() ?>
