<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php helper('ui'); ?>

<section class="hero">
  <div class="section-label">My Career Intelligence · Resume Analysis</div>
  <h1>Paste your resume. Watch Lumina read it.</h1>
  <p class="purpose">Lumina extracts your skills, scores your readiness, reads your Work Animal, and finds your best match — in seconds.</p>
</section>

<section class="section">
  <div class="grid grid-2">
    <!-- Input -->
    <div class="card">
      <div class="section-label">Your resume</div>
      <textarea id="resumeText" placeholder="Paste your resume or a summary of your experience here…&#10;&#10;e.g. Final-year Computer Science student. Treasurer of the Robotics Club. Built an attendance app with Python. Led a data analysis project."></textarea>
      <div id="liveHints" class="muted" style="font-size:12px;margin-top:6px;min-height:16px"></div>
      <div style="margin-top:12px" class="row">
        <button class="btn btn-gold btn-lg" id="analyzeBtn">Analyze with AI →</button>
        <button class="btn btn-ghost" id="sampleBtn">Use a sample</button>
      </div>
      <p class="muted" style="font-size:12px;margin-top:10px">No resume? Try the <a href="<?= base_url('start') ?>" class="gold">guided No-Resume builder →</a></p>
    </div>

    <!-- Processing + confirm + results -->
    <div class="card">
      <div class="section-label">AI analysis</div>

      <!-- idle -->
      <div id="idle" class="muted" style="padding:20px 0">Your analysis will appear here.</div>

      <!-- processing steps -->
      <div id="processing" style="display:none">
        <div class="ai-step" data-s="0"><span class="ai-step-dot"></span> Reading your resume…</div>
        <div class="ai-step" data-s="1"><span class="ai-step-dot"></span> Extracting explicit skills…</div>
        <div class="ai-step" data-s="2"><span class="ai-step-dot"></span> Inferring hidden skills from evidence…</div>
        <div class="ai-step" data-s="3"><span class="ai-step-dot"></span> Reading your Work Animal…</div>
        <div class="ai-step" data-s="4"><span class="ai-step-dot"></span> Matching to roles &amp; scoring readiness…</div>
      </div>

      <!-- confirm profile (name + highlights), before full analysis runs -->
      <div id="confirm" style="display:none">
        <div class="section-label">Confirm your profile</div>
        <p class="muted" style="font-size:12px;margin:2px 0 12px">Lumina read your resume and picked these up. Fix anything before we analyse.</p>
        <label class="muted" style="font-size:12px">Your name</label>
        <input type="text" id="nameInput" placeholder="Enter your name" style="width:100%;margin:4px 0 14px;padding:9px 11px;border-radius:8px;border:1px solid var(--line);background:rgba(255,255,255,.03);color:inherit;font-size:14px;box-sizing:border-box">
        <div class="muted" style="font-size:12px;margin-bottom:6px">What we noticed:</div>
        <ul id="confirmHighlights" class="muted" style="font-size:13px;margin:0 0 16px;padding-left:18px"></ul>
        <div class="row" style="gap:8px;flex-wrap:wrap">
          <button class="btn btn-gold" id="confirmGo">Looks good — Analyze with AI →</button>
          <button class="btn btn-ghost" id="confirmEdit">← Edit resume</button>
        </div>
      </div>

      <!-- results -->
      <div id="results" style="display:none">
        <!-- Layer 1 · summary -->
        <div class="donut-wrap" id="rDonut"></div>
        <div id="rBand" style="text-align:center;margin:2px 0 6px"></div>
        <div id="rBench" style="margin:8px 0"></div>
        <div id="rField" style="text-align:center;margin:6px 0 2px"></div>
        <div id="rCluster" style="text-align:center;margin:0 0 14px"></div>

        <!-- Layer 2 · Work Animal -->
        <div class="section-label">Your Work Animal · from evidence</div>
        <div id="rAnimal" style="margin-bottom:14px"></div>

        <!-- Top matches -->
        <div class="section-label">Top role matches · from the taxonomy</div>
        <div id="rMatches" class="stack" style="margin-bottom:12px"></div>

        <!-- Skills -->
        <div class="section-label">Skills detected</div>
        <div id="rSkills" style="margin-bottom:8px"></div>
        <div id="rGap" class="muted" style="font-size:13px;margin-bottom:14px"></div>

        <!-- Evidence: projects + leadership -->
        <div id="rEvidence"></div>

        <!-- Feedback -->
        <div class="section-label">Resume feedback</div>
        <ul id="rFeedback" class="muted" style="font-size:13px;margin:0 0 14px;padding-left:18px"></ul>

        <!-- Resume Coach (Strategic B4, restored) -->
        <div class="section-label">Resume Coach</div>
        <div id="rCoach" style="margin-bottom:14px"></div>

        <!-- Profile Consistency Check (Strategic C2) -->
        <div id="rConsistency" style="margin-bottom:14px"></div>

        <!-- Internships -->
        <div class="section-label">Recommended internship roles</div>
        <div id="rInternships" style="margin-bottom:14px"></div>

        <!-- Next best action -->
        <div id="rNext" style="margin-bottom:14px"></div>

        <!-- Micro-courses -->
        <div class="section-label">Micro-courses to close gaps</div>
        <div id="rCourses" class="muted" style="font-size:13px;margin-bottom:12px"></div>

        <!-- Lumina Graph new + related -->
        <div id="rGraphNew"></div>
        <div class="section-label">Related skills · from the Lumina Graph</div>
        <div id="rGraph" style="margin-bottom:6px"></div>
        <p class="muted" id="rGraphNote" style="font-size:12px;margin-bottom:12px"></p>

        <div id="rSaved" class="muted" style="font-size:12px;margin-bottom:10px"></div>

        <div class="row" style="margin-top:6px">
          <a class="btn btn-gold" href="<?= base_url('compass') ?>">See my career paths →</a>
          <a class="btn btn-ghost" href="<?= base_url('passport') ?>">View My EDGE Profile →</a>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section">
  <div class="card">
    <div class="section-label">How this works · under the hood</div>
    <div class="grid grid-3">
      <div><h3>1 · Read &amp; extract</h3><p class="muted">Lumina scans your text for a name and skill signals, separating what you <em>stated</em> from what it <em>inferred</em> — e.g. "treasurer" &rarr; budgeting.</p></div>
      <div><h3>2 · Score readiness</h3><p class="muted">A transparent weighted formula: skill coverage 40% + evidence 25% + activity 20% + learning pace 15%. No black box.</p></div>
      <div><h3>3 · Match &amp; recommend</h3><p class="muted">Skills are compared to each role's requirements &rarr; a match %, plus feedback, internships and next best action.</p></div>
    </div>
    <p class="purpose" style="margin-top:12px">Simulated AI for the demo &mdash; deterministic and explainable, designed to plug into real APIs later.</p>
  </div>
