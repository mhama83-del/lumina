<?php /** @var \Config\Continuum $product */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= esc($product->productName) ?> — <?= esc($title ?? $product->productDescriptor) ?></title>
<link rel="stylesheet" href="/css/continuum.css">
</head>
<body>
<header class="topbar">
  <span class="brand"><?= esc($product->productName) ?></span>
  <span class="status small"><?= esc($product->adoptionStatus) ?></span>
  <nav class="nav" style="margin-left:auto">
    <?php if (($workspace ?? '') === 'candidate'): ?>
      <a href="/candidate/home">Home</a><a href="/candidate/evidence">My Evidence</a><a href="/candidate/roles/data-analyst">Role Context</a>
    <?php elseif (($workspace ?? '') === 'employer'): ?>
      <a href="/employer/roles">Roles</a>
    <?php elseif (($workspace ?? '') === 'operator'): ?>
      <a href="/operator/control-tower">Control Tower</a>
    <?php elseif (($workspace ?? '') === 'university'): ?>
      <a href="/university/cohorts/1">Cohort</a>
    <?php endif; ?>
    <?php if (! empty($demoMode)): ?><a href="/demo/scenarios">Switch persona</a><?php endif; ?>
  </nav>
</header>
<div class="container">
  <?php if (! empty($demoMode) && isset($ctx)): ?>
    <div class="demobar">🎬 <strong>Demo mode</strong> — viewing as <strong><?= esc($ctx->identityKey) ?></strong>
      (<?= esc($ctx->role->label()) ?>). The Scenario Switcher only swaps who you view; it is not an
      authorisation mechanism. All data is <em>synthetic_fixture</em>.</div>
  <?php endif; ?>
  <?php if (session('error')): ?><div class="card" style="border-color:var(--error)"><?= esc(session('error')) ?></div><?php endif; ?>
  <?php if (session('ok')): ?><div class="card" style="border-color:var(--success)"><?= esc(session('ok')) ?></div><?php endif; ?>
  <?= $this->renderSection('content') ?>
</div>
</body>
</html>
