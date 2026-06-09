<?php
/** @var \App\Core\View $this  @var array $reservation  @var string $public_url */
$this->extends('layouts/admin');
$errors = flash('_errors') ?? [];
$r = $reservation;
$status = (string) $r['status'];
$isPending   = $status === 'pending_deposit';
$isConfirmed = $status === 'confirmed';
?>
<?php $this->section('content'); ?>
<div class="container-fluid">
    <div class="kae-page-head">
        <div>
            <a href="/admin/reservations" class="text-muted text-decoration-none small">← All reservations</a>
            <h1 class="mt-2">
                Reservation <code><?= e((string) $r['reference']) ?></code>
                <span class="kae-status kae-status-<?= e($status) ?> ms-2"><?= e($status) ?></span>
            </h1>
            <p class="text-muted small mb-0">
                <?= e(date('M j, Y · H:i', strtotime((string) $r['created_at']))) ?>
                · <a href="<?= e($public_url) ?>" target="_blank" rel="noopener">Public status page ↗</a>
            </p>
        </div>
    </div>

    <?php if (! empty($errors['_global'][0])): ?>
        <div class="alert alert-danger"><?= e($errors['_global'][0]) ?></div>
    <?php endif; ?>

    <div class="row g-3">
        <!-- LEFT: contact + vehicle + money -->
        <div class="col-12 col-lg-6">
            <div class="kae-card mb-3">
                <div class="kae-card-header">Customer</div>
                <div class="kae-card-body">
                    <dl class="row mb-0">
                        <dt class="col-4 text-muted small">Name</dt>
                        <dd class="col-8 fw-semibold"><?= e((string) $r['name']) ?></dd>
                        <dt class="col-4 text-muted small">Phone</dt>
                        <dd class="col-8"><a href="tel:<?= e(preg_replace('/\s+/', '', (string) $r['phone'])) ?>" class="text-decoration-none"><?= e((string) $r['phone']) ?></a></dd>
                        <?php if (! empty($r['whatsapp'])): ?>
                            <dt class="col-4 text-muted small">WhatsApp</dt>
                            <dd class="col-8"><a href="https://wa.me/<?= e(\App\Services\Phone::forWhatsapp((string) $r['whatsapp'])) ?>" target="_blank" rel="noopener" class="text-decoration-none">💬 <?= e((string) $r['whatsapp']) ?></a></dd>
                        <?php endif; ?>
                        <?php if (! empty($r['email'])): ?>
                            <dt class="col-4 text-muted small">Email</dt>
                            <dd class="col-8"><a href="mailto:<?= e((string) $r['email']) ?>" class="text-decoration-none"><?= e((string) $r['email']) ?></a></dd>
                        <?php endif; ?>
                        <dt class="col-4 text-muted small">City</dt>
                        <dd class="col-8"><?= e((string) ($r['city'] ?? '—')) ?></dd>
                        <dt class="col-4 text-muted small">Locale</dt>
                        <dd class="col-8"><?= e((string) $r['locale']) ?></dd>
                    </dl>
                </div>
            </div>

            <?php if (! empty($r['vehicle_label'])): ?>
                <div class="kae-card mb-3">
                    <div class="kae-card-header">Vehicle</div>
                    <div class="kae-card-body">
                        <div class="fw-semibold"><?= e((string) $r['vehicle_label']) ?></div>
                        <div class="mt-2 d-flex gap-2">
                            <?php if (! empty($r['vehicle_slug'])): ?>
                                <a href="/<?= e(config('locales.default')) ?>/vehicles/<?= e((string) $r['vehicle_slug']) ?>" target="_blank" class="btn btn-sm btn-outline-dark">View public ↗</a>
                            <?php endif; ?>
                            <?php if (! empty($r['vehicle_id'])): ?>
                                <a href="/admin/vehicles/<?= (int) $r['vehicle_id'] ?>/edit" class="btn btn-sm btn-outline-dark">Edit admin</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="kae-card mb-3">
                <div class="kae-card-header">Money</div>
                <div class="kae-card-body">
                    <dl class="row mb-0">
                        <dt class="col-5 text-muted small">Deposit</dt>
                        <dd class="col-7 fw-bold">$<?= e(number_format((float) $r['deposit_amount_usd'], 2)) ?></dd>
                        <dt class="col-5 text-muted small">Currency</dt>
                        <dd class="col-7"><?= e((string) $r['currency']) ?></dd>
                        <dt class="col-5 text-muted small">Expires</dt>
                        <dd class="col-7"><?= e((string) $r['expires_at']) ?></dd>
                        <?php if (! empty($r['confirmed_at'])): ?>
                            <dt class="col-5 text-muted small">Confirmed</dt>
                            <dd class="col-7"><?= e((string) $r['confirmed_at']) ?></dd>
                        <?php endif; ?>
                        <?php if (! empty($r['cancelled_at'])): ?>
                            <dt class="col-5 text-muted small">Cancelled</dt>
                            <dd class="col-7">
                                <?= e((string) $r['cancelled_at']) ?>
                                <?php if (! empty($r['cancellation_reason'])): ?>
                                    <div class="text-muted small mt-1"><?= e((string) $r['cancellation_reason']) ?></div>
                                <?php endif; ?>
                            </dd>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>
        </div>

        <!-- RIGHT: actions + admin note -->
        <div class="col-12 col-lg-6">
            <?php if ($isPending): ?>
                <div class="kae-card mb-3">
                    <div class="kae-card-header">Mark deposit received</div>
                    <div class="kae-card-body">
                        <form method="POST" action="/admin/reservations/<?= (int) $r['id'] ?>/confirm">
                            <?= csrf_field() ?>
                            <textarea name="admin_note" rows="2" class="form-control form-control-sm mb-2" placeholder="Wire ref / Wise tx id / etc. (optional)"></textarea>
                            <button type="submit" class="btn btn-success btn-sm">Confirm deposit received</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($isPending || $isConfirmed): ?>
                <div class="kae-card mb-3">
                    <div class="kae-card-header">Cancel</div>
                    <div class="kae-card-body">
                        <form method="POST" action="/admin/reservations/<?= (int) $r['id'] ?>/cancel"
                              onsubmit="return confirm('Cancel this reservation? The vehicle goes back to available.');">
                            <?= csrf_field() ?>
                            <textarea name="cancellation_reason" rows="2" class="form-control form-control-sm mb-2" placeholder="Reason (optional, shown to customer)"></textarea>
                            <button type="submit" class="btn btn-outline-danger btn-sm">Cancel reservation</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($isConfirmed): ?>
                <div class="kae-card mb-3">
                    <div class="kae-card-header">Convert to sale</div>
                    <div class="kae-card-body">
                        <p class="small text-muted mb-2">Use this when the deal closes — vehicle moves to <code>sold</code> permanently.</p>
                        <form method="POST" action="/admin/reservations/<?= (int) $r['id'] ?>/convert"
                              onsubmit="return confirm('Mark this vehicle as sold? This is permanent.');">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-primary btn-sm">Convert to sale</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <div class="kae-card">
                <div class="kae-card-header">Internal note</div>
                <div class="kae-card-body">
                    <form method="POST" action="/admin/reservations/<?= (int) $r['id'] ?>/note">
                        <?= csrf_field() ?>
                        <textarea name="admin_note" rows="3" class="form-control form-control-sm" placeholder="Free text — overwrites the existing note."><?= e((string) ($r['admin_note'] ?? '')) ?></textarea>
                        <button type="submit" class="btn btn-primary btn-sm mt-2">Save note</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->endSection(); ?>