</section>

<script>
const ANALYZE_URL = "<?= base_url('resume/analyze') ?>";
const PREVIEW_URL = "<?= base_url('resume/preview') ?>";
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
function pillClass(band){ return band==='On track'?'ok':(band==='Needs a nudge'?'nudge':'risk'); }

function showOnly(id){
  ['idle','processing','confirm','results'].forEach(function(k){
    document.getElementById(k).style.display = (k===id) ? 'block' : 'none';
  });
}

/** Step 1: lightweight read — detect name + highlights, let the candidate confirm/edit. */
function preview(text){
  showOnly('processing');
  const body = new URLSearchParams(); body.append('resume_text', text);
  fetch(PREVIEW_URL,{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest'},body}).then(r=>r.json()).then(function(data){
    if(data.error){ showOnly('idle'); document.getElementById('idle').textContent='Please paste some text first.'; return; }
    document.getElementById('nameInput').value = data.name || '';
    document.getElementById('nameInput').dataset.text = text;
    var hi = data.highlights || [];
    document.getElementById('confirmHighlights').innerHTML = hi.length
      ? hi.map(function(h){ return '<li>'+h+'</li>'; }).join('')
      : '<li>No specific highlights detected — that\'s okay, analysis still works.</li>';
    showOnly('confirm');
  });
}

