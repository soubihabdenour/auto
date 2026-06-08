<?php
/**
 * @var \App\Core\View $this
 * @var int    $lead_id
 * @var string $wa_link
 */
$this->extends('layouts/public');
?>
<?php $this->section('content'); ?>
<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-7 col-lg-6 text-center">
            <div class="kae-success-icon mx-auto mb-4">✓</div>
            <h1 class="h2 fw-bold mb-2"><?= e(t('lead.success.title')) ?></h1>
            <p class="text-muted lead mb-4"><?= e(t('lead.success.subtitle')) ?></p>

            <?php if ($lead_id > 0): ?>
                <p class="text-muted small mb-4">Ref: #<?= (int) $lead_id ?></p>
            <?php endif; ?>

            <div class="d-grid gap-2 col-md-8 mx-auto">
                <a href="<?= e($wa_link) ?>" target="_blank" rel="noopener"
                   class="btn btn-success btn-lg">
                    💬 <?= e(t('lead.success.wa_cta')) ?>
                </a>
                <a href="<?= e(locale_url('/vehicles')) ?>" class="btn btn-outline-dark btn-lg">
                    <?= e(t('lead.success.back')) ?>
                </a>
            </div>
        </div>
    </div>
</section>
<?php $this->endSection(); ?>
