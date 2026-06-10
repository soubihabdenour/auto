<?php
/**
 * @var \App\Core\View $this
 * @var array  $vehicle
 * @var array  $images
 * @var array  $videos
 * @var array|null $inspection
 * @var array  $similar
 * @var array  $estimate
 * @var float  $fx_rate
 * @var string $wa_link
 * @var string $vehicle_url
 * @var array  $json_ld
 * @var string|null $meta_desc
 */
$this->extends('layouts/public');

$locale     = current_locale();
$alt_lang   = 'alt_' . $locale;
$notes_lang = 'notes_' . $locale;
?>
<?php $this->section('head_extras'); ?>
    <?php if (! empty($meta_desc)): ?>
        <meta name="description" content="<?= e((string) $meta_desc) ?>">
    <?php endif; ?>
    <meta property="og:type" content="product">
    <meta property="og:title" content="<?= e((string) $vehicle['title']) ?>">
    <?php if (! empty($meta_desc)): ?>
        <meta property="og:description" content="<?= e((string) $meta_desc) ?>">
    <?php endif; ?>
    <meta property="og:url" content="<?= e($vehicle_url) ?>">
    <?php if (! empty($images[0]['path'])): ?>
        <meta property="og:image" content="<?= e(url(image_url((string) $images[0]['path']))) ?>">
    <?php endif; ?>
    <link rel="canonical" href="<?= e($vehicle_url) ?>">
    <script type="application/ld+json">
        <?= json_encode($json_ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
    </script>
<?php $this->endSection(); ?>

<?php $this->section('content'); ?>

<?php
    $cover     = $images[0] ?? null;
    $coverUrl  = $cover ? image_url((string) $cover['path']) : null;
    $status    = (string) ($vehicle['status'] ?? 'available');
    $reservable = $status === 'available';
?>

<!-- ============ CINEMATIC HERO ============ -->
<section class="kae-vd-hero" <?= $coverUrl ? 'style="--vd-cover: url(\'' . e($coverUrl) . '\')"' : '' ?>>
    <div class="kae-vd-hero-bg" aria-hidden="true"></div>

    <div class="container kae-vd-hero-inner">
        <nav aria-label="breadcrumb" class="kae-vd-crumbs">
            <a href="<?= e(locale_url('/')) ?>"><?= e(t('vehicle.detail.breadcrumb.home')) ?></a>
            <span aria-hidden="true">/</span>
            <a href="<?= e(locale_url('/vehicles')) ?>"><?= e(t('vehicle.detail.breadcrumb.vehicles')) ?></a>
            <span aria-hidden="true">/</span>
            <span aria-current="page"><?= e((string) $vehicle['title']) ?></span>
        </nav>

        <div class="kae-vd-hero-grid">
            <div class="kae-vd-hero-text">
                <span class="kae-eyebrow" data-reveal>
                    <?= e($vehicle['year'] . ' · ' . $vehicle['brand_name']) ?>
                </span>
                <h1 class="kae-vd-hero-title" data-reveal data-reveal-delay="120">
                    <?= e((string) $vehicle['title']) ?>
                </h1>

                <ul class="kae-vd-hero-specs" data-reveal data-reveal-delay="240">
                    <li><span class="kae-vd-hero-specs-l"><?= e(t('vehicle.specs.mileage')) ?></span>
                        <span class="kae-vd-hero-specs-v"><?= e(format_mileage((int) $vehicle['mileage_km'])) ?></span></li>
                    <li><span class="kae-vd-hero-specs-l"><?= e(t('vehicle.specs.fuel')) ?></span>
                        <span class="kae-vd-hero-specs-v"><?= e(t('vehicle.fuel.' . $vehicle['fuel_type'])) ?></span></li>
                    <li><span class="kae-vd-hero-specs-l"><?= e(t('vehicle.specs.transmission')) ?></span>
                        <span class="kae-vd-hero-specs-v"><?= e(t('vehicle.transmission.' . $vehicle['transmission'])) ?></span></li>
                    <?php if (! empty($vehicle['drivetrain'])): ?>
                        <li><span class="kae-vd-hero-specs-l"><?= e(t('vehicle.specs.drivetrain')) ?></span>
                            <span class="kae-vd-hero-specs-v"><?= e(t('vehicle.drivetrain.' . $vehicle['drivetrain'])) ?></span></li>
                    <?php endif; ?>
                </ul>

                <div class="kae-vd-hero-cta" data-reveal data-reveal-delay="360">
                    <?php if ($reservable): ?>
                        <a href="<?= e(locale_url('/vehicles/' . $vehicle['slug'] . '/reserve')) ?>" class="btn btn-primary btn-lg">
                            <?= e(t('vehicle.detail.cta.reserve')) ?>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="margin-inline-start: 6px;"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                        </a>
                    <?php else: ?>
                        <button type="button" class="btn btn-outline-light btn-lg" disabled>
                            <?= e(t('vehicle.detail.cta.reserve_unavailable')) ?>
                        </button>
                    <?php endif; ?>
                    <button type="button" class="btn btn-outline-light btn-lg"
                            data-bs-toggle="modal" data-bs-target="#kae-lead-modal"
                            data-lead-type="quotation">
                        <?= e(t('vehicle.detail.cta.quote')) ?>
                    </button>
                    <a href="<?= e($wa_link) ?>" target="_blank" rel="noopener"
                       class="btn btn-success btn-lg" data-track-wa="<?= (int) $vehicle['id'] ?>">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" style="margin-inline-end: 6px;"><path d="M20.5 3.5A11.4 11.4 0 0 0 12.3 0C5.9 0 .7 5.2.7 11.6c0 2.1.6 4.1 1.6 5.9L.6 24l6.7-1.8a11.6 11.6 0 0 0 5 1.2h.1c6.4 0 11.6-5.2 11.6-11.6 0-3.1-1.2-6-3.4-8.3zM12.3 21.4c-1.7 0-3.3-.4-4.7-1.3l-.3-.2-3.5.9.9-3.5-.2-.4a9.6 9.6 0 0 1-1.5-5.2C3 6.5 7.2 2.3 12.3 2.3c2.5 0 4.8 1 6.5 2.7a9.3 9.3 0 0 1 2.7 6.6c0 5.1-4.1 9.3-9.2 9.3z"/></svg>
                        <?= e(t('vehicle.detail.cta.whatsapp')) ?>
                    </a>
                </div>
            </div>

            <aside class="kae-vd-hero-price" data-reveal data-reveal-delay="480">
                <span class="kae-vd-hero-price-label"><?= e(t('vehicle.detail.cost.title')) ?></span>
                <span class="kae-vd-hero-price-value"><?= e(format_price((float) $vehicle['price_usd'])) ?></span>
                <span class="kae-vd-hero-price-dzd">
                    ≈ <?= e(format_price((float) ($vehicle['price_usd'] * $fx_rate), 'DZD')) ?>
                </span>
                <span class="kae-status kae-status-<?= e($status) ?>"><?= e(t('vehicle.status.' . $status)) ?></span>
            </aside>
        </div>
    </div>
</section>

<!-- ============ STICKY QUICK-BAR (appears after hero) ============ -->
<div class="kae-vd-stickybar" data-kae-vd-stickybar aria-hidden="true">
    <div class="container">
        <div class="kae-vd-stickybar-inner">
            <div class="kae-vd-stickybar-title">
                <span class="kae-vd-stickybar-eyebrow"><?= e($vehicle['year']) ?></span>
                <span class="kae-vd-stickybar-name"><?= e((string) $vehicle['title']) ?></span>
            </div>
            <div class="kae-vd-stickybar-price">
                <?= e(format_price((float) $vehicle['price_usd'])) ?>
            </div>
            <div class="kae-vd-stickybar-cta">
                <?php if ($reservable): ?>
                    <a href="<?= e(locale_url('/vehicles/' . $vehicle['slug'] . '/reserve')) ?>" class="btn btn-primary">
                        <?= e(t('vehicle.detail.cta.reserve')) ?>
                    </a>
                <?php endif; ?>
                <a href="<?= e($wa_link) ?>" target="_blank" rel="noopener" class="btn btn-success" data-track-wa="<?= (int) $vehicle['id'] ?>">
                    <?= e(t('vehicle.detail.cta.whatsapp')) ?>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ============ GALLERY ============ -->
<section class="kae-section kae-vd-gallery-section" data-reveal>
    <div class="container">
        <?= $this->partial('partials/vehicle-gallery', [
            'images'   => $images,
            'alt_lang' => $alt_lang,
            'title'    => (string) $vehicle['title'],
        ]) ?>
    </div>
</section>

<!-- ============ OVERVIEW / DESCRIPTION ============ -->
<?php if (! empty($vehicle['description'])): ?>
<section class="kae-section kae-section--soft">
    <div class="container">
        <div class="kae-vd-prose mx-auto" data-reveal>
            <span class="kae-eyebrow"><?= e(t('vehicle.detail.tabs.overview')) ?></span>
            <p class="lead"><?= nl2br(e((string) $vehicle['description'])) ?></p>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ============ SPEC GRID ============ -->
<section class="kae-section">
    <div class="container">
        <div class="row g-5 align-items-start">
            <div class="col-12 col-lg-4" data-reveal>
                <span class="kae-eyebrow"><?= e(t('vehicle.detail.tabs.specs')) ?></span>
                <h2 class="kae-section-title"><?= e(t('vehicle.detail.tabs.specs')) ?></h2>
                <p class="kae-section-subtitle">
                    <?= e(t('vehicle.detail.cost.disclaimer')) ?>
                </p>
            </div>

            <div class="col-12 col-lg-8" data-reveal data-reveal-delay="120">
                <dl class="kae-vd-specs">
                    <?php
                    $rows = [
                        'brand'        => $vehicle['brand_name'] ?? '',
                        'model'        => $vehicle['model_name'] ?? '',
                        'year'         => $vehicle['year'] ?? '',
                        'mileage'      => format_mileage((int) ($vehicle['mileage_km'] ?? 0)),
                        'engine'       => trim(($vehicle['engine_cc'] ? $vehicle['engine_cc'] . ' cc' : '') . (! empty($vehicle['engine_power_hp']) ? ' / ' . $vehicle['engine_power_hp'] . ' hp' : '')),
                        'transmission' => t('vehicle.transmission.' . $vehicle['transmission']),
                        'fuel'         => t('vehicle.fuel.' . $vehicle['fuel_type']),
                        'drivetrain'   => isset($vehicle['drivetrain']) ? t('vehicle.drivetrain.' . $vehicle['drivetrain']) : '',
                        'body_type'    => $vehicle['body_type_key'] ?? '',
                        'exterior'     => $vehicle['exterior_color'] ?? '',
                        'interior'     => $vehicle['interior_color'] ?? '',
                        'doors'        => $vehicle['doors'] ?? '',
                        'seats'        => $vehicle['seats'] ?? '',
                        'vin'          => $vehicle['vin'] ?? '',
                        'origin'       => $vehicle['origin_country'] ?? '',
                    ];
                    foreach ($rows as $k => $v):
                        if ($v === '' || $v === null) continue; ?>
                        <div class="kae-vd-spec">
                            <dt><?= e(t('vehicle.specs.' . $k)) ?></dt>
                            <dd><?= e((string) $v) ?></dd>
                        </div>
                    <?php endforeach; ?>
                </dl>
            </div>
        </div>
    </div>
</section>

<!-- ============ INSPECTION ============ -->
<?php if ($inspection): ?>
<section class="kae-section kae-section--soft">
    <div class="container">
        <div class="row g-5 align-items-start">
            <div class="col-12 col-lg-4" data-reveal>
                <span class="kae-eyebrow"><?= e(t('vehicle.detail.tabs.inspection')) ?></span>
                <h2 class="kae-section-title"><?= e(t('vehicle.detail.inspection.overall')) ?></h2>
            </div>
            <div class="col-12 col-lg-8" data-reveal data-reveal-delay="120">
                <?= $this->partial('partials/vehicle-inspection', [
                    'inspection' => $inspection,
                    'notes_lang' => $notes_lang,
                ]) ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ============ COST BREAKDOWN (dark band) ============ -->
<section class="kae-section kae-section--dark">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-12 col-lg-5" data-reveal>
                <span class="kae-eyebrow"><?= e(t('vehicle.detail.tabs.cost')) ?></span>
                <h2 class="kae-section-title"><?= e(t('vehicle.detail.cost.title')) ?></h2>
                <p class="kae-section-subtitle">
                    <?= e(t('vehicle.detail.cost.disclaimer')) ?>
                </p>
                <div class="d-flex flex-wrap gap-3 mt-4">
                    <button type="button" class="btn btn-primary"
                            data-bs-toggle="modal" data-bs-target="#kae-lead-modal"
                            data-lead-type="quotation">
                        <?= e(t('vehicle.detail.cost.request_exact')) ?>
                    </button>
                    <a href="<?= e(locale_url('/cost-calculator')) ?>?currency=usd&price=<?= (int) $vehicle['price_usd'] ?>" class="btn btn-outline-light">
                        <?= e(t('common.nav.cost_calculator')) ?>
                    </a>
                </div>
            </div>
            <div class="col-12 col-lg-7" data-reveal data-reveal-delay="120">
                <div class="kae-vd-cost-wrap">
                    <?= $this->partial('partials/cost-estimator', [
                        'estimate' => $estimate,
                        'fx_rate'  => $fx_rate,
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============ VIDEO ============ -->
<?php if (! empty($videos)): ?>
<section class="kae-section">
    <div class="container">
        <div class="text-center mb-4" data-reveal>
            <span class="kae-eyebrow"><?= e(t('vehicle.detail.tabs.video')) ?></span>
            <h2 class="kae-section-title"><?= e(t('vehicle.detail.video_caption')) ?></h2>
        </div>
        <div class="kae-vd-video" data-reveal>
            <?php $v = $videos[0]; ?>
            <?php if ($v['provider'] === 'youtube' && ! empty($v['external_url'])): ?>
                <?php
                $url = (string) $v['external_url'];
                $id  = '';
                if (preg_match('#(?:youtu\.be/|v=|/embed/)([A-Za-z0-9_-]{6,})#', $url, $m)) {
                    $id = $m[1];
                }
                ?>
                <?php if ($id !== ''): ?>
                    <div class="ratio ratio-16x9">
                        <iframe src="https://www.youtube-nocookie.com/embed/<?= e($id) ?>"
                                title="<?= e(t('vehicle.detail.video_caption')) ?>"
                                allow="encrypted-media; picture-in-picture"
                                allowfullscreen loading="lazy"></iframe>
                    </div>
                <?php endif; ?>
            <?php elseif (! empty($v['path'])): ?>
                <video controls preload="metadata" class="w-100 rounded"
                       <?php if (! empty($v['poster_path'])): ?>poster="<?= e(image_url((string) $v['poster_path'])) ?>"<?php endif; ?>>
                    <source src="<?= e(image_url((string) $v['path'])) ?>" type="video/mp4">
                </video>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ============ SIMILAR VEHICLES ============ -->
<?php if (! empty($similar)): ?>
<section class="kae-section kae-section--soft">
    <div class="container">
        <div class="mb-5" data-reveal>
            <span class="kae-eyebrow"><?= e(t('vehicle.detail.similar_eyebrow')) ?></span>
            <h2 class="kae-section-title"><?= e(t('vehicle.detail.similar')) ?></h2>
        </div>
        <div class="row g-3 g-md-4">
            <?php foreach ($similar as $i => $s): ?>
                <div class="col-12 col-sm-6 col-lg-3"
                     data-reveal data-reveal-delay="<?= ($i * 80) ?>">
                    <?= $this->partial('partials/vehicle-card', ['vehicle' => $s]) ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ============ CTA BAND ============ -->
<section class="kae-cta-band">
    <div class="container">
        <div class="kae-cta-band-inner">
            <span class="kae-eyebrow" data-reveal><?= e(t('vehicle.detail.cta.reserve')) ?></span>
            <h2 data-reveal data-reveal-delay="100"><?= e((string) $vehicle['title']) ?></h2>
            <p data-reveal data-reveal-delay="200">
                <?= e(format_price((float) $vehicle['price_usd'])) ?>
                · <?= e(format_mileage((int) $vehicle['mileage_km'])) ?>
                · <?= e(t('vehicle.fuel.' . $vehicle['fuel_type'])) ?>
            </p>
            <div class="kae-cta-band-actions" data-reveal data-reveal-delay="300">
                <a href="<?= e($wa_link) ?>" target="_blank" rel="noopener" class="btn btn-success btn-lg" data-track-wa="<?= (int) $vehicle['id'] ?>">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" style="margin-inline-end: 6px;"><path d="M20.5 3.5A11.4 11.4 0 0 0 12.3 0C5.9 0 .7 5.2.7 11.6c0 2.1.6 4.1 1.6 5.9L.6 24l6.7-1.8a11.6 11.6 0 0 0 5 1.2h.1c6.4 0 11.6-5.2 11.6-11.6 0-3.1-1.2-6-3.4-8.3zM12.3 21.4c-1.7 0-3.3-.4-4.7-1.3l-.3-.2-3.5.9.9-3.5-.2-.4a9.6 9.6 0 0 1-1.5-5.2C3 6.5 7.2 2.3 12.3 2.3c2.5 0 4.8 1 6.5 2.7a9.3 9.3 0 0 1 2.7 6.6c0 5.1-4.1 9.3-9.2 9.3z"/></svg>
                    <?= e(t('vehicle.detail.cta.whatsapp')) ?>
                </a>
                <?php if ($reservable): ?>
                    <a href="<?= e(locale_url('/vehicles/' . $vehicle['slug'] . '/reserve')) ?>" class="btn btn-primary btn-lg">
                        <?= e(t('vehicle.detail.cta.reserve')) ?>
                    </a>
                <?php endif; ?>
                <button type="button" class="btn btn-outline-light btn-lg"
                        data-bs-toggle="modal" data-bs-target="#kae-lead-modal"
                        data-lead-type="quotation">
                    <?= e(t('vehicle.detail.cta.quote')) ?>
                </button>
            </div>
        </div>
    </div>
</section>

<!-- ============ MOBILE STICKY CTA BAR ============ -->
<?= $this->partial('partials/sticky-lead-bar', [
    'wa_link'    => $wa_link,
    'vehicle_id' => (int) $vehicle['id'],
]) ?>

<!-- ============ LEAD MODAL ============ -->
<?= $this->partial('partials/lead-modal', [
    'vehicle_id'    => (int) $vehicle['id'],
    'vehicle_title' => (string) $vehicle['title'],
]) ?>

<script src="<?= e(asset('js/vehicle-detail.js')) ?>" defer></script>
<?php $this->endSection(); ?>
