<?php
/** @var \App\Core\View $this */
$this->extends('layouts/public');
$reasons    = (array) t_arr('pages.why_korea.reasons');
$comparison = (array) t_arr('pages.why_korea.comparison.rows');
?>
<?php $this->section('content'); ?>

<?= $this->partial('partials/page-hero', [
    'eyebrow'  => t('pages.why_korea.eyebrow') ?: 'Why Korea',
    'title'    => t('pages.why_korea.title'),
    'subtitle' => t('pages.why_korea.subtitle'),
    'crumbs'   => [
        ['label' => t('vehicle.detail.breadcrumb.home'), 'url' => locale_url('/')],
        ['label' => t('pages.why_korea.title'),          'url' => null],
    ],
]) ?>

<section class="kae-section">
    <div class="container">
        <div class="kae-feature-grid">
            <?php foreach ($reasons as $i => $r): ?>
                <div class="kae-feature" data-reveal data-reveal-delay="<?= ($i * 100) ?>">
                    <div class="kae-feature-icon"><?= e((string) ($r['icon'] ?? '')) ?></div>
                    <h3 class="kae-feature-title"><?= e((string) ($r['title'] ?? '')) ?></h3>
                    <p class="kae-feature-body"><?= e((string) ($r['body'] ?? '')) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Comparison table -->
<?php if ($comparison): ?>
<section class="kae-section kae-section--soft">
    <div class="container">
        <div class="mb-5">
            <span class="kae-eyebrow"><?= e(t('pages.why_korea.compare_eyebrow') ?: 'Side by side') ?></span>
            <h2 class="kae-section-title"><?= e(t('pages.why_korea.comparison.title')) ?></h2>
        </div>
        <div class="table-responsive">
            <table class="table table-striped bg-white align-middle rounded">
                <thead>
                    <tr>
                        <?php foreach ((array) $comparison[0] as $h): ?>
                            <th class="bg-dark text-white py-3 px-3 fw-bold"><?= e((string) $h) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($comparison, 1) as $row): ?>
                        <tr>
                            <?php foreach ((array) $row as $i => $cell): ?>
                                <td class="py-3 px-3 <?= $i === 0 ? 'fw-semibold' : '' ?>"><?= e((string) $cell) ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Quote block + CTA -->
<section class="kae-section text-center">
    <div class="container">
        <blockquote class="kae-quote display-6 fw-light fst-italic col-md-8 mx-auto">
            <?= e(t('pages.why_korea.quote')) ?>
        </blockquote>
        <a href="<?= e(locale_url('/vehicles')) ?>" class="btn btn-primary btn-lg mt-4">
            <?= e(t('pages.why_korea.cta')) ?> →
        </a>
    </div>
</section>
<?php $this->endSection(); ?>
