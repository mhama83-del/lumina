<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php helper('ui'); ?>

<section class="hero">
  <div class="section-label">Resume Analysis · AI</div>
  <h1>Paste your resume. Watch Lumina read it.</h1>
  <p class="purpose">Lumina extracts your skills, scores your readiness, and finds your best match — in seconds.</p>
</section>

<section class="section">
  <div class="grid grid-2">
    <!-- Input -->
    <div class="card">
      <div class="section-label">Your resume</div>
      <textarea id="resumeText" placeholder="Paste your resume or a summary of your experience here…&#10;&#10;e.g. Final-year Computer Science student. Treasurer of the Robotics Club. Built an attendance app with Python. Led a data analysis project."></textarea>
      <div style="margin-top:12px" class="row">
        <button class="btn btn-gold btn-lg" id="analyzeBtn">Analyze with AI →</button>
        <button class="btn btn-ghost" id="sampleBtn">Use a sample</button>
      </div>
    </div>

    <!-- Processing + results -->
    <div class="card">
      <div class="section-label">AI analysis</div>

      <!-- idle -->
      <div id="idle" class="muted" style="padding:20px 0">Your analysis will appear here.</div>

      <!-- processing steps -->
      <div id="processing" style="display:none">
        <div class="ai-step" data-s="0"><span class="ai-step-dot"></span> Reading your resume…</div>
        <div class="ai-step" data-s="1"><span class="ai-step-dot"></span> Extracting explicit skills…</div>
        <div class="ai-step" data-s="2"><span class="ai-step-dot"></span> Inferring hidden skills from evidence…</div>
        <div class="ai-step" data-s="3"><span class="ai-step-dot"></span> Matching to roles…</div>
        <div class="ai-step" data-s="4"><span class="ai-step-dot"></span> Scoring your readiness…</div>
      </div>

      <!-- results -->
      <div id="results" style="display:none">
        <div class="donut-wrap" id="rDonut"></div>
        <div id="rBench" style="margin:8px 0"></div>
        <div id="rField" style="text-align:center;margin:6px 0 14px"></div>
        <div class="section-label">Top role matches · from the taxonomy</div>
        <div id="rMatches" class="stack" style="margin-bottom:12px"></div>
        <div class="section-label">Skills detected</div>
        <div id="rSkills" style="margin-bottom:8px"></div>
        <div id="rGap" class="muted" style="font-size:13px"></div>
        <div class="row" style="margin-top:14px">
          <a class="btn btn-gold" href="<?= base_url('compass') ?>">See my career paths →</a>
          <a class="btn btn-ghost" href="<?= base_url('passport') ?>">View portfolio</a>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section">
  <div class="card">
    <div class="section-label">How this works · under the hood</div>
    <div class="grid grid-3">
      <div><h3>1 · Read &amp; extract</h3><p class="muted">Lumina scans your text for skill signals, mapping words to skills and separating what you <em>stated</em> from what it <em>inferred</em> — e.g. \"treasurer\" &rarr; budgeting.</p></div>
      <div><h3>2 · Score readiness</h3><p class="muted">A transparent weighted formula: skill coverage 40% + evidence 25% + activity 20% + learning pace 15%. No black box.</p></div>
      <div><h3>3 · Match roles</h3><p class="muted">Your skills are compared to each role\'s required skills (overlap) + readiness + trajectory &rarr; a match %. Your best fit is shown.</p></div>
    </div>
    <p class="purpose" style="margin-top:12px">Simulated AI for the demo &mdash; deterministic and explainable, designed to plug into real APIs later.</p>
  </div>
</section>

<script>
const ANALYZE_URL = "<?= base_url('resume/analyze') ?>";
const SAMPLE = "Final-year Computer Science student at USM. Treasurer of the Robotics Club for 2 years. Built an attendance app with Python. Volunteered in a community coding programme. Led a small data analysis project and built dashboards for the faculty.";

function donutSVG(pct, color){
  const r=45, c=2*Math.PI*r, off=c*(1-pct/100);
  return '<svg class="donut" viewBox="0 0 120 120">'+
    '<circle class="track" cx="60" cy="60" r="'+r+'"></circle>'+
    '<circle class="val" cx="60" cy="60" r="'+r+'" style="stroke:'+color+';stroke-dasharray:'+c.toFixed(1)+';stroke-dashoffset:'+c.toFixed(1)+'"></circle>'+
    '<text class="pct" x="60" y="68" text-anchor="middle">0%</text></svg>'+
    '<div class="donut-label">Readiness · <span id="rRole"></span></div>';
}

