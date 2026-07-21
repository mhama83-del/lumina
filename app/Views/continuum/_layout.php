<?php
/** @var \Config\Continuum $product */
$ws = $workspace ?? 'public';
$isApp = in_array($ws, ['candidate','employer','operator','university'], true);
$cur = '/' . uri_string();
$nav = [
  'candidate'  => [['/candidate/home','Home'],['/candidate/evidence','My evidence'],['/candidate/roles/data-analyst','Role context']],
  'employer'   => [['/employer/roles','Roles & queues']],
  'operator'   => [['/operator/control-tower','Control tower']],
  'university'  => [['/university/cohorts/1','Cohort signal']],
][$ws] ?? [];
$idk = $ctx->identityKey ?? 'cx';
$parts = explode('_', $idk);
$initials = strtoupper(substr($idk,0,1) . (isset($parts[1][0]) ? $parts[1][0] : ''));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= esc($product->productName) ?> — <?= esc($title ?? $product->productDescriptor) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="/css/continuum.css">
</head>
<body>
<header class="topbar">
  <a class="brand" href="/"><span class="mark"></span><?= esc($product->productName) ?></a>
  <span class="brand-pill"><?= esc($product->adoptionStatus) ?></span>
  <div class="topbar-right">
    <?php if (isset($ctx) && ! empty($demoMode)): ?>
      <span class="persona-tag"><span class="avatar"><?= esc($initials) ?></span><?= esc($ctx->identityKey) ?></span>
    <?php endif; ?>
    <?php if (! empty($demoMode)): ?><a class="btn-switch" href="/demo/scenarios">Switch persona</a><?php endif; ?>
  </div>
</header>

<?php if (! empty($demoMode) && isset($ctx)): ?>
  <div class="demostrip"><span class="dot-live"></span>
    <span class="tag">Demo</span> viewing as <strong><?= esc($ctx->identityKey) ?></strong> ·
    <?= esc($ctx->role->label()) ?>. Switching persona swaps the view only — it is not authorisation.
    All data is <span class="mono">synthetic_fixture</span>.</div>
<?php endif; ?>

<?php if ($isApp): ?>
<div class="shell">
  <aside class="sidebar">
    <div class="side-label"><?= esc(ucfirst($ws)) ?> workspace</div>
    <?php foreach ($nav as [$href,$label]): $active = ($cur === $href || str_starts_with($cur, $href)) ? ' active' : ''; ?>
      <a class="side-link<?= $active ?>" href="<?= esc($href) ?>"><span class="ic">›</span><?= esc($label) ?></a>
    <?php endforeach; ?>
    <div class="side-foot">Continuum · proposed for Talentbank.<br>Passport link is a mock adapter.</div>
  </aside>
  <main class="main">
    <?php if (session('error')): ?><div class="banner err"><?= esc(session('error')) ?></div><?php endif; ?>
    <?php if (session('ok')): ?><div class="banner ok"><?= esc(session('ok')) ?></div><?php endif; ?>
    <?= $this->renderSection('content') ?>
  </main>
</div>
<?php else: ?>
<div class="marketing">
  <?php if (session('error')): ?><div class="banner err"><?= esc(session('error')) ?></div><?php endif; ?>
  <?php if (session('ok')): ?><div class="banner ok"><?= esc(session('ok')) ?></div><?php endif; ?>
  <?= $this->renderSection('content') ?>
</div>
<?php endif; ?>

<footer class="foot">Continuum — Evidence-to-Outcome Career Operating Layer · proposed for Talentbank ·
  all figures are synthetic fixtures for demonstration.</footer>
</body>
</html>
