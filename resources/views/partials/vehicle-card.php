<?php
/**
 * @var \App\Core\View $this
 * @var array $vehicle  row from VehicleRepository::listSelect()
 */
$title = $vehicle['title']
       ?? ($vehicle['brand_name'] . ' ' . $vehicle['model_name'] . ' ' . $vehicle['year']);
$url   = vehicle_url((string) $vehicle['slug']);
$fuel  = t('vehicle.fuel.' . $vehicle['fuel_type']);
$trans = t('vehicle.transmission.' . $vehicle['transmission']);
?>
<a href="<?= e($url) ?>" class="kae-card text-decoration-none text-reset d-block h-100">
    <article class="card h-100 border-0 shadow-sm">
        <div class="kae-card-media">
            <?= vehicle_picture($vehicle['cover_image_path'] ?? null, $title,
                                '(min-width:992px) 25vw, (min-width:576px) 50vw, 100vw') ?>
            <?php if (! empty($vehicle['is_featured'])): ?>
                <span class="kae-card-badge"><?= e(t('common.badges.featured')) ?></span>
            <?php endif; ?>
            <?php if (($vehicle['status'] ?? '') === 'pending_reservation'): ?>
                <span class="kae-card-badge kae-card-badge-warning"><?= e(t('vehicle.status.pending_reservation')) ?></span>
            <?php elseif (($vehicle['status'] ?? '') === 'reserved'): ?>
                <span class="kae-card-badge kae-card-badge-warning"><?= e(t('vehicle.status.reserved')) ?></span>
            <?php elseif (($vehicle['status'] ?? '') === 'sold'): ?>
                <span class="kae-card-badge kae-card-badge-muted"><?= e(t('vehicle.status.sold')) ?></span>
            <?php endif; ?>
        </div>
        <div class="card-body d-flex flex-column">
            <h3 class="h6 fw-bold mb-1 text-truncate" title="<?= e($title) ?>">
                <?= e($title) ?>
            </h3>
            <div class="text-muted small mb-2">
                <?= e(format_mileage((int) ($vehicle['mileage_km'] ?? 0))) ?>
                · <?= e($fuel) ?>
                · <?= e($trans) ?>
            </div>
            <div class="d-flex justify-content-between align-items-end mt-auto pt-2">
                <span class="fw-bold fs-5"><?= e(format_price((float) ($vehicle['price_usd'] ?? 0))) ?></span>
                <?php if (! empty($vehicle['location'])): ?>
                    <span class="text-muted small">📍 <?= e((string) $vehicle['location']) ?></span>
                <?php endif; ?>
            </div>
        </div>
    </article>
</a>
