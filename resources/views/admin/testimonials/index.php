<?php
/** @var \App\Core\View $this  @var array $rows */
$this->extends('layouts/admin');
?>
<?php $this->section('content'); ?>
<div class="container-fluid">
    <div class="kae-page-head">
        <div><h1>Testimonials</h1><p class="text-muted mb-0 small"><?= count($rows) ?> total</p></div>
        <a href="/admin/testimonials/create" class="btn btn-primary btn-sm">+ Add</a>
    </div>
    <div class="kae-card">
        <?php if (empty($rows)): ?>
            <div class="kae-admin-empty m-3">No testimonials yet.</div>
        <?php else: ?>
            <table class="kae-table">
                <thead><tr><th>#</th><th>Customer</th><th>City</th><th>Vehicle</th><th>Rating</th><th>Pub</th><th>Order</th><th></th></tr></thead>
                <tbody>
                    <?php foreach ($rows as $t): ?>
                    <tr>
                        <td class="text-muted">#<?= (int) $t['id'] ?></td>
                        <td><a href="/admin/testimonials/<?= (int) $t['id'] ?>/edit" class="text-decoration-none fw-semibold"><?= e((string) $t['customer_name']) ?></a></td>
                        <td class="text-muted small"><?= e((string) ($t['customer_city'] ?? '—')) ?></td>
                        <td class="text-muted small"><?= e((string) ($t['vehicle_purchased'] ?? '—')) ?></td>
                        <td><?= str_repeat('★', (int) $t['rating']) ?></td>
                        <td><?= ! empty($t['is_published']) ? '<span class="kae-status kae-status-available">live</span>' : '<span class="kae-status kae-status-draft">draft</span>' ?></td>
                        <td class="text-muted small"><?= (int) $t['sort_order'] ?></td>
                        <td class="actions text-end"><a href="/admin/testimonials/<?= (int) $t['id'] ?>/edit">Edit</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
<?php $this->endSection(); ?>
