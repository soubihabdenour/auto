<?php
/** @var \App\Core\View $this  @var array $testimonials */
$this->extends('layouts/public');
?>
<?php $this->section('content'); ?>

<!-- Hero -->
<section class="kae-section kae-section--soft text-center">
    <div class="container">
        <span class="kae-eyebrow"><?= e(t('pages.testimonials.eyebrow') ?: 'Customer stories') ?></span>
        <h1 class="kae-section-title"><?= e(t('pages.testimonials.title')) ?></h1>
        <p class="kae-section-subtitle mx-auto"><?= e(t('pages.testimonials.subtitle')) ?></p>
    </div>
</section>

<!-- Grid -->
<section class="kae-section">
    <div class="container">
        <?php if (empty($testimonials)): ?>
            <p class="text-center text-muted py-5"><?= e(t('pages.testimonials.empty')) ?></p>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($testimonials as $t): ?>
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="kae-testimonial-card h-100 p-4 border rounded-3 bg-white">
                            <div class="kae-stars mb-2 text-warning fs-5">
                                <?php for ($i = 0; $i < (int) ($t['rating'] ?? 5); $i++) echo '★'; ?>
                            </div>
                            <p class="mb-3 fs-6"><?= e((string) ($t['body'] ?? '')) ?></p>
                            <div class="d-flex align-items-center gap-3 mt-auto">
                                <?php if (! empty($t['avatar_path'])): ?>
                                    <img src="<?= e('/uploads/' . ltrim((string) $t['avatar_path'], '/')) ?>" alt="" class="rounded-circle" style="width:48px;height:48px;object-fit:cover">
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
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php $this->endSection(); ?>
