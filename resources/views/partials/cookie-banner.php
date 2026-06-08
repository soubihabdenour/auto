<?php
/** @var \App\Core\View $this */
?>
<!-- Cookie banner (hidden until JS decides whether to show it) -->
<div class="kae-cookie-banner" id="kae-cookie-banner" hidden role="region" aria-label="cookie consent">
    <div class="container">
        <div class="row align-items-center g-3">
            <div class="col-lg-7">
                <h4 class="h6 fw-bold mb-1"><?= e(t('common.cookies.banner_title')) ?></h4>
                <p class="small text-white-50 mb-0"><?= e(t('common.cookies.banner_body')) ?></p>
            </div>
            <div class="col-lg-5">
                <div class="d-flex flex-wrap justify-content-lg-end gap-2">
                    <button type="button" class="btn btn-link text-white-50 btn-sm" data-cookie-action="customize" data-bs-toggle="modal" data-bs-target="#kae-cookie-modal">
                        <?= e(t('common.cookies.customize')) ?>
                    </button>
                    <button type="button" class="btn btn-outline-light btn-sm" data-cookie-action="reject">
                        <?= e(t('common.cookies.reject')) ?>
                    </button>
                    <button type="button" class="btn btn-primary btn-sm" data-cookie-action="accept-all">
                        <?= e(t('common.cookies.accept_all')) ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cookie preferences modal -->
<div class="modal fade" id="kae-cookie-modal" tabindex="-1" aria-labelledby="kae-cookie-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="kae-cookie-modal-label"><?= e(t('common.cookies.modal_title')) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="close"></button>
            </div>
            <div class="modal-body">
                <div class="kae-cookie-group mb-3 p-3 bg-light rounded">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong><?= e(t('common.cookies.group_necessary_title')) ?></strong>
                        <span class="badge bg-success">on</span>
                    </div>
                    <p class="small text-muted mb-0"><?= e(t('common.cookies.group_necessary_body')) ?></p>
                </div>
                <div class="kae-cookie-group p-3 border rounded">
                    <div class="form-check form-switch d-flex justify-content-between align-items-center mb-1">
                        <label class="form-check-label fw-bold" for="kae-cc-analytics"><?= e(t('common.cookies.group_analytics_title')) ?></label>
                        <input class="form-check-input" type="checkbox" id="kae-cc-analytics">
                    </div>
                    <p class="small text-muted mb-0"><?= e(t('common.cookies.group_analytics_body')) ?></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link" data-bs-dismiss="modal"><?= e(t('common.cookies.cancel')) ?></button>
                <button type="button" class="btn btn-primary" data-cookie-action="save"><?= e(t('common.cookies.save')) ?></button>
            </div>
        </div>
    </div>
</div>

<script src="<?= e(asset('js/cookies.js')) ?>" defer></script>
