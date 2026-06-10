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
<a href="<?= e($url) ?>" class="kae-v-card text-decoration-none text-reset d-block h-100">
    <article class="kae-v-card-inner h-100">
        <div class="kae-v-card-media">
            <?= vehicle_picture($vehicle['cover_image_path'] ?? null, $title,
                                '(min-width:992px) 33vw, (min-width:576px) 50vw, 100vw') ?>
            <div class="kae-v-card-shade" aria-hidden="true"></div>

            <div class="kae-v-card-badges">
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

            <!-- Floating price chip over the photo, Kia-style -->
            <div class="kae-v-card-price-chip">
                <span class="kae-v-card-price-amount"><?= e(format_price((float) ($vehicle['price_usd'] ?? 0))) ?></span>
            </div>
        </div>

        <div class="kae-v-card-body">
            <h3 class="kae-v-card-title" title="<?= e($title) ?>">
                <?= e($title) ?>
            </h3>
            <ul class="kae-v-card-specs">
                <li>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                    <?= e(format_mileage((int) ($vehicle['mileage_km'] ?? 0))) ?>
                </li>
                <li>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h14v12H3z"/><path d="M17 10h2a2 2 0 0 1 2 2v4a1 1 0 0 1-2 0v-2h-2"/><path d="M7 14h6"/></svg>
                    <?= e($fuel) ?>
                </li>
                <li>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="6" cy="6" r="2"/><circle cx="18" cy="18" r="2"/><path d="M6 8v4a4 4 0 0 0 4 4h6"/><path d="M18 16V8"/></svg>
                    <?= e($trans) ?>
                </li>
            </ul>
            <div class="kae-v-card-foot">
                <?php if (! empty($vehicle['location'])): ?>
                    <span class="kae-v-card-loc">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 10c0 7-8 12-8 12s-8-5-8-12a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        <?= e((string) $vehicle['location']) ?>
                    </span>
                <?php endif; ?>
                <span class="kae-v-card-cta">
                    <?= e(t('home.featured.view_all') !== 'home.featured.view_all' ? 'View' : 'View') ?>
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                </span>
            </div>
        </div>
    </article>
</a>
