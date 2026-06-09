<?php
/**
 * @var \App\Core\View $this
 * @var array  $reservation
 * @var string $bank_instructions
 */
$this->extends('layouts/public');
$status = (string) $reservation['status'];
$ref    = (string) $reservation['reference'];

$expiresAtTs = strtotime((string) $reservation['expires_at']) ?: 0;
$remaining   = max(0, $expiresAtTs - time());
$hoursLeft   = (int) floor($remaining / 3600);
$minsLeft    = (int) floor(($remaining % 3600) / 60);
?>
<?php $this->section('content'); ?>
<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-9 col-lg-7">
            <div class="text-center mb-4">
                <span class="kae-eyebrow"><?= e(t('reservation.show.eyebrow')) ?></span>
                <h1 class="h2 fw-bold mt-2 mb-2"><?= e(t('reservation.show.title')) ?></h1>
                <div class="text-muted">
                    <?= e(t('reservation.fields.reference')) ?>:
                    <code class="fw-bold"><?= e($ref) ?></code>
                </div>
            </div>

            <div class="kae-card p-4 mb-3">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <strong><?= e(t('reservation.fields.status')) ?></strong>
                    <span class="kae-status kae-status-<?= e($status) ?>">
                        <?= e(t('reservation.status.' . $status)) ?>
                    </span>
                </div>

                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted"><?= e(t('reservation.fields.vehicle')) ?></dt>
                    <dd class="col-7 fw-semibold">
                        <?php if (! empty($reservation['vehicle_slug'])): ?>
                            <a href="<?= e(locale_url('/vehicles/' . $reservation['vehicle_slug'])) ?>"
                               class="text-decoration-none">
                                <?= e((string) ($reservation['vehicle_label'] ?? '—')) ?>
                            </a>
                        <?php else: ?>
                            <?= e((string) ($reservation['vehicle_label'] ?? '—')) ?>
                        <?php endif; ?>
                    </dd>
                    <dt class="col-5 text-muted"><?= e(t('reservation.fields.deposit')) ?></dt>
                    <dd class="col-7 fw-semibold">$<?= e(number_format((float) $reservation['deposit_amount_usd'], 2)) ?></dd>

                    <?php if ($status === 'pending_deposit'): ?>
                        <dt class="col-5 text-muted"><?= e(t('reservation.fields.expires_at')) ?></dt>
                        <dd class="col-7 fw-semibold">
                            <?= e((string) $reservation['expires_at']) ?>
                            <span class="text-muted">(<?= $hoursLeft ?>h <?= $minsLeft ?>m)</span>
                        </dd>
                    <?php elseif ($status === 'confirmed' && ! empty($reservation['confirmed_at'])): ?>
                        <dt class="col-5 text-muted"><?= e(t('reservation.fields.confirmed_at')) ?></dt>
                        <dd class="col-7 fw-semibold"><?= e((string) $reservation['confirmed_at']) ?></dd>
                    <?php elseif ($status === 'cancelled' && ! empty($reservation['cancelled_at'])): ?>
                        <dt class="col-5 text-muted"><?= e(t('reservation.fields.cancelled_at')) ?></dt>
                        <dd class="col-7 fw-semibold"><?= e((string) $reservation['cancelled_at']) ?></dd>
                    <?php endif; ?>
                </dl>
            </div>

            <?php if ($status === 'pending_deposit' && $bank_instructions !== ''): ?>
                <div class="kae-card p-4 mb-3">
                    <h2 class="h6 fw-bold mb-2"><?= e(t('reservation.show.instructions_title')) ?></h2>
                    <p class="small text-muted mb-2">
                        <?= e(t('reservation.show.instructions_note', ['reference' => $ref])) ?>
                    </p>
                    <div class="small" style="white-space:pre-line"><?= e($bank_instructions) ?></div>
                </div>
            <?php endif; ?>

            <?php if ($status === 'pending_deposit'): ?>
                <div class="alert alert-info small">
                    <?= e(t('reservation.show.next_steps')) ?>
                </div>
            <?php elseif ($status === 'confirmed'): ?>
                <div class="alert alert-success small">
                    <?= e(t('reservation.show.confirmed_msg')) ?>
                </div>
            <?php elseif ($status === 'expired'): ?>
                <div class="alert alert-warning small">
                    <?= e(t('reservation.show.expired_msg')) ?>
                </div>
            <?php elseif ($status === 'cancelled'): ?>
                <div class="alert alert-secondary small">
                    <?= e(t('reservation.show.cancelled_msg')) ?>
                </div>
            <?php endif; ?>

            <div class="text-center mt-4">
                <a href="<?= e(locale_url('/vehicles')) ?>" class="btn btn-outline-dark">
                    <?= e(t('reservation.show.back_to_listings')) ?>
                </a>
            </div>
        </div>
    </div>
</section>
<?php $this->endSection(); ?>
