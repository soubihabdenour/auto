<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\Exception\NotFoundException;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Repositories\BodyTypeRepository;
use App\Repositories\BrandRepository;
use App\Repositories\ModelRepository;
use App\Repositories\VehicleRepository;
use App\Repositories\VehicleSearchCriteria;
use App\Services\Estimate\ImportCostEstimator;
use App\Services\Lead\WhatsAppLinkBuilder;
use App\Services\Seo\VehicleSchemaBuilder;
use App\Services\Setting\SettingService;

final class VehicleController
{
    public function __construct(
        private View                $view,
        private VehicleRepository   $vehicles,
        private BrandRepository     $brands,
        private ModelRepository     $models,
        private BodyTypeRepository  $bodyTypes,
        private ImportCostEstimator   $estimator,
        private WhatsAppLinkBuilder   $whatsapp,
        private SettingService        $settings,
        private VehicleSchemaBuilder  $schemaBuilder,
    ) {}

    public function index(Request $request): Response
    {
        $locale   = current_locale();
        $criteria = VehicleSearchCriteria::fromArray($request->query);

        [$rows, $total] = $this->safeSearch($criteria, $locale);

        $html = $this->view->render('public/vehicles/index', [
            'page_title' => t('vehicle.list.title') . ' · ' . t('common.brand.name'),
            'meta_desc'  => t('vehicle.list.subtitle'),
            'criteria'   => $criteria,
            'results'    => $rows,
            'total'      => $total,
            'pages'      => max(1, (int) ceil($total / $criteria->perPage)),
            'brands'     => $this->safe(fn () => $this->brands->allActive()),
            'models'     => $this->safe(fn () => $this->models->allActive()),
            'body_types' => $this->safe(fn () => $this->bodyTypes->all($locale)),
            'breadcrumb' => [
                ['name' => t('vehicle.detail.breadcrumb.home'),     'url' => url(locale_url('/'))],
                ['name' => t('vehicle.detail.breadcrumb.vehicles'), 'url' => url(locale_url('/vehicles'))],
            ],
        ]);
        return Response::html($html);
    }

    /**
     * AJAX endpoint. Returns JSON: { html, count, page, pages, total }.
     */
    public function filter(Request $request): Response
    {
        $locale   = current_locale();
        $criteria = VehicleSearchCriteria::fromArray($request->query);

        [$rows, $total] = $this->safeSearch($criteria, $locale);

        $html = $this->view->render('public/vehicles/_results', [
            'results'  => $rows,
            'criteria' => $criteria,
            'total'    => $total,
            'pages'    => max(1, (int) ceil($total / $criteria->perPage)),
        ]);

        return Response::json([
            'html'  => $html,
            'count' => count($rows),
            'page'  => $criteria->page,
            'pages' => max(1, (int) ceil($total / $criteria->perPage)),
            'total' => $total,
        ]);
    }

    public function show(Request $request): Response
    {
        $locale = current_locale();
        $slug   = (string) $request->route('slug', '');
        if ($slug === '') {
            throw new NotFoundException();
        }

        $vehicle = null;
        try {
            $vehicle = $this->vehicles->findBySlug($slug, $locale);
        } catch (\Throwable) {
            // DB unavailable
        }
        if ($vehicle === null) {
            throw new NotFoundException("Vehicle not found: {$slug}");
        }

        // Fire-and-forget view counter
        try { $this->vehicles->incrementViews((int) $vehicle['id']); } catch (\Throwable) {}

        $images     = $this->safe(fn () => $this->vehicles->imagesFor((int) $vehicle['id']));
        $videos     = $this->safe(fn () => $this->vehicles->videosFor((int) $vehicle['id']));
        $inspection = null;
        try {
            $inspection = $this->vehicles->inspectionFor((int) $vehicle['id']);
        } catch (\Throwable) {}
        $similar = $this->safe(fn () => $this->vehicles->findSimilar(
            (int) $vehicle['id'],
            (int) $vehicle['brand_id'],
            (int) ($vehicle['body_type_id'] ?? 0),
            $locale,
            4
        ));

        $estimate = $this->estimator->estimate((float) $vehicle['price_usd']);

        $vehicleUrl = url(locale_url('/vehicles/' . $vehicle['slug']));
        $waLink     = $this->whatsapp->forVehicle($locale, (string) $vehicle['title'], $vehicleUrl);
        $schema     = $this->schemaBuilder->build($vehicle, $images, $vehicleUrl);

        $html = $this->view->render('public/vehicles/show', [
            'page_title'  => ($vehicle['meta_title'] ?? $vehicle['title']) . ' · ' . t('common.brand.name'),
            'meta_desc'   => $vehicle['meta_description'] ?? null,
            'vehicle'     => $vehicle,
            'images'      => $images,
            'videos'      => $videos,
            'inspection'  => $inspection,
            'similar'     => $similar,
            'estimate'    => $estimate,
            'wa_link'     => $waLink,
            'fx_rate'     => $estimate['fx_rate'],
            'vehicle_url' => $vehicleUrl,
            'json_ld'     => $schema,
            'breadcrumb'  => [
                ['name' => t('vehicle.detail.breadcrumb.home'),     'url' => url(locale_url('/'))],
                ['name' => t('vehicle.detail.breadcrumb.vehicles'), 'url' => url(locale_url('/vehicles'))],
                ['name' => (string) $vehicle['title'],              'url' => $vehicleUrl],
            ],
        ]);
        return Response::html($html);
    }

    /**
     * @return array{0: array<int, array<string,mixed>>, 1: int}
     */
    private function safeSearch(VehicleSearchCriteria $c, string $locale): array
    {
        try {
            return $this->vehicles->search($c, $locale);
        } catch (\Throwable) {
            return [[], 0];
        }
    }

    private function safe(\Closure $cb, mixed $fallback = []): mixed
    {
        try {
            return $cb();
        } catch (\Throwable) {
            return $fallback;
        }
    }
}
