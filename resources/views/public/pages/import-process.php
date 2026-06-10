<?php
/** @var \App\Core\View $this */
$this->extends('layouts/public');
$steps = t_arr('pages.process.steps');
?>
<?php $this->section('content'); ?>

<?= $this->partial('partials/page-hero', [
    'eyebrow'  => t('pages.process.eyebrow') ?: 'Import process',
    'title'    => t('pages.process.title'),
    'subtitle' => t('pages.process.subtitle'),
    'crumbs'   => [
        ['label' => t('vehicle.detail.breadcrumb.home'), 'url' => locale_url('/')],
        ['label' => t('pages.process.title'),            'url' => null],
    ],
]) ?>

<section class="kae-section">
    <div class="container">
        <div class="kae-step-grid">
            <?php foreach ($steps as $i => $s): ?>
                <div class="kae-step" data-reveal data-reveal-delay="<?= ($i * 100) ?>">
                    <div class="kae-step-num"><?= e((string) ($s['n'] ?? (string) ($i + 1))) ?></div>
                    <h3 class="kae-step-title"><?= e((string) ($s['title'] ?? '')) ?></h3>
                    <p class="kae-step-body"><?= e((string) ($s['body'] ?? '')) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-5" data-reveal>
            <a href="<?= e(locale_url('/vehicles')) ?>" class="btn btn-primary btn-lg">
                <?= e(t('pages.process.cta')) ?> →
            </a>
        </div>
    </div>
</section>
<?php $this->endSection(); ?>
