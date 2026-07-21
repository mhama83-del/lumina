/* ============================================================
   LUMINA — Guided Demo (Fasa 5 · 10 canonical steps, ~3 min)
   Problem → Discover → Read → Trust → Explore → Grow →
   Match → Prepare → Support → Vision
   One step = one UI highlight, one short line, one Continue.
   Uses the preloaded Aiman journey. No live typing.
   Cross-page: persists step in sessionStorage, resumes after nav.
   ============================================================ */
(function () {
  var ROOT = new URL(BASE).pathname.replace(/\/$/, '');
  function path() {
    var p = location.pathname;
    if (ROOT && p.indexOf(ROOT) === 0) p = p.slice(ROOT.length);
    return p || '/';
  }
  function href(target) { return BASE.replace(/\/$/, '') + target; }

  var STEPS = [
    { chapter:'Problem', url:'/', sel:'#landingHero',
      title:'Potential is hard to read',
      body:'Young talent has potential, but a static resume does not always make it readable.' },

    { chapter:'Discover', url:'/start', nav:'/start', sel:'#entryChoices',
      title:'Start from anywhere',
      body:'Lumina can begin with a resume — or with real activities and experience.' },

    { chapter:'Read', url:'/passport', nav:'/start/sample', sel:'#passportHeader',
      title:'A readable Living Portfolio',
      body:'Scattered evidence becomes a readable Living Portfolio.' },

    { chapter:'Trust', url:'/passport', sel:'#cvEvidenceCheck',
      title:'Every signal shows its evidence',
      body:'Every signal shows supporting evidence and what can be strengthened.' },

    { chapter:'Explore', url:'/compass', nav:'/compass', sel:'#recommendedPaths',
      title:'Practical directions',
      body:'Career Compass turns the profile into practical directions.' },

    { chapter:'Grow', url:'/compass', sel:'#growthPathway',
      title:'Learn, Build, Prove, Apply',
      body:'The next step is to Learn, Build, Prove and Apply.' },

    { chapter:'Match', url:'/match', nav:'/match', sel:'#smartMatchResults',
      title:'Roles that fit',
      body:'Smart Matching shows roles that are Ready Now, Reachable or Longer Path.' },

    { chapter:'Prepare', url:'/match', sel:"#interviewPreparation",
      title:'Prepare with real evidence',
      body:'Preparation comes from the candidate\u2019s actual skills and evidence.' },

    { chapter:'Support', url:'/university/interventions', nav:'/university/interventions', sel:'#supportPriority',
      title:'Where Support Is Needed',
      body:'Universities see Where Support Is Needed before students are left behind.' },

    { chapter:'Vision', url:'/', nav:'/', sel:'#luminaVision',
      title:'One connected platform',
      body:'Lumina connects candidate growth, employer conversations and university support inside Talentbank Career OS.' }
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
      '<div class="prog">Step ' + (i + 1) + ' of ' + STEPS.length + ' \u00b7 ' + (step.chapter || '') + '</div>' +
      '<h4>' + step.title + '</h4>' +
      '<p class="muted" style="margin:4px 0 0">' + step.body + '</p>' +
      '<div class="row" style="justify-content:flex-end">' +
        '<button class="btn btn-ghost" id="tourSkip">Skip</button>' +
        (i > 0 ? '<button class="btn btn-ghost" id="tourPrev">Back</button>' : '') +
        '<button class="btn btn-primary" id="tourNext">' + (i === STEPS.length - 1 ? 'Finish' : 'Continue') + '</button>' +
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

  document.addEventListener('click', function (e) {
    var t = e.target.closest ? e.target.closest('[data-tour]') : null;
    if (t) { e.preventDefault(); window.LuminaTour.start(); }
  });

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', resume);
  else resume();
})();
