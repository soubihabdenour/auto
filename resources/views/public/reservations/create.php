<?php
/**
 * @var \App\Core\View $this
 * @var array  $vehicle
 * @var array|null $active_reservation
 * @var float  $deposit_amount_usd
 * @var int    $expiry_hours
 * @var string $bank_instructions
 * @var array  $old
 * @var array  $errors
 */
$this->extends('layouts/public');
$slug = (string) $vehicle['slug'];
?>
<?php $this->section('content'); ?>
<section class="container py-4">
    <nav aria-label="breadcrumb" class="small mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= e(locale_url('/')) ?>"><?= e(t('vehicle.detail.breadcrumb.home')) ?></a></li>
            <li class="breadcrumb-item"><a href="<?= e(locale_url('/vehicles')) ?>"><?= e(t('vehicle.detail.breadcrumb.vehicles')) ?></a></li>
            <li class="breadcrumb-item"><a href="<?= e(locale_url('/vehicles/' . $slug)) ?>"><?= e((string) $vehicle['title']) ?></a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= e(t('reservation.create.title')) ?></li>
        </ol>
    </nav>

    <div class="row g-4">
        <div class="col-12 col-lg-7">
            <h1 class="h2 fw-bold mb-2"><?= e(t('reservation.create.title')) ?></h1>
            <p class="text-muted mb-4">
                <?= e(t('reservation.create.subtitle', [
                    'vehicle' => (string) $vehicle['title'],
                    'amount'  => '$' . number_format($deposit_amount_usd, 2),
                    'hours'   => (string) $expiry_hours,
                ])) ?>
            </p>

            <?php if ($active_reservation !== null): ?>
                <div class="alert alert-warning">
                    <strong><?= e(t('reservation.create.locked_title')) ?></strong>
                    <div><?= e(t('reservation.create.locked_body')) ?></div>
                    <div class="mt-2">
                        <a href="<?= e(locale_url('/reservations/' . $active_reservation['reference'])) ?>"
                           class="btn btn-sm btn-outline-dark">
                            <?= e(t('reservation.create.view_existing')) ?>
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <?php if (! empty($errors['_global'][0])): ?>
                    <div class="alert alert-danger"><?= e($errors['_global'][0]) ?></div>
                <?php endif; ?>

                <form method="POST" action="<?= e(locale_url('/vehicles/' . $slug . '/reserve')) ?>" class="kae-card p-3 p-md-4">
                    <?= csrf_field() ?>
                    <input type="text" name="_website" value="" autocomplete="off" tabindex="-1" style="position:absolute;left:-9999px">

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="r-name" class="form-label"><?= e(t('lead.fields.name')) ?></label>
                            <input type="text" name="name" id="r-name" required maxlength="150"
                                   value="<?= e((string) ($old['name'] ?? '')) ?>"
                                   class="form-control <?= empty($errors['name']) ? '' : 'is-invalid' ?>">
                            <?php if (! empty($errors['name'][0])): ?>
                                <div class="invalid-feedback"><?= e($errors['name'][0]) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="r-phone" class="form-label"><?= e(t('lead.fields.phone')) ?></label>
                            <input type="tel" name="phone" id="r-phone" required maxlength="40"
                                   value="<?= e((string) ($old['phone'] ?? '')) ?>"
                                   class="form-control <?= empty($errors['phone']) ? '' : 'is-invalid' ?>">
                            <?php if (! empty($errors['phone'][0])): ?>
                                <div class="invalid-feedback"><?= e($errors['phone'][0]) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="r-whatsapp" class="form-label"><?= e(t('lead.fields.whatsapp')) ?></label>
                            <input type="tel" name="whatsapp" id="r-whatsapp" maxlength="40"
                                   value="<?= e((string) ($old['whatsapp'] ?? '')) ?>"
                                   class="form-control">
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="r-email" class="form-label"><?= e(t('lead.fields.email')) ?></label>
                            <input type="email" name="email" id="r-email" maxlength="190"
                                   value="<?= e((string) ($old['email'] ?? '')) ?>"
                                   class="form-control <?= empty($errors['email']) ? '' : 'is-invalid' ?>">
                            <?php if (! empty($errors['email'][0])): ?>
                                <div class="invalid-feedback"><?= e($errors['email'][0]) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-12">
                            <label for="r-city" class="form-label"><?= e(t('lead.fields.city')) ?></label>
                            <input type="text" name="city" id="r-city" maxlength="120"
                                   value="<?= e((string) ($old['city'] ?? '')) ?>"
                                   class="form-control">
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" name="agree_terms" id="r-agree" value="1" required>
                        <label class="form-check-label" for="r-agree">
                            <?= e(t('reservation.create.agree')) ?>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg">
                        📌 <?= e(t('reservation.create.submit', [
                            'amount' => '$' . number_format($deposit_amount_usd, 2),
                        ])) ?>
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <div class="col-12 col-lg-5">
            <aside class="kae-card p-3 p-md-4 mb-3">
                <h2 class="h5 fw-bold mb-2"><?= e(t('reservation.create.summary_title')) ?></h2>
                <div class="mb-2 text-muted small"><?= e((string) $vehicle['title']) ?></div>
                <dl class="row mb-0 small">
                    <dt class="col-6 text-muted"><?= e(t('reservation.fields.deposit')) ?></dt>
                    <dd class="col-6 fw-semibold">$<?= e(number_format($deposit_amount_usd, 2)) ?></dd>
                    <dt class="col-6 text-muted"><?= e(t('reservation.fields.expiry')) ?></dt>
                    <dd class="col-6 fw-semibold"><?= (int) $expiry_hours ?> h</dd>
                </dl>
            </aside>

            <?php if ($bank_instructions !== ''): ?>
                <aside class="kae-card p-3 p-md-4">
                    <h2 class="h6 fw-bold mb-2"><?= e(t('reservation.create.instructions_title')) ?></h2>
                    <div class="small" style="white-space:pre-line"><?= e($bank_instructions) ?></div>
                </aside>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php $this->endSection(); ?>
