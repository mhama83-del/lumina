<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php helper('ui'); $first = $paths[0]; ?>

<section class="hero">
  <div class="section-label">Career Compass · <?= esc($profile['name'] ?? 'You') ?></div>
  <h1>Where you are. Where you can go. What's next.</h1>
  <p class="purpose">Pick a path, then add a skill and watch your readiness move.</p>
</section>

<section class="section" style="padding-top:6px">
  <?= lumina_journey('compass') ?>
  <?= lumina_note("Based on your portfolio, here are 3 real directions — and how ready you are for each.") ?>
</section>

<!-- Path cards -->
<section class="section">
  <div class="grid grid-3">
    <?php foreach ($paths as $i => $p): ?>
      <div class="card path-card <?= $i === 0 ? 'sel' : '' ?>" data-key="<?= esc($p['key']) ?>" role="button" tabindex="0"
           style="--pc:<?= $p['color'] ?>">
        <div class="row" style="justify-content:space-between">
          <h3 style="margin:0"><?= esc($p['title']) ?></h3>
          <span class="pill <?= $p['label']==='best'?'ok':($p['label']==='growth'?'nudge':'risk') ?>"><?= esc(ucfirst($p['label'])) ?> fit</span>
        </div>
        <div class="row" style="align-items:flex-end;gap:8px;margin-top:8px">
          <div style="font-family:var(--font-head);font-weight:800;font-size:30px;color:var(--text)"><?= (int)$p['readiness'] ?>%</div>
          <div class="muted" style="margin-bottom:5px">ready</div>
        </div>
        <p class="muted" style="margin:6px 0 0">
          <?php if ($p['gaps']): ?>Gap: <?= esc(implode(', ', array_map(fn($g)=>$g['label'],$p['gaps']))) ?><?php else: ?>No gaps — strong match.<?php endif; ?>
        </p>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- Detail: What-If + trajectory -->
<section class="section">
  <div class="grid grid-2">

    <div class="card">
      <div class="section-label" id="cPathLabel"><?= esc($first['title']) ?></div>
      <div class="donut-wrap" id="cDonut"><?= lumina_donut($first['readiness'], 'Readiness', $first['color']) ?></div>
      <div style="text-align:center;font-size:15px;margin:6px 0 14px" id="deltaTxt"><?= (int)$first['readiness'] ?>%</div>

      <h3 style="margin-bottom:8px">Add a skill — see it move</h3>
      <div id="gapList" class="stack"></div>
      <p class="purpose" style="margin-top:10px">Decision support only. This is your trajectory, not a guarantee.</p>
    </div>

    <div class="card">
      <div class="section-label">Your trajectory</div>
      <canvas id="trajChart" height="150"></canvas>
      <h3 style="margin:16px 0 8px">30 / 60 / 90-day plan</h3>
      <div id="planList" class="stack"></div>
    </div>

  </div>

  <div class="row" style="margin-top:18px">
    <a class="btn btn-gold btn-lg" href="<?= base_url('match') ?>">Find opportunities →</a>
    <a class="btn btn-ghost" href="<?= base_url('passport') ?>">← Back to portfolio</a>
  </div>
</section>

<!-- data + scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const PATHS = <?= json_encode(array_map(fn($p)=>[
  'key'=>$p['key'],'title'=>$p['title'],'readiness'=>$p['readiness'],
  'colorHex'=>$p['colorHex'],'gaps'=>$p['gaps'],'plan'=>$p['plan'],'traj'=>$p['traj']
], $paths)) ?>;
const WHATIF_URL = "<?= base_url('whatif') ?>";
let active = PATHS[0], chart = null;

function setDonut(pct, color){
  const wrap = document.getElementById('cDonut');
  const val = wrap.querySelector('.val'), txt = wrap.querySelector('.pct');
  const r = 45, c = 2*Math.PI*r;
  val.style.stroke = color;
  val.style.strokeDasharray = c.toFixed(1);
  val.style.strokeDashoffset = (c*(1-pct/100)).toFixed(1);
  txt.textContent = pct + '%';
}
function setDelta(before, after, delta){
  document.getElementById('deltaTxt').innerHTML =
    before + '% → <strong>' + after + '%</strong>' + (delta>0 ? ' <span class="gold">(+'+delta+')</span>' : '');
}
function buildGaps(p){
  const box = document.getElementById('gapList');
  if(!p.gaps.length){ box.innerHTML = '<p class="muted">No gaps — you are a strong match already.</p>'; return; }
  box.innerHTML = p.gaps.map(g =>
    '<label class="opt"><input type="checkbox" class="gap-cb" value="'+g.code+'">'+
    '<span class="opt-box">Add '+g.label+'</span></label>').join('');
}
function buildPlan(p){
  document.getElementById('planList').innerHTML = p.plan.map(s =>
    '<div class="ev"><strong class="gold">'+s.d+'</strong> — '+s.t+'</div>').join('');
}
function renderChart(p){
  const ctx = document.getElementById('trajChart').getContext('2d');
  const data = [p.traj.now, p.traj.d30, p.traj.d60, p.traj.d90];
  if(chart) chart.destroy();
  chart = new Chart(ctx, {
    type:'line',
    data:{ labels:['Now','30d','60d','90d'], datasets:[{ data, borderColor:p.colorHex,
      backgroundColor:'transparent', tension:.35, pointRadius:4, pointBackgroundColor:p.colorHex, borderWidth:3 }] },
    options:{ plugins:{legend:{display:false}}, scales:{
      y:{ min:0, max:100, grid:{color:'rgba(255,255,255,.06)'}, ticks:{color:'#9AA4B8'} },
      x:{ grid:{display:false}, ticks:{color:'#9AA4B8'} } } }
  });
}
function recompute(){
  const checked = [...document.querySelectorAll('.gap-cb:checked')].map(c=>c.value);
  const body = new URLSearchParams(); body.append('role', active.key);
  checked.forEach(c => body.append('add[]', c));
  fetch(WHATIF_URL, {method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'}, body})
    .then(r=>r.json())
    .then(w=>{ setDonut(w.after, active.colorHex); setDelta(w.before, w.after, w.delta); })
    .catch(()=>{});
}
function selectPath(key){
  active = PATHS.find(p=>p.key===key) || PATHS[0];
  document.querySelectorAll('.path-card').forEach(c=>c.classList.toggle('sel', c.dataset.key===active.key));
  document.getElementById('cPathLabel').textContent = active.title;
  setDonut(active.readiness, active.colorHex);
  setDelta(active.readiness, active.readiness, 0);
  buildGaps(active); buildPlan(active); renderChart(active);
}
document.addEventListener('click', e=>{
  const card = e.target.closest('.path-card'); if(card){ selectPath(card.dataset.key); }
  if(e.target.classList.contains('gap-cb')){ recompute(); }
});
document.addEventListener('change', e=>{ if(e.target.classList.contains('gap-cb')) recompute(); });
selectPath(PATHS[0].key);
</script>

<?= $this->endSection() ?>
