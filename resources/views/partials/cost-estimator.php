<?php
/**
 * @var \App\Core\View $this
 * @var array $estimate     output of ImportCostEstimator::estimate()
 * @var float $fx_rate
 */
?>
<div class="kae-estimator p-3 p-md-4 border rounded-3 bg-white">
    <h3 class="h5 fw-bold mb-3"><?= e(t('vehicle.detail.cost.title')) ?></h3>
    <dl class="kae-estimator-rows">
        <div class="kae-estimator-row d-flex justify-content-between py-2">
            <dt class="text-muted"><?= e(t('vehicle.detail.cost.vehicle')) ?></dt>
            <dd class="mb-0 fw-bold"><?= e(format_price((float) $estimate['vehicle_usd'])) ?></dd>
        </div>
        <div class="kae-estimator-row d-flex justify-content-between py-2 border-top">
            <dt class="text-muted"><?= e(t('vehicle.detail.cost.shipping')) ?></dt>
            <dd class="mb-0 fw-bold"><?= e(format_price((float) $estimate['shipping_usd'])) ?></dd>
        </div>
        <div class="kae-estimator-row d-flex justify-content-between py-2 border-top">
            <dt class="text-muted"><?= e(t('vehicle.detail.cost.customs')) ?></dt>
            <dd class="mb-0 fw-bold"><?= e(format_price((float) $estimate['customs_usd'])) ?></dd>
        </div>
        <div class="kae-estimator-row d-flex justify-content-between py-2 border-top">
            <dt class="text-muted"><?= e(t('vehicle.detail.cost.service_fee')) ?></dt>
            <dd class="mb-0 fw-bold"><?= e(format_price((float) $estimate['service_fee_usd'])) ?></dd>
        </div>
        <div class="kae-estimator-total d-flex justify-content-between py-3 border-top mt-2">
            <dt class="fw-bold"><?= e(t('vehicle.detail.cost.total_usd')) ?></dt>
            <dd class="mb-0 fw-bold fs-4 text-primary"><?= e(format_price((float) $estimate['total_usd'])) ?></dd>
        </div>
        <div class="kae-estimator-total d-flex justify-content-between py-2">
            <dt class="text-muted"><?= e(t('vehicle.detail.cost.total_dzd')) ?></dt>
            <dd class="mb-0 fw-bold"><?= e(format_price((float) $estimate['total_dzd'], 'DZD')) ?></dd>
        </div>
    </dl>
    <p class="text-muted small mb-0"><?= e(t('vehicle.detail.cost.disclaimer')) ?></p>
</div>
