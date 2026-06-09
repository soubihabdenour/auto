<?php
/** @var \App\Core\View $this  @var array $rows  @var int $total $page $pages  @var array $filters $statuses */
$this->extends('layouts/admin');
?>
<?php $this->section('content'); ?>
<div class="container-fluid">
    <div class="kae-page-head">
        <div>
            <h1>Reservations</h1>
            <p class="text-muted mb-0 small"><?= number_format($total) ?> total</p>
        </div>
    </div>

    <form method="GET" action="/admin/reservations" class="kae-card mb-3 p-3 d-flex flex-wrap gap-2 align-items-end">
        <div class="flex-grow-1">
            <label class="form-label small text-uppercase text-muted" for="f-q">Search</label>
            <input type="search" name="q" id="f-q" value="<?= e((string) ($filters['q'] ?? '')) ?>"
                   class="form-control form-control-sm" placeholder="reference, name, phone or email">
        </div>
        <div>
            <label class="form-label small text-uppercase text-muted" for="f-status">Status</label>
            <select name="status" id="f-status" class="form-select form-select-sm">
                <option value="">All</option>
                <?php foreach ($statuses as $s): ?>
                    <option value="<?= e($s) ?>" <?= ($filters['status']??'')===$s?'selected':'' ?>><?= e($s) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-dark btn-sm">Filter</button>
            <a href="/admin/reservations" class="btn btn-link btn-sm">Reset</a>
        </div>
    </form>

    <div class="kae-card">
        <?php if (empty($rows)): ?>
            <div class="kae-admin-empty m-3">No reservations match these filters.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="kae-table">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Phone</th>
                            <th>Vehicle</th>
                            <th>Deposit</th>
                            <th>Expires</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $r): ?>
                        <tr>
                            <td><code class="text-muted"><?= e((string) $r['reference']) ?></code></td>
                            <td class="text-muted small"><?= e(date('M j, H:i', strtotime((string) $r['created_at']))) ?></td>
                            <td>
                                <a href="/admin/reservations/<?= (int) $r['id'] ?>" class="text-decoration-none fw-semibold"><?= e((string) $r['name']) ?></a>
                            </td>
                            <td class="text-muted small"><?= e((string) $r['phone']) ?></td>
                            <td class="text-muted small"><?= e((string) ($r['vehicle_label'] ?? '—')) ?></td>
                            <td class="fw-semibold">$<?= e(number_format((float) $r['deposit_amount_usd'], 2)) ?></td>
                            <td class="text-muted small"><?= e(date('M j, H:i', strtotime((string) $r['expires_at']))) ?></td>
                            <td><span class="kae-status kae-status-<?= e($r['status']) ?>"><?= e((string) $r['status']) ?></span></td>
                            <td class="actions text-end"><a href="/admin/reservations/<?= (int) $r['id'] ?>">Open</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($pages > 1): ?>
        <nav class="kae-pagination mt-3">
            <ul class="pagination">
                <?php for ($p = 1; $p <= $pages; $p++): ?>
                    <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                        <?php $qs = http_build_query(array_merge($filters, ['page'=>$p])); ?>
                        <a class="page-link" href="/admin/reservations?<?= e($qs) ?>"><?= $p ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>
<?php $this->endSection(); ?>
