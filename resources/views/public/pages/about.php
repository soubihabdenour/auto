<?php
/** @var \App\Core\View $this */
$this->extends('layouts/public');
$values = t_arr('pages.about.values');
?>
<?php $this->section('content'); ?>

<?= $this->partial('partials/page-hero', [
    'eyebrow'  => t('pages.about.eyebrow') ?: 'About us',
    'title'    => t('pages.about.title'),
    'subtitle' => t('pages.about.subtitle'),
    'crumbs'   => [
        ['label' => t('vehicle.detail.breadcrumb.home'), 'url' => locale_url('/')],
        ['label' => t('pages.about.title'),              'url' => null],
    ],
]) ?>

<section class="kae-section">
    <div class="container">
        <div class="kae-prose" data-reveal>
            <p class="lead"><?= e(t('pages.about.body')) ?></p>
        </div>
    </div>
</section>

<section class="kae-section kae-section--soft">
    <div class="container">
        <div class="text-center mb-5" data-reveal>
            <span class="kae-eyebrow"><?= e(t('pages.about.values_eyebrow') ?: 'What we stand for') ?></span>
            <h2 class="kae-section-title"><?= e(t('pages.about.values_title')) ?></h2>
        </div>
        <div class="kae-feature-grid">
            <?php foreach ($values as $i => $v): ?>
                <div class="kae-feature" data-reveal data-reveal-delay="<?= ($i * 100) ?>">
                    <h3 class="kae-feature-title"><?= e((string) ($v['title'] ?? '')) ?></h3>
                    <p class="kae-feature-body"><?= e((string) ($v['body'] ?? '')) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php $this->endSection(); ?>
