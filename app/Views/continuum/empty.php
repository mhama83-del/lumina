<?= $this->extend('continuum/_layout') ?>
<?= $this->section('content') ?>
<div class="empty"><?= esc($message ?? 'Nothing here yet.') ?></div>
<?= $this->endSection() ?>
