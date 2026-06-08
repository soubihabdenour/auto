<?php
/**
 * @var \App\Core\View $this
 * @var int   $totalVehicles
 * @var int   $available
 * @var int   $reserved
 * @var int   $sold
 * @var int   $totalLeads
 * @var int   $newLeads
 * @var int   $leadsThisWeek
 * @var array $recentLeads
 * @var array $popularVehicles
 */
$this->extends('layouts/admin');
?>
<?php $this->section('content'); ?>
<div class="container-fluid">
    <div class="kae-page-head">
        <div>
            <h1>Dashboard</h1>
            <p class="text-muted mb-0 small">Welcome back, <?= e($current_user['name'] ?? '') ?>.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="/admin/vehicles/create" class="btn btn-primary btn-sm">+ Add vehicle</a>
        </div>
    </div>

    <!-- KPIs -->
    <div class="kae-kpi-grid">
        <div class="kae-kpi">
            <div class="kae-kpi-label">Vehicles</div>
            <div class="kae-kpi-value"><?= number_format($totalVehicles) ?></div>
            <div class="kae-kpi-delta">
                <?= number_format($available) ?> available · <?= number_format($reserved) ?> reserved · <?= number_format($sold) ?> sold
            </div>
        </div>
        <div class="kae-kpi">
            <div class="kae-kpi-label">Total leads</div>
            <div class="kae-kpi-value"><?= number_format($totalLeads) ?></div>
            <div class="kae-kpi-delta"><?= number_format($newLeads) ?> awaiting follow-up</div>
        </div>
        <div class="kae-kpi">
            <div class="kae-kpi-label">Last 7 days</div>
            <div class="kae-kpi-value"><?= number_format($leadsThisWeek) ?></div>
            <div class="kae-kpi-delta">new leads</div>
        </div>
        <div class="kae-kpi">
            <div class="kae-kpi-label">Reserved</div>
            <div class="kae-kpi-value"><?= number_format($reserved) ?></div>
            <div class="kae-kpi-delta">vehicles awaiting deposit clearance</div>
        </div>
    </div>

    <div class="row g-3">
        <!-- Recent leads -->
        <div class="col-12 col-xl-7">
            <div class="kae-card">
                <div class="kae-card-header d-flex justify-content-between align-items-center">
                    Recent leads
                    <a href="/admin/leads" class="text-decoration-none small">View all →</a>
                </div>
                <?php if (empty($recentLeads)): ?>
                    <div class="kae-admin-empty m-3">No leads yet. They'll appear here once visitors submit forms.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="kae-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Name</th>
                                    <th>Vehicle</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentLeads as $l): ?>
                                <tr>
                                    <td class="text-muted"><?= e(date('M j, H:i', strtotime((string) $l['created_at']))) ?></td>
                                    <td><?= e((string) $l['lead_type']) ?></td>
                                    <td><?= e((string) $l['name']) ?></td>
                                    <td class="text-muted small"><?= e((string) ($l['vehicle_label'] ?? '—')) ?></td>
                                    <td><span class="kae-status kae-status-<?= e($l['status']) ?>"><?= e($l['status']) ?></span></td>
                                    <td class="actions"><a href="/admin/leads/<?= (int) $l['id'] ?>">Open</a></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Popular vehicles -->
        <div class="col-12 col-xl-5">
            <div class="kae-card">
                <div class="kae-card-header d-flex justify-content-between align-items-center">
                    Most-viewed vehicles
                    <a href="/admin/vehicles" class="text-decoration-none small">All →</a>
                </div>
                <?php if (empty($popularVehicles)): ?>
                    <div class="kae-admin-empty m-3">No vehicles yet. Add the first one to start.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="kae-table">
                            <thead>
                                <tr><th>Vehicle</th><th class="text-end">Views</th><th class="text-end">Price</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($popularVehicles as $v): ?>
                                <tr>
                                    <td>
                                        <a href="/admin/vehicles/<?= (int) $v['id'] ?>/edit" class="text-decoration-none">
                                            <?= e((string) $v['title']) ?>
                                        </a>
                                        <div class="text-muted small"><?= (int) $v['year'] ?> · <?= e((string) $v['fuel_type']) ?></div>
                                    </td>
                                    <td class="text-end"><?= number_format((int) ($v['views_count'] ?? 0)) ?></td>
                                    <td class="text-end fw-bold"><?= e(format_price((float) $v['price_usd'], 'USD', 'en')) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $this->endSection(); ?>