function runSteps(done){
  const steps = document.querySelectorAll('#processing .ai-step');
  let i = 0;
  steps.forEach(s=>s.classList.remove('active','done'));
  const tick = ()=>{
    if(i>0) steps[i-1].classList.remove('active'), steps[i-1].classList.add('done');
    if(i<steps.length){ steps[i].classList.add('active'); i++; setTimeout(tick, 480); }
    else { done(); }
  };
  tick();
}

function analyze(text){
  document.getElementById('idle').style.display='none';
  document.getElementById('results').style.display='none';
  document.getElementById('processing').style.display='block';

  const body = new URLSearchParams(); body.append('resume_text', text);
  const fetchP = fetch(ANALYZE_URL,{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest'},body}).then(r=>r.json());

  runSteps(()=>{
    fetchP.then(data=>{
      if(data.error){ document.getElementById('processing').style.display='none'; document.getElementById('idle').style.display='block'; document.getElementById('idle').textContent='Please paste some text first.'; return; }
      document.getElementById('processing').style.display='none';
      const res = document.getElementById('results'); res.style.display='block';
      const top = (data.matches && data.matches[0]) || null;
      const color = top ? top.color : '#6C5CE7';
      document.getElementById('rDonut').innerHTML = donutSVG(data.readiness, color);
      document.getElementById('rRole').textContent = top ? top.title : data.domain;
      // animate donut
      setTimeout(()=>{ const v=document.querySelector('#rDonut .val'), t=document.querySelector('#rDonut .pct');
        const r=45,c=2*Math.PI*r; v.style.strokeDashoffset=(c*(1-data.readiness/100)).toFixed(1); t.textContent=data.readiness+'%'; }, 60);
      // field alignment
      document.getElementById('rField').innerHTML = 'Your profile aligns with <strong class=\"gold\">'+data.field+'</strong>'+(data.university?(' \u00b7 detected: '+data.university):'');
      if(data.cohort && data.cohort.size){ var c=data.cohort;
        document.getElementById('rBench').innerHTML = '<div style=\"background:rgba(108,92,231,.10);border:1px solid rgba(108,92,231,.35);border-radius:10px;padding:10px 13px;font-size:13px\">Benchmarked against <strong>'+c.size+'</strong> '+c.domain+' students in the cohort: your readiness <strong class=\"gold\">'+c.you+'%</strong> is higher than <strong>'+c.percentile+'%</strong> of them (cohort average '+c.avg+'%).</div>'; }
      // top-3 role matches
      document.getElementById('rMatches').innerHTML = (data.matches||[]).map((m,i)=>{
        const fit = m.label==='best'?'ok':(m.label==='growth'?'nudge':'risk');
        const gap = m.gap.length ? ' · gap: '+m.gap.join(', ') : ' · strong match';
        return '<div class="ev" style="display:flex;justify-content:space-between;align-items:center;gap:8px">'+
          '<span>'+(i+1)+'. <strong>'+m.title+'</strong> <span class="muted">@ '+m.company+gap+'</span></span>'+
          '<span class="pill '+fit+'">'+m.match+'%</span></div>';
      }).join('');
      // skills
      document.getElementById('rSkills').innerHTML = data.skills.map(s=>{
        const cls = s.source==='inferred' ? 'skill inferred' : 'skill';
        const tip = s.from ? ('inferred from: "'+s.from+'"') : 'you stated this';
        const tag = s.from ? ' <span class="conf">\u2190 '+s.from+'</span>' : '';
        return '<span class="'+cls+'" title="'+tip+'">'+s.label+tag+'</span>';
      }).join('');
      // gap
      document.getElementById('rGap').innerHTML = (top && top.gap.length)
        ? 'To reach a stronger match for '+top.title+', add: <strong style="color:var(--gold)">'+top.gap.join(', ')+'</strong>.'
        : 'Strong match — no major gaps.';
    });
  });
}

document.getElementById('analyzeBtn').onclick = ()=>{ const t=document.getElementById('resumeText').value.trim(); if(t) analyze(t); };
document.getElementById('sampleBtn').onclick = ()=>{ document.getElementById('resumeText').value = SAMPLE; analyze(SAMPLE); };
</script>
<?= $this->endSection() ?>
