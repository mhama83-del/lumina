<?= $this->extend('continuum/_layout') ?>
<?= $this->section('content') ?>
<div class="pagehead"><h1>Access denied</h1></div>
<div class="card"><p style="margin:0">You don't have permission to view this information<?php if(!empty($reason)): ?> <span class="small faint">(<?= esc($reason) ?>)</span><?php endif; ?>. Consent is enforced server-side, per application and role version.</p></div>
<?= $this->endSection() ?>
