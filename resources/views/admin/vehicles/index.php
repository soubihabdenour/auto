<?php
/**
 * @var \App\Core\View $this
 * @var array $rows
 * @var int   $total
 * @var int   $page
 * @var int   $pages
 * @var array $filters
 * @var array $brands
 * @var array $statuses
 */
$this->extends('layouts/admin');
?>
<?php $this->section('content'); ?>
<div class="container-fluid">
    <div class="kae-page-head">
        <div>
            <h1>Vehicles</h1>
            <p class="text-muted mb-0 small"><?= number_format($total) ?> total</p>
        </div>
        <div class="d-flex gap-2">
            <a href="/admin/vehicles/create" class="btn btn-primary btn-sm">+ Add vehicle</a>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" action="/admin/vehicles" class="kae-card mb-3 p-3 d-flex flex-wrap gap-2 align-items-end">
        <div class="flex-grow-1">
            <label class="form-label small text-uppercase text-muted" for="f-q">Search</label>
            <input type="search" name="q" id="f-q" value="<?= e((string) ($filters['q'] ?? '')) ?>" class="form-control form-control-sm" placeholder="slug or VIN">
        </div>
        <div>
            <label class="form-label small text-uppercase text-muted" for="f-status">Status</label>
            <select name="status" id="f-status" class="form-select form-select-sm" style="min-width:140px">
                <option value="">All</option>
                <?php foreach ($statuses as $s): ?>
                    <option value="<?= e($s) ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= e($s) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="form-label small text-uppercase text-muted" for="f-brand">Brand</label>
            <select name="brand_id" id="f-brand" class="form-select form-select-sm" style="min-width:160px">
                <option value="">All</option>
                <?php foreach ($brands as $b): ?>
                    <option value="<?= (int) $b['id'] ?>" <?= (int) ($filters['brand_id'] ?? 0) === (int) $b['id'] ? 'selected' : '' ?>>
                        <?= e((string) $b['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-dark btn-sm">Filter</button>
            <a href="/admin/vehicles" class="btn btn-link btn-sm">Reset</a>
        </div>
    </form>

    <div class="kae-card">
        <?php if (empty($rows)): ?>
            <div class="kae-admin-empty m-3">
                <p class="mb-2">No vehicles match these filters.</p>
                <a href="/admin/vehicles/create" class="btn btn-primary btn-sm">+ Add the first vehicle</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="kae-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Brand</th>
                            <th>Year</th>
                            <th>km</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Views</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $v): ?>
                        <tr>
                            <td class="text-muted">#<?= (int) $v['id'] ?></td>
                            <td>
                                <a href="/admin/vehicles/<?= (int) $v['id'] ?>/edit" class="text-decoration-none fw-semibold">
                                    <?= e((string) $v['brand_name']) ?> <?= e((string) $v['model_name']) ?>
                                </a>
                                <?php if (! empty($v['is_featured'])): ?>
                                    <span class="badge bg-danger ms-1">★</span>
                                <?php endif; ?>
                                <div class="text-muted small"><?= e((string) $v['slug']) ?></div>
                            </td>
                            <td><?= e((string) $v['brand_name']) ?></td>
                            <td><?= (int) $v['year'] ?></td>
                            <td><?= number_format((int) $v['mileage_km']) ?></td>
                            <td class="fw-bold"><?= e(format_price((float) $v['price_usd'], 'USD', 'en')) ?></td>
                            <td><span class="kae-status kae-status-<?= e($v['status']) ?>"><?= e($v['status']) ?></span></td>
                            <td class="text-muted small"><?= number_format((int) ($v['views_count'] ?? 0)) ?></td>
                            <td class="actions text-end">
                                <a href="/admin/vehicles/<?= (int) $v['id'] ?>/edit">Edit</a>
                                <a href="/<?= e(config('locales.default')) ?>/vehicles/<?= e((string) $v['slug']) ?>" target="_blank" class="text-muted">View ↗</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($pages > 1): ?>
        <nav class="kae-pagination mt-3" aria-label="pagination">
            <ul class="pagination">
                <?php for ($p = 1; $p <= $pages; $p++): ?>
                    <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                        <?php $qs = http_build_query(array_merge($filters, ['page' => $p])); ?>
                        <a class="page-link" href="/admin/vehicles?<?= e($qs) ?>"><?= $p ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>
<?php $this->endSection(); ?>
