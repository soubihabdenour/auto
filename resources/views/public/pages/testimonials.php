<?php
/** @var \App\Core\View $this  @var array $testimonials */
$this->extends('layouts/public');
?>
<?php $this->section('content'); ?>

<?= $this->partial('partials/page-hero', [
    'eyebrow'  => t('pages.testimonials.eyebrow') ?: 'Customer stories',
    'title'    => t('pages.testimonials.title'),
    'subtitle' => t('pages.testimonials.subtitle'),
    'crumbs'   => [
        ['label' => t('vehicle.detail.breadcrumb.home'), 'url' => locale_url('/')],
        ['label' => t('pages.testimonials.title'),       'url' => null],
    ],
]) ?>

<section class="kae-section">
    <div class="container">
        <?php if (empty($testimonials)): ?>
            <p class="text-center text-muted py-5"><?= e(t('pages.testimonials.empty')) ?></p>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($testimonials as $i => $t): ?>
                    <div class="col-12 col-md-6 col-lg-4"
                         data-reveal data-reveal-delay="<?= (($i % 6) * 80) ?>">
                        <article class="kae-testimonial-card h-100">
                            <div class="kae-stars mb-2 text-warning fs-5">
                                <?php for ($s = 0; $s < (int) ($t['rating'] ?? 5); $s++) echo '★'; ?>
                            </div>
                            <p class="lead mb-3"><?= e((string) ($t['body'] ?? '')) ?></p>
                            <div class="d-flex align-items-center gap-3 mt-auto pt-3 border-top">
                                <?php if (! empty($t['avatar_path'])): ?>
                                    <img src="<?= e('/uploads/' . ltrim((string) $t['avatar_path'], '/')) ?>" alt="" class="rounded-circle" style="width:44px;height:44px;object-fit:cover">
                                <?php endif; ?>
                                <div>
                                    <div class="fw-bold"><?= e((string) ($t['customer_name'] ?? '')) ?></div>
                                    <?php if (! empty($t['customer_city'])): ?>
                                        <div class="text-muted small"><?= e((string) $t['customer_city']) ?></div>
                                    <?php endif; ?>
                                    <?php if (! empty($t['vehicle_purchased'])): ?>
                                        <div class="text-muted small">— <?= e((string) $t['vehicle_purchased']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php $this->endSection(); ?>
