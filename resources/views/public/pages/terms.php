<?php
/** @var \App\Core\View $this */
$this->extends('layouts/public');
$sections = t_arr('pages.terms.sections');
?>
<?php $this->section('content'); ?>

<?= $this->partial('partials/page-hero', [
    'eyebrow'  => t('pages.terms.eyebrow'),
    'title'    => t('pages.terms.title'),
    'subtitle' => t('pages.terms.last_updated', ['date' => '2026-06-08']),
    'crumbs'   => [
        ['label' => t('vehicle.detail.breadcrumb.home'), 'url' => locale_url('/')],
        ['label' => t('pages.terms.title'),              'url' => null],
    ],
]) ?>

<section class="kae-section">
    <div class="container">
        <div class="kae-prose" data-reveal>
            <?php foreach ($sections as $i => $sec): ?>
                <h2><?= ($i + 1) . '. ' . e((string) ($sec['title'] ?? '')) ?></h2>
                <p style="white-space: pre-line"><?= e((string) ($sec['body'] ?? '')) ?></p>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php $this->endSection(); ?>