/** Step 2: full AI analysis (existing pipeline), now carrying the confirmed name. */
function analyze(text, name){
  showOnly('processing');
  const body = new URLSearchParams(); body.append('resume_text', text); if(name) body.append('name', name);
  const fetchP = fetch(ANALYZE_URL,{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest'},body}).then(r=>r.json());
  runSteps(()=>{
    fetchP.then(data=>{
      if(data.error){ showOnly('idle'); document.getElementById('idle').textContent='Please paste some text first.'; return; }
      showOnly('results');
      const top = (data.matches && data.matches[0]) || null;
      const color = top ? top.color : '#6C5CE7';

      // donut
      document.getElementById('rDonut').innerHTML = donutSVG(data.readiness, color);
      document.getElementById('rRole').textContent = top ? top.title : data.domain;
      setTimeout(()=>{ const v=document.querySelector('#rDonut .val'), t=document.querySelector('#rDonut .pct');
        const r=45,c=2*Math.PI*r; v.style.strokeDashoffset=(c*(1-data.readiness/100)).toFixed(1); t.textContent=data.readiness+'%'; }, 60);

      // band + cluster
      document.getElementById('rBand').innerHTML = '<span class="pill '+pillClass(data.band)+'">'+data.band+'</span> <span class="muted" style="font-size:12px">employability band</span>';
      document.getElementById('rCluster').innerHTML = data.career_cluster ? ('Career cluster: <strong class="gold">'+data.career_cluster+'</strong>') : '';

      // field alignment
      document.getElementById('rField').innerHTML = 'Your profile aligns with <strong class="gold">'+data.field+'</strong>'+(data.university?(' · detected: '+data.university):'');

      // benchmark
      if(data.cohort && data.cohort.size){ var cb=data.cohort;
        document.getElementById('rBench').innerHTML = '<div style="background:rgba(108,92,231,.10);border:1px solid rgba(108,92,231,.35);border-radius:10px;padding:10px 13px;font-size:13px">Benchmarked against <strong>'+cb.size+'</strong> '+cb.domain+' students in the cohort: your readiness <strong class="gold">'+cb.you+'%</strong> is higher than <strong>'+cb.percentile+'%</strong> of them (cohort average '+cb.avg+'%).</div>'; }
      else { document.getElementById('rBench').innerHTML=''; }

      // Work Animal
      if(data.animal){ var a=data.animal;
        var chip=function(x,role){ return '<span class="skill">'+x.label+' <span class="conf">'+role+'</span></span>'; };
        document.getElementById('rAnimal').innerHTML =
          '<div class="card card-tight">'+
          '<div style="margin-bottom:8px">'+chip(a.primary,'primary')+' '+chip(a.secondary,'secondary')+' '+chip(a.growth,'growth')+'</div>'+
          '<div class="muted" style="font-size:13px">'+a.line+' <span class="gold">Confidence '+a.confidence+'%</span></div>'+
          '<div class="muted" style="font-size:12px;margin-top:6px">Traits: '+(a.primary.traits||[]).join(' · ')+'</div>'+
          '<div class="muted" style="font-size:12px;margin-top:4px">'+(a.primary.role||'')+(a.careerFit? ' · Career fit: '+a.careerFit.join(', '):'')+'</div>'+
          (a.growthAdvice? '<div class="muted" style="font-size:12px;margin-top:4px">Growth ('+(a.growth?a.growth.label:'')+'): '+a.growthAdvice+'</div>':'')+
          '</div>';
      }

      // top-3 role matches
      document.getElementById('rMatches').innerHTML = (data.matches||[]).map(function(m,i){
        var fit = m.label==='best'?'ok':(m.label==='growth'?'nudge':'risk');
        var gap = m.gap.length ? ' · gap: '+m.gap.join(', ') : ' · strong match';
        return '<div class="ev" style="display:flex;justify-content:space-between;align-items:center;gap:8px">'+
          '<span>'+(i+1)+'. <strong>'+m.title+'</strong> <span class="muted">@ '+m.company+gap+'</span></span>'+
          '<span class="pill '+fit+'">'+m.match+'%</span></div>';
      }).join('');

      // skills
      document.getElementById('rSkills').innerHTML = data.skills.map(function(s){
        var cls = s.source==='inferred' ? 'skill inferred' : 'skill';
        var tip = s.from ? ('inferred from: "'+s.from+'"') : 'you stated this';
        var tag = s.from ? ' <span class="conf">&larr; '+s.from+'</span>' : '';
        return '<span class="'+cls+'" title="'+tip+'">'+s.label+tag+'</span>';
      }).join('');

      // gap
      document.getElementById('rGap').innerHTML = (top && top.gap.length)
        ? 'To reach a stronger match for '+top.title+', add: <strong style="color:var(--gold)">'+top.gap.join(', ')+'</strong>.'
        : 'Strong match — no major gaps.';

      // evidence: projects + leadership
      var ev='';
      if(data.projects && data.projects.length){ ev+='<div class="section-label">Projects detected</div><ul class="muted" style="font-size:13px;margin:0 0 12px;padding-left:18px">'+data.projects.map(function(p){return '<li>'+p+'</li>';}).join('')+'</ul>'; }
      if(data.leadership && data.leadership.length){ ev+='<div class="section-label">Leadership detected</div><ul class="muted" style="font-size:13px;margin:0 0 12px;padding-left:18px">'+data.leadership.map(function(p){return '<li>'+p+'</li>';}).join('')+'</ul>'; }
      document.getElementById('rEvidence').innerHTML = ev;

      // feedback
      document.getElementById('rFeedback').innerHTML = (data.feedback||[]).map(function(f){return '<li>'+f+'</li>';}).join('');

      // Resume Coach (Strategic B4, restored)
      if(data.resume_coach){ var rc=data.resume_coach; var coachHtml='';
        if(rc.top_strengths && rc.top_strengths.length){
          coachHtml += '<div class="muted" style="font-size:13px;margin-bottom:6px"><strong style="color:var(--text)">What works:</strong> '+rc.top_strengths.join(', ')+'</div>';
        }
        if(rc.evidence_gaps && rc.evidence_gaps.length){
          coachHtml += '<div class="muted" style="font-size:13px;margin-bottom:6px"><strong style="color:var(--text)">What is missing:</strong> '+rc.evidence_gaps.join(', ')+'</div>';
        }
        if(rc.role_alignment_gap){
          coachHtml += '<div class="muted" style="font-size:13px;margin-bottom:6px"><strong style="color:var(--text)">Fix next:</strong> '+rc.role_alignment_gap+'</div>';
        }
        if(rc.before_after){
          coachHtml += '<div style="background:rgba(108,92,231,.08);border:1px solid rgba(108,92,231,.3);border-radius:10px;padding:10px 13px;font-size:13px;margin-top:8px">'+
            '<div class="muted" style="margin-bottom:4px"><strong style="color:var(--text)">Before:</strong> '+rc.before_after.before+'</div>'+
            '<div class="muted"><strong class="gold">Better:</strong> '+rc.before_after.after+'</div>'+
            '</div>';
        }
        document.getElementById('rCoach').innerHTML = coachHtml || '<span class="muted" style="font-size:13px">Not enough evidence yet to coach \xe2\x80\x94 add more detail to your resume.</span>';
      }
      // Profile Consistency Check (Strategic C2)
      document.getElementById('rConsistency').innerHTML = (data.consistency_flags||[]).map(function(f){
        return '<div class="muted" style="font-size:13px;background:rgba(253,224,71,.08);border:1px solid rgba(253,224,71,.3);border-radius:8px;padding:8px 11px;margin-bottom:6px">'+f.message+'</div>';
      }).join('');

      // internships
      document.getElementById('rInternships').innerHTML = (data.internships||[]).map(function(x){return '<span class="skill">'+x+'</span>';}).join(' ');

      // next best action
      document.getElementById('rNext').innerHTML = '<div style="background:rgba(245,197,24,.10);border:1px solid rgba(245,197,24,.35);border-radius:10px;padding:11px 13px;font-size:13px"><strong>Next best action:</strong> '+data.next_action+'</div>';

      // micro-courses
      document.getElementById('rCourses').innerHTML = (data.courses||[]).map(function(c){return '<div>• <strong>'+c.skill+'</strong> — '+c.course+'</div>';}).join('');

      // Lumina Graph related + growth counter
      document.getElementById('rGraph').innerHTML = (data.graph_related && data.graph_related.length)
        ? data.graph_related.map(function(g){return '<span class="skill inferred">'+g.label+' <span class="conf">from graph</span></span>';}).join(' ')
        : '<span class="muted" style="font-size:13px">No adjacent skills surfaced yet.</span>';
      document.getElementById('rGraphNew').innerHTML = (data.graph_new_skills && data.graph_new_skills.length)
        ? '<div style="background:rgba(245,197,24,.10);border:1px solid rgba(245,197,24,.35);border-radius:10px;padding:10px 13px;font-size:13px;margin-bottom:10px">&#127793; <strong>New skills learned into the graph:</strong> '+data.graph_new_skills.join(', ')+' — Lumina now recognises these for future candidates.</div>'
        : '';
      if (data.graph_stats){ var gs=data.graph_stats;
        document.getElementById('rGraphNote').innerHTML = 'People with similar profiles often also develop these. Lumina Graph now knows <strong>'+gs.skills+'</strong> skills · <strong>'+gs.patterns+'</strong> patterns · <strong>'+gs.profiles_learned+'</strong> profiles'
          + (data.graph_added ? ' — <span class="gold">+'+data.graph_added+' new learned from your resume</span>' : ' — your resume matched existing patterns') + '.';
      }

      // saved
      document.getElementById('rSaved').textContent = data.saved ? ('Saved to your Lumina record (#'+data.saved_id+').') : '';
    });
  });
}

document.getElementById('analyzeBtn').onclick = ()=>{ const t=document.getElementById('resumeText').value.trim(); if(t) preview(t); };
document.getElementById('sampleBtn').onclick = ()=>{ document.getElementById('resumeText').value = SAMPLE; preview(SAMPLE); };
document.getElementById('confirmGo').onclick = ()=>{
  const t = document.getElementById('nameInput').dataset.text || document.getElementById('resumeText').value.trim();
  const n = document.getElementById('nameInput').value.trim();
  analyze(t, n);
};
document.getElementById('confirmEdit').onclick = ()=>{ showOnly('idle'); };

// Strategic C4: Responsive Resume Intelligence — deterministic, client-side
// live hints while typing (debounced). No AI API, no server round-trip.
(function () {
  var ta = document.getElementById('resumeText');
  var hintBox = document.getElementById('liveHints');
  if (!ta || !hintBox) return;
  var leadershipWords = ['led ','lead ','president','treasurer','captain','head ','manage','mentor','coordinat','organis','founder'];
  var outcomeWords = ['increas','improv','reduc','grew','deliver','achiev','launch','sav','cut ','boost','streamlin','automat'];
  var timer = null;
  function hasAny(text, words) {
    for (var i = 0; i < words.length; i++) { if (text.indexOf(words[i]) !== -1) return true; }
    return false;
  }
  function checkText() {
    var text = ta.value.toLowerCase();
    if (text.trim().length < 25) { hintBox.innerHTML = ''; return; }
    var hasNumber = /\d/.test(text);
    var hasOutcome = hasAny(text, outcomeWords);
    var hasLeadership = hasAny(text, leadershipWords);
    var msg;
    if (hasLeadership && !hasNumber && !hasOutcome) {
      msg = 'You mentioned leadership — add team size and what changed.';
    } else if (!hasNumber && !hasOutcome) {
      msg = 'Add a number, scale or outcome to make this stronger.';
    } else {
      msg = 'Your evidence is looking stronger.';
    }
    hintBox.innerHTML = '<span style="color:var(--gold)">•</span> ' + msg;
  }
  ta.addEventListener('input', function () {
    clearTimeout(timer);
    timer = setTimeout(checkText, 600);
  });
})();
</script>
<?= $this->endSection() ?>
