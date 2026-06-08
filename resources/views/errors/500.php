<?php
/** @var \App\Core\View $this  @var int $status */
$this->extends('layouts/public');
?>
<?php $this->section('content'); ?>
<section class="container py-5 text-center">
    <p class="text-uppercase text-muted small mb-2"><?= e((string) ($status ?? 500)) ?></p>
    <h1 class="display-5 fw-bold mb-3"><?= e(t('common.errors.500.title')) ?></h1>
    <p class="text-muted mb-4"><?= e(t('common.errors.500.subtitle')) ?></p>
    <a href="<?= e(locale_url('/')) ?>" class="btn btn-primary"><?= e(t('common.errors.500.cta')) ?></a>
</section>
<?php $this->endSection(); ?>
