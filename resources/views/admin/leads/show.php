<?php
/** @var \App\Core\View $this  @var array $lead  @var array $notes  @var array $statuses */
$this->extends('layouts/admin');
$errors = flash('_errors') ?? [];
?>
<?php $this->section('content'); ?>
<div class="container-fluid">
    <div class="kae-page-head">
        <div>
            <a href="/admin/leads" class="text-muted text-decoration-none small">← All leads</a>
            <h1 class="mt-2">
                Lead #<?= (int) $lead['id'] ?> — <?= e((string) $lead['name']) ?>
                <span class="kae-status kae-status-<?= e($lead['status']) ?> ms-2"><?= e($lead['status']) ?></span>
            </h1>
            <p class="text-muted small mb-0">
                <?= e((string) $lead['lead_type']) ?> ·
                <?= e(date('M j, Y · H:i', strtotime((string) $lead['created_at']))) ?>
            </p>
        </div>
    </div>

    <?php if (! empty($errors['_global'][0])): ?>
        <div class="alert alert-danger"><?= e($errors['_global'][0]) ?></div>
    <?php endif; ?>

    <div class="row g-3">
        <!-- LEFT: customer + vehicle + message -->
        <div class="col-12 col-lg-6">
            <div class="kae-card mb-3">
                <div class="kae-card-header">Customer</div>
                <div class="kae-card-body">
                    <dl class="row mb-0">
                        <dt class="col-4 text-muted small">Name</dt>
                        <dd class="col-8 fw-semibold"><?= e((string) $lead['name']) ?></dd>
                        <dt class="col-4 text-muted small">Phone</dt>
                        <dd class="col-8"><a href="tel:<?= e(preg_replace('/\s+/', '', (string) $lead['phone'])) ?>" class="text-decoration-none"><?= e((string) $lead['phone']) ?></a></dd>
                        <?php if (! empty($lead['whatsapp'])): ?>
                            <dt class="col-4 text-muted small">WhatsApp</dt>
                            <dd class="col-8"><a href="https://wa.me/<?= e(\App\Services\Phone::forWhatsapp((string) $lead['whatsapp'])) ?>" target="_blank" rel="noopener" class="text-decoration-none">💬 <?= e((string) $lead['whatsapp']) ?></a></dd>
                        <?php endif; ?>
                        <?php if (! empty($lead['email'])): ?>
                            <dt class="col-4 text-muted small">Email</dt>
                            <dd class="col-8"><a href="mailto:<?= e((string) $lead['email']) ?>" class="text-decoration-none"><?= e((string) $lead['email']) ?></a></dd>
                        <?php endif; ?>
                        <dt class="col-4 text-muted small">City</dt>
                        <dd class="col-8"><?= e((string) ($lead['city'] ?? '—')) ?></dd>
                        <dt class="col-4 text-muted small">Locale</dt>
                        <dd class="col-8"><?= e((string) $lead['locale']) ?></dd>
                        <dt class="col-4 text-muted small">Source</dt>
                        <dd class="col-8"><?= e((string) $lead['source']) ?></dd>
                    </dl>
                </div>
            </div>

            <?php if (! empty($lead['vehicle_label'])): ?>
                <div class="kae-card mb-3">
                    <div class="kae-card-header">Vehicle of interest</div>
                    <div class="kae-card-body">
                        <div class="fw-semibold"><?= e((string) $lead['vehicle_label']) ?></div>
                        <div class="mt-2 d-flex gap-2">
                            <?php if (! empty($lead['vehicle_slug'])): ?>
                                <a href="/<?= e(config('locales.default')) ?>/vehicles/<?= e((string) $lead['vehicle_slug']) ?>" target="_blank" class="btn btn-sm btn-outline-dark">View public ↗</a>
                            <?php endif; ?>
                            <?php if (! empty($lead['vehicle_id'])): ?>
                                <a href="/admin/vehicles/<?= (int) $lead['vehicle_id'] ?>/edit" class="btn btn-sm btn-outline-dark">Edit admin</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (! empty($lead['message'])): ?>
                <div class="kae-card mb-3">
                    <div class="kae-card-header">Message</div>
                    <div class="kae-card-body">
                        <p class="mb-0" style="white-space: pre-line"><?= e((string) $lead['message']) ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- RIGHT: actions + notes -->
        <div class="col-12 col-lg-6">
            <div class="kae-card mb-3">
                <div class="kae-card-header">Update status</div>
                <div class="kae-card-body">
                    <form method="POST" action="/admin/leads/<?= (int) $lead['id'] ?>/status" class="d-flex gap-2">
                        <?= csrf_field() ?><?= method_field('PUT') ?>
                        <select name="status" class="form-select form-select-sm">
                            <?php foreach ($statuses as $s): ?>
                                <option value="<?= e($s) ?>" <?= $lead['status']===$s?'selected':'' ?>><?= e($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-primary btn-sm">Save</button>
                    </form>
                </div>
            </div>

            <div class="kae-card">
                <div class="kae-card-header">Internal notes (<?= count($notes) ?>)</div>
                <div class="kae-card-body">
                    <form method="POST" action="/admin/leads/<?= (int) $lead['id'] ?>/notes" class="mb-3">
                        <?= csrf_field() ?>
                        <textarea name="body" rows="2" class="form-control form-control-sm" placeholder="Log a call, an update, anything…" required></textarea>
                        <button type="submit" class="btn btn-primary btn-sm mt-2">Add note</button>
                    </form>
                    <?php if (empty($notes)): ?>
                        <p class="text-muted small mb-0">No notes yet.</p>
                    <?php else: ?>
                        <ul class="list-unstyled mb-0">
                            <?php foreach ($notes as $n): ?>
                                <li class="border-top pt-3 mt-3">
                                    <div class="d-flex justify-content-between text-muted small">
                                        <span><?= e((string) ($n['author'] ?? 'system')) ?></span>
                                        <span><?= e(date('M j, H:i', strtotime((string) $n['created_at']))) ?></span>
                                    </div>
                                    <p class="mt-1 mb-0" style="white-space:pre-line"><?= e((string) $n['body']) ?></p>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->endSection(); ?>
