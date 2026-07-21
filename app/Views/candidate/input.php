<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $profile = session('profile') ?? []; ?>
<section class="hero">
  <div class="section-label">Build your Living Portfolio</div>
  <h1>Tell Lumina what you've done.</h1>
  <p class="lead">No resume needed — answer a guided form and Lumina builds a starter portfolio, a readiness score, and a resume draft.<?php if (!empty($profile['animalLabel'])): ?> You're <strong class="gold"><?= esc($profile['animalLabel']) ?></strong>.<?php endif; ?></p>
</section>
<section class="section">
  <div class="card" data-tabs>
    <div class="tabs">
      <div class="tab" data-tab="panel-guided">Guided setup ★</div>
      <div class="tab active" data-tab="panel-paste">Paste activities</div>
      <div class="tab" data-tab="panel-transcript">Import transcript</div>
      <div class="tab" data-tab="panel-5q">5 questions</div>
    </div>
    <form method="post" action="<?= base_url('onboard/input') ?>">

      <!-- Guided setup (Fasa 3) -->
      <div class="tabpanel" id="panel-guided">
        <div class="grid grid-2">
          <div>
            <label class="fl">Programme / course</label>
            <input class="field" type="text" name="g_programme" placeholder="e.g. BSc Computer Science">
          </div>
          <div>
            <label class="fl">CGPA <span class="muted">(optional)</span></label>
            <input class="field" type="text" name="g_cgpa" placeholder="e.g. 3.50">
          </div>
          <div>
            <label class="fl">Field of interest</label>
            <select class="field" name="g_interest">
              <option>Data</option><option>Engineering</option><option>Business</option><option>Design</option>
            </select>
          </div>
          <div>
            <label class="fl">Activities / clubs joined</label>
            <input class="field" type="text" name="g_activities" placeholder="e.g. Robotics Club, Volunteer Corps">
          </div>
          <div>
            <label class="fl">Leadership role <span class="muted">(if any)</span></label>
            <input class="field" type="text" name="g_leadership" placeholder="e.g. Treasurer, President">
          </div>
          <div>
            <label class="fl">Projects done</label>
            <input class="field" type="text" name="g_projects" placeholder="e.g. Built an attendance app">
          </div>
          <div>
            <label class="fl">Tools used</label>
            <input class="field" type="text" name="g_tools" placeholder="e.g. Python, Excel, Figma">
          </div>
          <div>
            <label class="fl">Competitions <span class="muted">(if any)</span></label>
            <input class="field" type="text" name="g_competitions" placeholder="e.g. Hackathon 2025">
          </div>
          <div>
            <label class="fl">Internship status</label>
            <select class="field" name="g_internship">
              <option value="none">Not yet</option>
              <option value="ongoing">Currently doing one</option>
              <option value="completed">Completed one</option>
            </select>
          </div>
          <div>
            <label class="fl">Preferred role <span class="muted">(optional)</span></label>
            <input class="field" type="text" name="g_role" placeholder="e.g. Data Analyst">
          </div>
          <div>
            <label class="fl">When facing a hard problem, I…</label>
            <select class="field" name="g_ws1">
              <option value="I like to analyse it deeply">Analyse it deeply</option>
              <option value="I improvise and adapt quickly">Improvise and adapt</option>
              <option value="I decide fast and lead">Decide fast and lead</option>
              <option value="I talk it through with people">Talk it through with people</option>
              <option value="I follow a careful method">Follow a careful method</option>
            </select>
          </div>
        </div>
        <div style="margin-top:8px">
          <label class="fl">The impact I want to have is…</label>
          <select class="field" name="g_ws2">
            <option value="sharp insight from data">Sharp insight from data</option>
            <option value="building new things">Building new things</option>
            <option value="leading a team to deliver">Leading a team to deliver</option>
            <option value="bringing people together">Bringing people together</option>
            <option value="keeping things running well">Keeping things running well</option>
          </select>
        </div>
        <div style="margin-top:14px">
          <button class="btn btn-primary btn-lg" type="submit" name="method" value="guided">Build my Starter Portfolio →</button>
        </div>
      </div>

      <!-- Paste -->
      <div class="tabpanel active" id="panel-paste">
        <label class="fl">Paste your clubs, projects, or experience (1–3 sentences is enough)</label>
        <div class="row" style="margin:0 0 10px">
          <button class="btn btn-ghost" type="button" data-persona-ev="aiman">Aiman · IT</button>
          <button class="btn btn-ghost" type="button" data-persona-ev="nurul">Nurul · Business</button>
          <button class="btn btn-ghost" type="button" data-persona-ev="weijie">Wei Jie · Engineering</button>
          <button class="btn btn-ghost" type="button" id="fillSelfBtn">Fill it myself</button>
        </div>
        <div id="evidenceNote"></div>
        <div class="grid grid-2" style="gap:10px;margin-bottom:10px">
          <input class="field" type="text" id="pName" name="p_name" placeholder="Your name (optional)">
          <input class="field" type="text" id="pProgramme" name="p_programme" placeholder="Programme (optional)">
          <input class="field" type="text" id="pUniversity" name="p_university" placeholder="University (optional)">
          <input class="field" type="text" id="pYear" name="p_year" placeholder="Study year (optional)">
          <input class="field" type="text" id="pCgpa" name="p_cgpa" placeholder="CGPA (optional)">
        </div>
        <textarea id="evidenceText" name="evidence_text" placeholder="e.g. Treasurer of the Robotics Club; built an attendance app; led a data project."></textarea>
        <div style="margin-top:12px"><button class="btn btn-primary" type="submit" name="method" value="paste">Build my Living Portfolio →</button></div>
      </div>

      <!-- Transcript -->
      <div class="tabpanel" id="panel-transcript">
        <label class="fl">Co-curricular transcript (MyCSD) — demo sample</label>
        <div class="card card-tight" style="background:var(--card-2)"><span class="muted"><?= esc($sample) ?></span></div>
        <p class="purpose" style="margin-top:8px">Lumina reads verified activities and translates them into skills.</p>
        <button class="btn btn-primary" type="submit" name="method" value="transcript">Import &amp; build →</button>
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
        <div style="margin-top:12px"><button class="btn btn-primary" type="submit" name="method" value="fiveq">Build my Living Portfolio →</button></div>
      </div>
    </form>
  </div>
