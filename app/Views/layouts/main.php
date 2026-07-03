<?php $role = session('role') ?? null; $stage = session('stage') ?? '19-22'; ?>
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
        <a href="<?= base_url('demo/candidate-' . $stage) ?>" class="<?= $role==='candidate'?'active':'' ?>">Candidate</a>
        <a href="<?= base_url('demo/employer') ?>" class="<?= $role==='employer'?'active':'' ?>">Employer</a>
        <a href="<?= base_url('demo/university') ?>" class="<?= $role==='university'?'active':'' ?>">University</a>
      </div>
      <select id="stageSelect" class="stage-select" title="Life stage">
        <?php foreach (['16-18','19-22','23-28','26-28+'] as $s): ?>
          <option value="<?= $s ?>" <?= $stage===$s?'selected':'' ?>><?= $s ?></option>
        <?php endforeach; ?>
      </select>
      <div class="spacer"></div>
      <a href="#" class="btn btn-gold" data-tour="1">▶ Guided tour</a>
    </div>
  </header>

  <main class="wrap">
    <?= $this->renderSection('content') ?>
  </main>

  <div class="disclaimer">Simulated AI for demonstration. Lumina is decision support; people make the final decision.</div>
  <footer class="footer">
    Talentbank Tech Hackathon 2026 · Lumina v1.0 · AI Talent Intelligence Layer for Career OS
  </footer>

  <script src="<?= base_url('js/app.js') ?>"></script>
</body>
</html>
