<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Exception\NotFoundException;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Repositories\LeadRepository;
use App\Repositories\VehicleRepository;
use App\Services\Estimate\ImportCostEstimator;
use App\Services\Setting\SettingService;

/**
 * Prospect proposal generator (print-to-PDF).
 *
 * Renders an A4-friendly HTML proposal that the admin opens, then uses
 * the browser's print dialog ("Save as PDF") to produce a file they can
 * attach to email / WhatsApp.
 *
 *   GET /admin/proposals/vehicle/{id}            — generic proposal
 *   GET /admin/proposals/vehicle/{id}?lead=N     — addressed to a specific lead
 */
final class ProposalController
{
    public function __construct(
        private View                 $view,
        private VehicleRepository    $vehicles,
        private LeadRepository       $leads,
        private ImportCostEstimator  $estimator,
        private SettingService       $settings,
    ) {}

    public function show(Request $request): Response
    {
        return $this->render(
            vehicleId: (int) $request->route('id', 0),
            leadId:    (int) $request->input('lead', 0),
            isPublic:  false,
        );
    }

    /**
     * Public proposal — accessible via the tokenized slug. No auth.
     * Route param `slug` is "{vehicleId}-{leadId}-{token}".
     */
    public function publicShow(Request $request): Response
    {
        $slug = (string) $request->route('slug', '');
        if (! preg_match('/^(\d+)-(\d+)-([a-f0-9]{16})$/', $slug, $m)) {
            throw new NotFoundException('Proposal not found');
        }
        $vehicleId = (int) $m[1];
        $leadId    = (int) $m[2];
        $token     = $m[3];

        // Constant-time check against the expected token
        if (! hash_equals(proposal_token($vehicleId, $leadId), $token)) {
            throw new NotFoundException('Proposal not found');
        }

        return $this->render(vehicleId: $vehicleId, leadId: $leadId, isPublic: true);
    }

    private function render(int $vehicleId, int $leadId, bool $isPublic): Response
    {
        $row = $this->vehicles->findRawById($vehicleId)
            ?? throw new NotFoundException("Vehicle {$vehicleId} not found");

        // Pull translations + inspection + images for richer proposal
        $translations = $this->vehicles->translationsFor($vehicleId);
        $byLocale     = [];
        foreach ($translations as $t) $byLocale[$t['locale']] = $t;
        $titleEn = $byLocale['en']['title']
            ?? trim(($row['brand_name'] ?? '') . ' ' . ($row['model_name'] ?? '') . ' ' . ($row['year'] ?? ''));

        $inspection = $this->vehicles->inspectionFor($vehicleId);
        $images     = $this->vehicles->imagesFor($vehicleId);

        // Optional: address to a lead
        $lead = $leadId > 0 ? $this->leads->find($leadId) : null;

        // Cost breakdown — uses current settings (same as the public calculator)
        $estimate = $this->estimator->estimate((float) $row['price_usd']);

        // Business info
        $business = [
            'name'     => (string) ($this->settings->get('site_name', 'ADY Motors') ?? 'ADY Motors'),
            'email'    => (string) ($this->settings->get('contact_email', '') ?? ''),
            'phone'    => (string) ($this->settings->get('contact_phone', '') ?? ''),
            'whatsapp' => (string) ($this->settings->get('whatsapp_number', '') ?? ''),
            'site_url' => (string) (config('app.url') ?? ''),
        ];

        return Response::html($this->view->render('admin/proposal', [
            'page_title' => 'Proposal — ' . $titleEn . ' · Admin',
            'vehicle'    => $row,
            'title_en'   => $titleEn,
            'cover'      => $images[0] ?? null,
            'images'     => array_slice($images, 0, 4),
            'inspection' => $inspection,
            'estimate'   => $estimate,
            'lead'       => $lead,
            'business'   => $business,
            'reference'  => 'PROP-' . str_pad((string) $vehicleId, 5, '0', STR_PAD_LEFT)
                          . ($lead ? '-L' . (int) $lead['id'] : ''),
            'generated'  => date('Y-m-d'),
            'is_public'  => $isPublic,
            'public_url' => proposal_public_url($vehicleId, $lead ? (int) $lead['id'] : 0),
        ]));
    }
}
