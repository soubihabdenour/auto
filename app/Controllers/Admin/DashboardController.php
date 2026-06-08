<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Repositories\LeadRepository;
use App\Repositories\VehicleRepository;

final class DashboardController
{
    public function __construct(
        private View              $view,
        private VehicleRepository $vehicles,
        private LeadRepository    $leads,
    ) {}

    public function index(Request $request): Response
    {
        $vCounts        = $this->safe(fn () => $this->vehicles->countsByStatus(), []);
        $totalVehicles  = array_sum($vCounts);
        $available      = (int) ($vCounts['available'] ?? 0);
        $reserved       = (int) ($vCounts['reserved']  ?? 0);
        $sold           = (int) ($vCounts['sold']      ?? 0);

        $totalLeads     = $this->safe(fn () => $this->leads->countTotal(), 0);
        $newLeads       = $this->safe(fn () => $this->leads->countByStatus('new'), 0);
        $weekAgo        = date('Y-m-d H:i:s', strtotime('-7 days') ?: time() - 7 * 86400);
        $leadsThisWeek  = $this->safe(fn () => $this->leads->countSince($weekAgo), 0);

        $recentLeads    = $this->safe(fn () => $this->leads->recent(8));
        $popularVehicles= $this->safe(fn () => $this->vehicles->findMostViewed(5, 'en'));

        $html = $this->view->render('admin/dashboard', [
            'page_title'       => 'Dashboard · Admin',
            'totalVehicles'    => $totalVehicles,
            'available'        => $available,
            'reserved'         => $reserved,
            'sold'             => $sold,
            'totalLeads'       => $totalLeads,
            'newLeads'         => $newLeads,
            'leadsThisWeek'    => $leadsThisWeek,
            'recentLeads'      => $recentLeads,
            'popularVehicles'  => $popularVehicles,
        ]);
        return Response::html($html);
    }

    private function safe(\Closure $cb, mixed $fallback = []): mixed
    {
        try { return $cb(); }
        catch (\Throwable) { return $fallback; }
    }
}
