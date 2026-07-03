/* Lumina front-end interactions (Fasa 0-2) */
(function () {
  // Stage selector -> one-click switch
  document.addEventListener('change', function (e) {
    if (e.target && e.target.id === 'stageSelect') {
      var stage = e.target.value;
      window.location.href = BASE + 'demo/candidate-' + stage;
    }
  });

  // Guided tour button placeholder (Driver.js wired in Fasa 7)
  document.addEventListener('click', function (e) {
    var t = e.target.closest('[data-tour]');
    if (t) {
      e.preventDefault();
      if (window.LuminaTour) { window.LuminaTour.start(); }
      else { window.location.href = BASE + 'demo/candidate-19-22'; } // fallback: start the golden path
    }
  });
})();

/* ===== Fasa 3: tabs + Why drawer ===== */
document.addEventListener('click', function (e) {
  // Tabs
  var tab = e.target.closest('.tab[data-tab]');
  if (tab) {
    var group = tab.closest('[data-tabs]');
    group.querySelectorAll('.tab').forEach(function (t) { t.classList.remove('active'); });
    group.querySelectorAll('.tabpanel').forEach(function (p) { p.classList.remove('active'); });
    tab.classList.add('active');
    var panel = document.getElementById(tab.getAttribute('data-tab'));
    if (panel) panel.classList.add('active');
  }
  // Why drawer open
  if (e.target.closest('[data-why]')) {
    document.getElementById('whyDrawer').classList.add('open');
    document.getElementById('whyBackdrop').classList.add('open');
  }
  // Why drawer close
  if (e.target.closest('[data-close]') || e.target.id === 'whyBackdrop') {
    var d = document.getElementById('whyDrawer'); var b = document.getElementById('whyBackdrop');
    if (d) d.classList.remove('open'); if (b) b.classList.remove('open');
  }
});

/* ===== Fasa 5: reusable global drawer ===== */
document.addEventListener('click', function (e) {
  var t = e.target.closest('[data-drawer]');
  if (t) {
    document.getElementById('gTitle').textContent = t.getAttribute('data-title') || 'Details';
    document.getElementById('gBody').innerHTML = t.getAttribute('data-body') || '';
    document.getElementById('gDrawer').classList.add('open');
    document.getElementById('gBackdrop').classList.add('open');
  }
  if (e.target.closest('[data-gclose]') || e.target.id === 'gBackdrop') {
    document.getElementById('gDrawer').classList.remove('open');
    document.getElementById('gBackdrop').classList.remove('open');
  }
});
