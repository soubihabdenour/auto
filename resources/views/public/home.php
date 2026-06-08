<?php
/**
 * @var \App\Core\View $this
 * @var string $tagline
 * @var array  $featured
 * @var array  $testimonials
 * @var array  $brands
 */
$this->extends('layouts/public');
$why_reasons   = t_arr('pages.why_korea.reasons');
$process_steps = t_arr('pages.process.steps');
$faq_items     = t_arr('home.faq.items');
$stats         = t_arr('home.stats.items');
$vs_korea      = t_arr('home.vs.korea');
$vs_local      = t_arr('home.vs.local');
?>
<?php $this->section('content'); ?>

<!-- ============================================================
     HERO — dark, full-bleed, large headline + dual CTA
     ============================================================ -->
<section class="kae-home-hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-9">
                <span class="kae-eyebrow"><?= e(t('home.hero.eyebrow')) ?></span>
                <h1 class="mb-3"><?= e(t('home.hero.headline')) ?></h1>
                <p class="lead text-white-50 mb-4"><?= e($tagline) ?></p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="<?= e(locale_url('/vehicles')) ?>" class="btn btn-primary btn-lg">
                        <?= e(t('common.cta.browse_cars')) ?> →
                    </a>
                    <a href="<?= e(locale_url('/request-vehicle')) ?>" class="btn btn-outline-light btn-lg">
                        <?= e(t('common.cta.request_vehicle')) ?>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container kae-home-hero-search mt-5">
        <?= $this->partial('partials/quick-search', ['brands' => $brands]) ?>
    </div>
</section>

<!-- ============================================================
     STATS STRIP — at-a-glance trust numbers
     ============================================================ -->
<section class="kae-stats-strip">
    <div class="container">
        <div class="kae-stats">
            <?php foreach ($stats as $s): ?>
                <div class="kae-stat">
                    <span class="kae-stat-value"><?= e((string) ($s['value'] ?? '')) ?></span>
                    <span class="kae-stat-label"><?= e((string) ($s['label'] ?? '')) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============================================================
     FEATURED VEHICLES
     ============================================================ -->
<section class="kae-section">
    <div class="container">
        <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
            <div>
                <span class="kae-eyebrow"><?= e(t('home.featured.eyebrow')) ?></span>
                <h2 class="kae-section-title"><?= e(t('home.featured.title')) ?></h2>
                <p class="kae-section-subtitle"><?= e(t('home.featured.subtitle')) ?></p>
            </div>
            <a href="<?= e(locale_url('/vehicles')) ?>" class="btn btn-outline-dark">
                <?= e(t('home.featured.view_all')) ?> →
            </a>
        </div>

        <?php if (empty($featured)): ?>
            <div class="kae-empty py-5 text-center">
                <p class="text-muted mb-0"><?= e(t('home.featured.empty')) ?></p>
            </div>
        <?php else: ?>
            <div class="row g-3 g-md-4">
                <?php foreach ($featured as $vehicle): ?>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <?= $this->partial('partials/vehicle-card', ['vehicle' => $vehicle]) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- ============================================================
     WHY US — 3-column feature grid (Label Studio rhythm)
     ============================================================ -->
<section class="kae-section kae-section--soft">
    <div class="container">
        <div class="text-center mb-5">
            <span class="kae-eyebrow"><?= e(t('home.why.eyebrow')) ?></span>
            <h2 class="kae-section-title"><?= e(t('home.why.title')) ?></h2>
            <p class="kae-section-subtitle mx-auto"><?= e(t('home.why.subtitle')) ?></p>
        </div>
        <div class="kae-feature-grid">
            <?php foreach (array_slice($why_reasons, 0, 4) as $r): ?>
                <div class="kae-feature">
                    <div class="kae-feature-icon"><?= e((string) ($r['icon'] ?? '')) ?></div>
                    <h3 class="kae-feature-title"><?= e((string) ($r['title'] ?? '')) ?></h3>
                    <p class="kae-feature-body"><?= e((string) ($r['body'] ?? '')) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============================================================
     HOW IT WORKS — numbered step grid
     ============================================================ -->
<section class="kae-section">
    <div class="container">
        <div class="text-center mb-5">
            <span class="kae-eyebrow"><?= e(t('home.process.eyebrow')) ?></span>
            <h2 class="kae-section-title"><?= e(t('home.process.title')) ?></h2>
            <p class="kae-section-subtitle mx-auto"><?= e(t('home.process.subtitle')) ?></p>
        </div>
        <div class="kae-step-grid">
            <?php foreach ($process_steps as $i => $s): ?>
                <div class="kae-step">
                    <div class="kae-step-num"><?= e((string) ($s['n'] ?? (string) ($i + 1))) ?></div>
                    <h3 class="kae-step-title"><?= e((string) ($s['title'] ?? '')) ?></h3>
                    <p class="kae-step-body"><?= e((string) ($s['body'] ?? '')) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="<?= e(locale_url('/import-process')) ?>" class="btn btn-outline-dark">
                <?= e(t('home.process.more')) ?> →
            </a>
        </div>
    </div>
