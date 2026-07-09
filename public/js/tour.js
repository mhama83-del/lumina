/* ============================================================
   LUMINA — Guided Demo Mode (v2 · ~5 min, comprehensive)
   Flow: system overview → 1 candidate → 1 industry → 1 university → close.
   Cross-page tour: persists step in sessionStorage, resumes after nav.
   Page stays interactive (dim is visual only). Self-contained trigger.
   ============================================================ */
(function () {
  var ROOT = new URL(BASE).pathname.replace(/\/$/, ''); // '' at domain root
  function path() {
    var p = location.pathname;
    if (ROOT && p.indexOf(ROOT) === 0) p = p.slice(ROOT.length);
    return p || '/';
  }
  function href(target) { return BASE.replace(/\/$/, '') + target; }

  // step.url = page it belongs to; step.nav = URL to reach it (optional); step.chapter = section label
  var STEPS = [
    // ---------- CHAPTER 1 · System overview ----------
    { chapter:'Overview', url:'/', sel:'.hero',
      title:'Meet Lumina',
      body:'An AI Talent Intelligence Layer for Asia’s Career OS. The principle: <b>hire for trajectory, not just history</b>. Three no-login modes — Candidate, Employer, University — over one shared engine.' },
    { chapter:'Overview', url:'/', sel:'.grid',
      title:'One engine, three stakeholders',
      body:'The same intelligence serves students, employers and universities. We’ll walk one of each — but first, how it thinks.' },
    { chapter:'Overview', url:'/how-it-works', nav:'/how-it-works', sel:'.section-label',
      title:'Under the hood',
      body:'A layered, explainable, API-first design. Evidence is parsed, scored by deterministic engines, then matched — nothing is a black box.' },
    { chapter:'Overview', url:'/how-it-works', sel:'.mathmap',
      title:'Every score is traceable',
      body:'Evidence in → 6 scoring engines → 3 decisions. Each formula is shown with a worked example — judges can verify any number.' },

    // ---------- CHAPTER 2 · One candidate (Aiman) ----------
    { chapter:'Candidate', url:'/start', nav:'/start', sel:'.grid',
      title:'Candidate demo — start from nothing',
      body:'Meet Aiman, 19, with no resume. He can begin from a Work Animal quiz, a transcript, or just a few questions.' },
    { chapter:'Candidate', url:'/passport', nav:'/start/sample', sel:'.donut-wrap',
      title:'A profile from nothing',
      body:'Lumina read his scattered evidence and scored his readiness — even with no CV.' },
    { chapter:'Candidate', url:'/passport', sel:'.grid',
      title:'Inferred skills',
      body:'Dashed chips are skills Lumina <b>inferred</b> from evidence (e.g. “treasurer” → budgeting). Tap “Why this score?” to see the weighted breakdown.' },
    { chapter:'Candidate', url:'/compass', nav:'/compass', sel:'.path-card',
      title:'Three real directions',
      body:'Career Compass shows realistic paths, how ready he is for each, and the exact gap to close.' },
    { chapter:'Candidate', url:'/compass', sel:'#gapList',
      title:'Watch it move',
      body:'Add a skill and his readiness rises along the 30/60/90 trajectory — that’s trajectory, not just keyword matching.' },
    { chapter:'Candidate', url:'/match', nav:'/match', sel:'.grid',
      title:'Opportunities that fit',
      body:'Best, Growth and Stretch matches — each ranked by readiness and trajectory, each with the reason why.' },

    // ---------- CHAPTER 3 · One industry (employer) ----------
    { chapter:'Employer', url:'/employer', nav:'/employer', sel:'.grid',
      title:'Industry demo — browse the market',
      body:'1,450 synthetic job descriptions across 11 domains and 11 countries. Filter by domain, level, country and sector.' },
    { chapter:'Employer', url:'/employer/role/4904', nav:'/employer/role/4904', sel:'.stack',
      title:'Rank on fit + trajectory',
      body:'Open a role and Lumina ranks candidates on a Talent Match Signal (40% skill · 20% evidence · 20% velocity · 10% animal · 5% domain · 5% CGPA).' },
    { chapter:'Employer', url:'/employer/role/4904', sel:'.stack',
      title:'Every score opens up',
      body:'Click “Why?” on any candidate for the exact six-part breakdown — a shortlist a recruiter can defend, surfacing talent from unexpected programmes.' },

    // ---------- CHAPTER 4 · One university ----------
    { chapter:'University', url:'/university', nav:'/university', sel:'.grid',
      title:'University demo — the whole cohort',
      body:'1,504 students, 34% with no resume yet — and Lumina still sees them all. Every KPI card is clickable.' },
    { chapter:'University', url:'/university', sel:'.donut-wrap',
      title:'Segmented by readiness',
      body:'On track, needs a nudge, at risk — plus the cohort’s top skill gaps and the single highest-impact intervention.' },
    { chapter:'University', url:'/university/student/1', nav:'/university/student/1', sel:'.card',
      title:'Down to one student — the why',
      body:'Drill into a single at-risk student and see exactly what’s holding their readiness back, with a recommendation tailored to their domain.' },
    { chapter:'University', url:'/university/interventions', nav:'/university/interventions', sel:'.card',
      title:'The highest-leverage move',
      body:'One targeted bootcamp can move hundreds toward career-ready — Lumina names it per programme.' },

    // ---------- CHAPTER 5 · The brain + close ----------
    { chapter:'Lumina Graph', url:'/graph', nav:'/graph', sel:'.grid',
      title:'The brain that learns',
      body:'Every profile grows the Lumina Graph — canonical skills, how they relate, and the patterns behind them. New skills it has never seen are added automatically.' },
    { chapter:'Close', url:'/', nav:'/', sel:'.hero',
      title:'One connected platform',
      body:'From no resume → a direction, a match, and a cohort signal — over a graph that keeps learning. Hire for trajectory, not just history. That’s Lumina.' }
  ];

  var KEY = 'lumina_tour';
  var cur  = function () { var v = sessionStorage.getItem(KEY); return v === null ? null : parseInt(v, 10); };
  var save = function (i) { sessionStorage.setItem(KEY, String(i)); };

  function removeUI() {
    var d = document.getElementById('tourDim'); if (d) d.remove();
    var p = document.getElementById('tourPop'); if (p) p.remove();
    document.querySelectorAll('.tour-target').forEach(function (el) { el.classList.remove('tour-target'); });
  }
  function clear() { sessionStorage.removeItem(KEY); removeUI(); }

  function go(i) {
    if (i < 0) i = 0;
    if (i >= STEPS.length) { clear(); return; }
    var step = STEPS[i]; save(i);
    if (path() !== step.url) { location.href = href(step.nav || step.url); return; }
    show(step, i);
  }

  function show(step, i) {
    removeUI();
    var el = null;
    try { el = document.querySelector(step.sel); } catch (e) {}
    if (el) { el.classList.add('tour-target'); el.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
    var dim = document.createElement('div'); dim.id = 'tourDim'; dim.className = 'tour-dim';
    document.body.appendChild(dim);
    var pop = document.createElement('div'); pop.id = 'tourPop'; pop.className = 'tour-pop';
    pop.innerHTML =
      '<div class="prog">' + (step.chapter || '') + ' · Step ' + (i + 1) + ' / ' + STEPS.length + '</div>' +
      '<h4>' + step.title + '</h4>' +
      '<p class="muted" style="margin:4px 0 0">' + step.body + '</p>' +
      '<div class="row" style="justify-content:flex-end">' +
        '<button class="btn btn-ghost" id="tourSkip">Skip</button>' +
        (i > 0 ? '<button class="btn btn-ghost" id="tourPrev">Back</button>' : '') +
        '<button class="btn btn-gold" id="tourNext">' + (i === STEPS.length - 1 ? 'Finish' : 'Next →') + '</button>' +
      '</div>';
    document.body.appendChild(pop);
    document.getElementById('tourNext').onclick = function () { go(i + 1); };
    document.getElementById('tourSkip').onclick = function () { clear(); };
    var prev = document.getElementById('tourPrev'); if (prev) prev.onclick = function () { go(i - 1); };
  }

  function resume() {
    var i = cur(); if (i === null) return;
    var step = STEPS[i];
    if (step && path() === step.url) { setTimeout(function () { show(step, i); }, 350); }
  }

  window.LuminaTour = {
    start: function () { clear(); go(0); },
    next:  function () { go((cur() || 0) + 1); },
    stop:  function () { clear(); }
  };

  // self-contained trigger: any [data-tour] element starts the tour
  document.addEventListener('click', function (e) {
    var t = e.target.closest ? e.target.closest('[data-tour]') : null;
    if (t) { e.preventDefault(); window.LuminaTour.start(); }
  });

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', resume);
  else resume();
})();
