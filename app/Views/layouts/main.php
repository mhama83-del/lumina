<?php $role = session('role') ?? null; $stage = session('stage') ?? '19-22';
// Nav active ikut URI semasa (bukan session yg melekat).
$__uri = service('uri')->getSegment(1) ?? '';
$navCtx = '';
if (in_array($__uri, ['start','resume','passport','compass','onboard'], true)) $navCtx = 'candidate';
elseif ($__uri === 'employer') $navCtx = 'employer';
elseif ($__uri === 'university') $navCtx = 'university';
elseif ($__uri === 'demo') { $seg2 = service('uri')->getSegment(2) ?? ''; if (strpos($seg2,'candidate')===0) $navCtx='candidate'; elseif ($seg2==='employer') $navCtx='employer'; elseif ($seg2==='university') $navCtx='university'; }
else $navCtx = $role; // fallback ke session utk halaman lain
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= esc($title ?? 'Lumina — AI Talent Intelligence Layer') ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
  <script>const BASE = "<?= rtrim(base_url(), '/') . '/' ?>";</script>
</head>
<body>
  <header class="topbar">
    <div class="inner">
      <a href="<?= base_url('/') ?>" class="brand">Lumina<span class="dot">.</span></a>
      <div class="role-switch">
        <a href="<?= base_url('demo/candidate-' . $stage) ?>" class="<?= $navCtx==='candidate'?'active':'' ?>">Candidate</a>
        <a href="<?= base_url('demo/employer') ?>" class="<?= $navCtx==='employer'?'active':'' ?>">Employer</a>
        <a href="<?= base_url('demo/university') ?>" class="<?= $navCtx==='university'?'active':'' ?>">University</a>
      </div>
      <a href="<?= base_url('how-it-works') ?>" style="color:var(--muted);text-decoration:none;font-size:14px;margin-left:10px" title="System design">How it works</a>
      <a href="<?= base_url('graph') ?>" style="color:var(--muted);text-decoration:none;font-size:14px;margin-left:10px" title="Lumina Graph">Graph</a>
      <div class="spacer"></div>
      <a href="#" class="btn btn-ghost" data-tour="1">▶ Guided tour</a>
    </div>
  </header>
  <main class="wrap">
    <?= $this->renderSection('content') ?>
  </main>
  <div class="disclaimer">Simulated AI for demonstration. Lumina is decision support; people make the final decision.</div>
  <footer class="footer">
    Talentbank Tech Hackathon 2026 · Lumina v2.0 · AI Talent Intelligence Layer for Career OS
  </footer>
  <div class="drawer-backdrop" id="gBackdrop"></div>
  <aside class="drawer" id="gDrawer" aria-label="Details">
    <div class="row" style="justify-content:space-between">
      <h3 id="gTitle">Why</h3>
      <button class="btn btn-ghost" data-gclose="1">✕</button>
    </div>
    <div id="gBody"></div>
  </aside>
  <script src="<?= base_url('js/app.js') ?>"></script>
  <script src="<?= base_url('js/tour.js') ?>"></script>
</body>
</html>