</section>

<!-- ============================================================
     COMPARISON — Korea vs local market, two visual cards
     ============================================================ -->
<section class="kae-section kae-section--soft">
    <div class="container">
        <div class="text-center mb-5">
            <span class="kae-eyebrow"><?= e(t('home.vs.eyebrow')) ?></span>
            <h2 class="kae-section-title"><?= e(t('home.vs.title')) ?></h2>
            <p class="kae-section-subtitle mx-auto"><?= e(t('home.vs.subtitle')) ?></p>
        </div>
        <div class="kae-vs">
            <div class="kae-vs-card kae-vs-card--korea">
                <h3><?= e(t('home.vs.korea_title')) ?></h3>
                <ul class="kae-vs-list">
                    <?php foreach ($vs_korea as $item): ?>
                        <li><?= e((string) $item) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="kae-vs-card kae-vs-card--local">
                <h3><?= e(t('home.vs.local_title')) ?></h3>
                <ul class="kae-vs-list">
                    <?php foreach ($vs_local as $item): ?>
                        <li><?= e((string) $item) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <div class="text-center mt-4">
            <a href="<?= e(locale_url('/why-korea')) ?>" class="btn btn-outline-dark">
                <?= e(t('home.why.more')) ?> →
            </a>
        </div>
    </div>
</section>

<!-- ============================================================
     TESTIMONIALS — carousel
     ============================================================ -->
<?php if (! empty($testimonials)): ?>
<section class="kae-section">
    <div class="container">
        <div class="text-center mb-5">
            <span class="kae-eyebrow"><?= e(t('home.testimonials.eyebrow')) ?></span>
            <h2 class="kae-section-title"><?= e(t('home.testimonials.title')) ?></h2>
            <p class="kae-section-subtitle mx-auto"><?= e(t('home.testimonials.subtitle')) ?></p>
        </div>
        <div id="kae-testimonial-carousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="6000">
            <div class="carousel-inner">
                <?php foreach (array_chunk($testimonials, 3) as $i => $chunk): ?>
                    <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                        <div class="row g-3 px-md-5">
                            <?php foreach ($chunk as $tst): ?>
                                <div class="col-md-4">
                                    <div class="kae-testimonial-card h-100 p-4 bg-white border rounded-3">
                                        <div class="text-warning mb-2 fs-4">
                                            <?php for ($s = 0; $s < (int) ($tst['rating'] ?? 5); $s++) echo '★'; ?>
                                        </div>
                                        <p class="mb-3 lead fs-6"><?= e((string) ($tst['body'] ?? '')) ?></p>
                                        <div class="fw-bold"><?= e((string) ($tst['customer_name'] ?? '')) ?></div>
                                        <?php if (! empty($tst['customer_city'])): ?>
                                            <div class="text-muted small"><?= e((string) $tst['customer_city']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if (count(array_chunk($testimonials, 3)) > 1): ?>
                <button class="carousel-control-prev" type="button" data-bs-target="#kae-testimonial-carousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#kae-testimonial-carousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                </button>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ============================================================
     FAQ — accordion
     ============================================================ -->
<section class="kae-section kae-section--soft">
    <div class="container">
        <div class="text-center mb-5">
            <span class="kae-eyebrow"><?= e(t('home.faq.eyebrow')) ?></span>
            <h2 class="kae-section-title"><?= e(t('home.faq.title')) ?></h2>
        </div>
        <div class="accordion col-md-9 mx-auto" id="kae-faq">
            <?php foreach ($faq_items as $i => $item): ?>
                <div class="accordion-item">
                    <h3 class="accordion-header" id="faq-h-<?= $i ?>">
                        <button class="accordion-button collapsed fw-semibold" type="button"
                                data-bs-toggle="collapse" data-bs-target="#faq-c-<?= $i ?>">
                            <?= e((string) ($item['q'] ?? '')) ?>
                        </button>
                    </h3>
                    <div id="faq-c-<?= $i ?>" class="accordion-collapse collapse"
                         data-bs-parent="#kae-faq">
                        <div class="accordion-body text-muted"><?= e((string) ($item['a'] ?? '')) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============================================================
     FINAL CTA — branded card on a light background
     ============================================================ -->
<section class="kae-section">
    <div class="container">
        <div class="kae-cta-card">
            <div class="row align-items-center justify-content-between gx-4">
                <div class="col-lg-7">
                    <span class="kae-eyebrow"><?= e(t('home.final_cta.eyebrow')) ?></span>
                    <h2><?= e(t('home.final_cta.title')) ?></h2>
                    <p><?= e(t('home.final_cta.subtitle')) ?></p>
                </div>
                <div class="col-lg-auto d-flex flex-wrap gap-2">
                    <a href="<?= e(locale_url('/request-vehicle')) ?>" class="btn btn-primary btn-lg">
                        <?= e(t('home.final_cta.primary')) ?>
                    </a>
                    <a href="<?= e(locale_url('/contact')) ?>" class="btn btn-outline-light btn-lg">
                        <?= e(t('home.final_cta.secondary')) ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php $this->endSection(); ?>
