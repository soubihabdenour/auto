<?php
/** @var \App\Core\View $this  @var array $rows  @var int $total $page $pages  @var array $filters $statuses $types $sources */
$this->extends('layouts/admin');
?>
<?php $this->section('content'); ?>
<div class="container-fluid">
    <div class="kae-page-head">
        <div>
            <h1>Leads</h1>
            <p class="text-muted mb-0 small"><?= number_format($total) ?> total</p>
        </div>
        <div class="d-flex gap-2">
            <a href="/admin/leads/export?<?= e(http_build_query($filters)) ?>" class="btn btn-outline-dark btn-sm">Export CSV</a>
        </div>
    </div>

    <form method="GET" action="/admin/leads" class="kae-card mb-3 p-3 d-flex flex-wrap gap-2 align-items-end">
        <div class="flex-grow-1">
            <label class="form-label small text-uppercase text-muted" for="f-q">Search</label>
            <input type="search" name="q" id="f-q" value="<?= e((string) ($filters['q'] ?? '')) ?>" class="form-control form-control-sm" placeholder="name, phone or email">
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
        <div>
            <label class="form-label small text-uppercase text-muted" for="f-type">Type</label>
            <select name="lead_type" id="f-type" class="form-select form-select-sm">
                <option value="">All</option>
                <?php foreach ($types as $t): ?>
                    <option value="<?= e($t) ?>" <?= ($filters['lead_type']??'')===$t?'selected':'' ?>><?= e($t) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="form-label small text-uppercase text-muted" for="f-source">Source</label>
            <select name="source" id="f-source" class="form-select form-select-sm">
                <option value="">All</option>
                <?php foreach ($sources as $s): ?>
                    <option value="<?= e($s) ?>" <?= ($filters['source']??'')===$s?'selected':'' ?>><?= e($s) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-dark btn-sm">Filter</button>
            <a href="/admin/leads" class="btn btn-link btn-sm">Reset</a>
        </div>
    </form>

    <div class="kae-card">
        <?php if (empty($rows)): ?>
            <div class="kae-admin-empty m-3">No leads match these filters.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="kae-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Vehicle</th>
                            <th>Status</th>
                            <th>Assignee</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $l): ?>
                        <tr>
                            <td class="text-muted">#<?= (int) $l['id'] ?></td>
                            <td class="text-muted small"><?= e(date('M j, H:i', strtotime((string) $l['created_at']))) ?></td>
                            <td><?= e((string) $l['lead_type']) ?></td>
                            <td>
                                <a href="/admin/leads/<?= (int) $l['id'] ?>" class="text-decoration-none fw-semibold"><?= e((string) $l['name']) ?></a>
                            </td>
                            <td class="text-muted small"><?= e((string) $l['phone']) ?></td>
                            <td class="text-muted small"><?= e((string) ($l['vehicle_label'] ?? '—')) ?></td>
                            <td><span class="kae-status kae-status-<?= e($l['status']) ?>"><?= e($l['status']) ?></span></td>
                            <td class="text-muted small"><?= e((string) ($l['assignee_name'] ?? '—')) ?></td>
                            <td class="actions text-end"><a href="/admin/leads/<?= (int) $l['id'] ?>">Open</a></td>
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
                        <a class="page-link" href="/admin/leads?<?= e($qs) ?>"><?= $p ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>
<?php $this->endSection(); ?>
