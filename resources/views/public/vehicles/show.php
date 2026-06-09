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

<section class="container py-4">
    <nav aria-label="breadcrumb" class="small mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= e(locale_url('/')) ?>"><?= e(t('vehicle.detail.breadcrumb.home')) ?></a></li>
            <li class="breadcrumb-item"><a href="<?= e(locale_url('/vehicles')) ?>"><?= e(t('vehicle.detail.breadcrumb.vehicles')) ?></a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= e((string) $vehicle['title']) ?></li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- ============ GALLERY (left) ============ -->
        <div class="col-12 col-lg-8">
            <?= $this->partial('partials/vehicle-gallery', [
                'images'   => $images,
                'alt_lang' => $alt_lang,
                'title'    => (string) $vehicle['title'],
            ]) ?>
        </div>

        <!-- ============ PRICE + STICKY CTAs (right, sticky on desktop) ============ -->
        <div class="col-12 col-lg-4">
            <aside class="kae-detail-sidebar-v2">
                <span class="kae-eyebrow"><?= e($vehicle['year'] . ' · ' . $vehicle['brand_name']) ?></span>
                <h1 class="kae-detail-title">
                    <?= e((string) $vehicle['title']) ?>
                </h1>
                <div class="text-muted small mb-3">
                    <?= e(format_mileage((int) $vehicle['mileage_km'])) ?>
                    · <?= e(t('vehicle.fuel.' . $vehicle['fuel_type'])) ?>
                    · <?= e(t('vehicle.transmission.' . $vehicle['transmission'])) ?>
                    <?php if (! empty($vehicle['location'])): ?>
                        · 📍 <?= e((string) $vehicle['location']) ?>
                    <?php endif; ?>
                </div>

                <div class="kae-detail-price-v2">
                    <span class="kae-detail-price-label"><?= e(t('vehicle.detail.cost.title')) ?></span>
                    <span class="kae-detail-price-value">
                        <?= e(format_price((float) $vehicle['price_usd'])) ?>
                    </span>
                    <span class="kae-detail-price-dzd">
                        ≈ <?= e(format_price((float) ($vehicle['price_usd'] * $fx_rate), 'DZD')) ?>
                    </span>
                </div>

                <?php
                    $status = (string) ($vehicle['status'] ?? 'available');
                ?>
                <span class="kae-status kae-status-<?= e($status) ?> mb-3 d-inline-block">
                    <?= e(t('vehicle.status.' . $status)) ?>
                </span>

                <div class="d-grid gap-2">
                    <a href="<?= e($wa_link) ?>" target="_blank" rel="noopener"
                       class="btn btn-success btn-lg"
                       data-track-wa="<?= (int) $vehicle['id'] ?>">
                        💬 <?= e(t('vehicle.detail.cta.whatsapp')) ?>
                    </a>
                    <button type="button" class="btn btn-primary btn-lg"
                            data-bs-toggle="modal" data-bs-target="#kae-lead-modal"
                            data-lead-type="quotation">
                        <?= e(t('vehicle.detail.cta.quote')) ?> →
                    </button>
                    <?php $reservable = ($vehicle['status'] ?? '') === 'available'; ?>
                    <?php if ($reservable): ?>
                        <a href="<?= e(locale_url('/vehicles/' . $vehicle['slug'] . '/reserve')) ?>"
                           class="btn btn-outline-dark btn-lg">
                            📌 <?= e(t('vehicle.detail.cta.reserve')) ?>
                        </a>
                    <?php else: ?>
                        <button type="button" class="btn btn-outline-secondary btn-lg" disabled>
                            📌 <?= e(t('vehicle.detail.cta.reserve_unavailable')) ?>
                        </button>
                    <?php endif; ?>
                </div>
            </aside>
        </div>
    </div>
</section>

<!-- ============ TABS (specs, inspection, cost, video) ============ -->
<section class="container pb-5">
    <ul class="nav nav-tabs kae-detail-tabs" id="kae-detail-tabs" role="tablist">
        <?php $tabs = [
            'overview'   => 'vehicle.detail.tabs.overview',
            'specs'      => 'vehicle.detail.tabs.specs',
            'inspection' => 'vehicle.detail.tabs.inspection',
            'cost'       => 'vehicle.detail.tabs.cost',
        ]; ?>
        <?php if (! empty($videos)): $tabs['video'] = 'vehicle.detail.tabs.video'; endif; ?>
        <?php foreach ($tabs as $key => $label): ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $key === 'overview' ? 'active' : '' ?>"
                        id="tab-<?= e($key) ?>" data-bs-toggle="tab"
                        data-bs-target="#pane-<?= e($key) ?>" type="button" role="tab">
                    <?= e(t($label)) ?>
                </button>
            </li>
        <?php endforeach; ?>
    </ul>

    <div class="tab-content border border-top-0 rounded-bottom p-3 p-md-4 bg-white" id="kae-detail-panes">
        <!-- Overview -->
        <div class="tab-pane fade show active" id="pane-overview" role="tabpanel">
            <?php if (! empty($vehicle['description'])): ?>
                <p class="lead text-muted"><?= nl2br(e((string) $vehicle['description'])) ?></p>
            <?php else: ?>
                <p class="text-muted">—</p>
            <?php endif; ?>
        </div>

        <!-- Specs -->
        <div class="tab-pane fade" id="pane-specs" role="tabpanel">
            <dl class="row mb-0">
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
                    <dt class="col-5 col-md-3 text-muted small"><?= e(t('vehicle.specs.' . $k)) ?></dt>
                    <dd class="col-7 col-md-9 fw-bold"><?= e((string) $v) ?></dd>
                <?php endforeach; ?>
            </dl>
        </div>

        <!-- Inspection -->
        <div class="tab-pane fade" id="pane-inspection" role="tabpanel">
            <?= $this->partial('partials/vehicle-inspection', [
                'inspection' => $inspection,
                'notes_lang' => $notes_lang,
            ]) ?>
        </div>

        <!-- Cost -->
        <div class="tab-pane fade" id="pane-cost" role="tabpanel">
            <?= $this->partial('partials/cost-estimator', [
                'estimate' => $estimate,
                'fx_rate'  => $fx_rate,
            ]) ?>
            <div class="text-center mt-3">
                <button type="button" class="btn btn-outline-primary"
                        data-bs-toggle="modal" data-bs-target="#kae-lead-modal"
                        data-lead-type="quotation">
                    <?= e(t('vehicle.detail.cost.request_exact')) ?>
                </button>
            </div>
        </div>

        <!-- Video -->
        <?php if (! empty($videos)): ?>
            <div class="tab-pane fade" id="pane-video" role="tabpanel">
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
        <?php endif; ?>
    </div>
</section>

<!-- ============ SIMILAR VEHICLES ============ -->
<?php if (! empty($similar)): ?>
<section class="kae-section kae-section--soft">
    <div class="container">
        <div class="mb-4">
            <span class="kae-eyebrow"><?= e(t('vehicle.detail.similar_eyebrow')) ?></span>
            <h2 class="kae-section-title"><?= e(t('vehicle.detail.similar')) ?></h2>
        </div>
        <div class="row g-3 g-md-4">
            <?php foreach ($similar as $s): ?>
                <div class="col-12 col-sm-6 col-lg-3">
                    <?= $this->partial('partials/vehicle-card', ['vehicle' => $s]) ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

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
