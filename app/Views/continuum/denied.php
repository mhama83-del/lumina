<?= $this->extend('continuum/_layout') ?>
<?= $this->section('content') ?>
<div class="card" style="border-color:var(--error)"><h2>Access denied</h2>
<p>You do not have permission to view this information.<?php if(!empty($reason)): ?> <span class="small muted">(<?= esc($reason) ?>)</span><?php endif; ?></p></div>
<?= $this->endSection() ?>