</section>
<script>
(function () {
  var PERSONA_EVIDENCE = <?= json_encode($personaEvidence ?? []) ?>;
  var ta = document.getElementById('evidenceText');
  var note = document.getElementById('evidenceNote');
  var selfBtn = document.getElementById('fillSelfBtn');
  if (!ta) return;
  document.querySelectorAll('[data-persona-ev]').forEach(function (b) {
    b.onclick = function () {
      var p = PERSONA_EVIDENCE[b.getAttribute('data-persona-ev')] || {};
      ta.value = p.text || '';
      ['pName','pProgramme','pUniversity','pYear','pCgpa'].forEach(function (k) {
        var el = document.getElementById(k); if (el && p[k]) el.value = p[k];
      });
      note.innerHTML = '<div style="display:flex;gap:8px;align-items:center;background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.25);color:#4ade80;padding:9px 13px;border-radius:10px;font-size:13px;margin:0 0 12px"><span>&#10003;</span><span>Demo mode &middot; Sample answers can still be edited.</span></div>';
      ta.focus();
    };
  });
  selfBtn.onclick = function () {
    ta.value = ''; note.innerHTML = '';
    ['pName','pProgramme','pUniversity','pYear','pCgpa'].forEach(function (k) {
      var el = document.getElementById(k); if (el) el.value = '';
    });
    ta.focus();
  };
})();
</script>
<?= $this->endSection() ?>
