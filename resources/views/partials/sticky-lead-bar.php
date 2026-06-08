<?php
/**
 * @var \App\Core\View $this
 * @var string $wa_link
 * @var int    $vehicle_id
 */
?>
<div class="kae-sticky-bar d-lg-none">
    <a href="<?= e($wa_link) ?>" target="_blank" rel="noopener"
       class="kae-sticky-btn kae-sticky-btn-wa"
       data-track-wa="<?= (int) $vehicle_id ?>">
        <span class="kae-sticky-icon">💬</span>
        <span class="kae-sticky-label"><?= e(t('vehicle.detail.cta.whatsapp')) ?></span>
    </a>
    <button type="button" class="kae-sticky-btn kae-sticky-btn-quote"
            data-bs-toggle="modal" data-bs-target="#kae-lead-modal"
            data-lead-type="quotation">
        <span class="kae-sticky-icon">💰</span>
        <span class="kae-sticky-label"><?= e(t('vehicle.detail.cta.quote')) ?></span>
    </button>
    <button type="button" class="kae-sticky-btn kae-sticky-btn-reserve"
            data-bs-toggle="modal" data-bs-target="#kae-lead-modal"
            data-lead-type="reservation">
        <span class="kae-sticky-icon">📌</span>
        <span class="kae-sticky-label"><?= e(t('vehicle.detail.cta.reserve')) ?></span>
    </button>
</div>
