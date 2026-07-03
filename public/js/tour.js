/* ============================================================
   LUMINA — Guided Demo Mode (Fasa 7)
   Cross-page 10-step tour. Persists step in sessionStorage and
   resumes after navigation. Page stays interactive (dim is visual).
   ============================================================ */
(function () {
  var ROOT = new URL(BASE).pathname.replace(/\/$/, ''); // '' when at domain root
  function path() {
    var p = location.pathname;
    if (ROOT && p.indexOf(ROOT) === 0) p = p.slice(ROOT.length);
    return p || '/';
  }
  function href(target) { return BASE.replace(/\/$/, '') + target; }

  // step.url = page it belongs to; step.nav = URL to navigate to reach it (optional)
  var STEPS = [
    { url: '/',           sel: '.hero',        title: 'Meet Lumina',                body: "CareerOS intelligence for every student. Let's follow Aiman, 19 — with no resume." },
    { url: '/start',      nav: '/start',       sel: '.grid',        title: 'Start from nothing',        body: 'No resume? Begin with a Work Animal, a transcript, or just 5 questions.' },
    { url: '/passport',   nav: '/start/sample',sel: '.donut-wrap',  title: 'A profile from nothing',    body: 'Lumina inferred skills he never listed — and scored his readiness.' },
    { url: '/passport',   sel: '.grid',        title: 'Inferred skills',           body: 'Dashed chips are skills Lumina inferred from his evidence. Tap “Why this score?” anytime.' },
    { url: '/compass',    nav: '/compass',     sel: '.path-card',   title: 'Three real directions',     body: 'Each path shows how ready he is — and exactly what gap to close.' },
    { url: '/compass',    sel: '#gapList',     title: 'Watch it move',             body: 'Tick a skill — his readiness jumps. That is trajectory, not just matching.' },
    { url: '/match',      nav: '/match',       sel: '.grid',        title: 'Opportunities that fit',    body: 'Best, Growth and Stretch matches — each with the reason why.' },
    { url: '/employer',   nav: '/employer',    sel: '.stack',       title: 'Employers see why',         body: 'Ranked candidates with reasons and ready interview questions.' },
    { url: '/university', nav: '/university',  sel: '.grid',        title: 'Universities see the cohort',body: 'Who is nearly ready — and the one intervention that unlocks them.' },
    { url: '/',           nav: '/',            sel: '.hero',        title: 'One connected platform',    body: 'From no resume to a direction, a match, and a cohort signal. That is Lumina.' }
  ];

  var KEY = 'lumina_tour';
  var cur = function () { var v = sessionStorage.getItem(KEY); return v === null ? null : parseInt(v, 10); };
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
    if (el) {
      el.classList.add('tour-target');
      el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    var dim = document.createElement('div'); dim.id = 'tourDim'; dim.className = 'tour-dim';
    document.body.appendChild(dim);

    var pop = document.createElement('div'); pop.id = 'tourPop'; pop.className = 'tour-pop';
    pop.innerHTML =
      '<div class="prog">Step ' + (i + 1) + ' / ' + STEPS.length + '</div>' +
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
    start: function () { go(0); },
    next:  function () { go((cur() || 0) + 1); },
    stop:  function () { clear(); }
  };

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', resume);
  else resume();
})();
