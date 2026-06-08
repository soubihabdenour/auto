<?php
/**
 * @var \App\Core\View $this
 * @var int    $vehicle_id
 * @var string $vehicle_title
 */
?>
<div class="modal fade" id="kae-lead-modal" tabindex="-1" aria-labelledby="kae-lead-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="kae-lead-form" method="POST" action="<?= e(locale_url('/inquiry')) ?>" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="vehicle_id" value="<?= (int) $vehicle_id ?>">
                <input type="hidden" name="lead_type" id="kae-lead-type" value="inquiry">
                <input type="text" name="_website" tabindex="-1" autocomplete="off"
                       style="position:absolute;left:-9999px;" aria-hidden="true">

                <div class="modal-header">
                    <h5 class="modal-title" id="kae-lead-modal-label">
                        <span id="kae-lead-modal-title"><?= e(t('vehicle.detail.cta.quote')) ?></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="close"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-3">
                        <?= e(t('lead.modal.about', ['vehicle' => $vehicle_title])) ?>
                    </p>

                    <div class="mb-3">
                        <label class="form-label" for="lm-name"><?= e(t('lead.fields.name')) ?> *</label>
                        <input type="text" name="name" id="lm-name" class="form-control" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="lm-phone"><?= e(t('lead.fields.phone')) ?> *</label>
                            <input type="tel" name="phone" id="lm-phone" class="form-control" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="lm-whatsapp"><?= e(t('lead.fields.whatsapp')) ?></label>
                            <input type="tel" name="whatsapp" id="lm-whatsapp" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="lm-city"><?= e(t('lead.fields.city')) ?></label>
                        <input type="text" name="city" id="lm-city" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="lm-message"><?= e(t('lead.fields.message')) ?></label>
                        <textarea name="message" id="lm-message" rows="3" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link" data-bs-dismiss="modal">
                        <?= e(t('common.actions.cancel')) ?>
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <?= e(t('lead.submit')) ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
