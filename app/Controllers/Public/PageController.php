<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Repositories\BrandRepository;
use App\Repositories\TestimonialRepository;
use App\Services\Estimate\ImportCostEstimator;
use App\Services\Seo\SitemapGenerator;
use App\Services\Setting\SettingService;

final class PageController
{
    public function __construct(
        private View                  $view,
        private TestimonialRepository $testimonials,
        private BrandRepository       $brands,
        private SettingService        $settings,
        private SitemapGenerator      $sitemap,
        private ImportCostEstimator   $estimator,
    ) {}

    public function sitemap(Request $request): Response
    {
        $cached = $this->sitemap->cachedXml(3600);
        $xml = $cached ?? $this->sitemap->generateAndCache();
        return (new Response())
            ->status(200)
            ->header('Content-Type', 'application/xml; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=3600')
            ->body($xml);
    }

    public function robots(Request $request): Response
    {
        $base = (string) config('app.url', '');
        $body = "User-agent: *\n"
              . "Disallow: /admin/\n"
              . "Disallow: /events/\n"
              . "Allow: /\n\n"
              . "Sitemap: " . rtrim($base, '/') . "/sitemap.xml\n";
        return (new Response())
            ->status(200)
            ->header('Content-Type', 'text/plain; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=86400')
            ->body($body);
    }

    public function whyKorea(Request $request): Response
    {
        return $this->render('public/pages/why-korea', $request, t('pages.why_korea.title'));
    }

    public function importProcess(Request $request): Response
    {
        return $this->render('public/pages/import-process', $request, t('pages.process.title'));
    }

    public function testimonials(Request $request): Response
    {
        return $this->render('public/pages/testimonials', $request, t('pages.testimonials.title'), [
            'testimonials' => $this->safe(fn () => $this->testimonials->published(current_locale(), 24)),
        ]);
    }

    public function about(Request $request): Response
    {
        return $this->render('public/pages/about', $request, t('pages.about.title'));
    }

    public function contact(Request $request): Response
    {
        return $this->render('public/pages/contact', $request, t('pages.contact.title'), [
            'whatsapp_number' => $this->safe(fn () => (string) $this->settings->get('whatsapp_number', '')),
            'contact_email'   => $this->safe(fn () => (string) $this->settings->get('contact_email', '')),
            'contact_phone'   => $this->safe(fn () => (string) $this->settings->get('contact_phone', '')),
            'old'             => flash('_old') ?? [],
            'errors'          => flash('_errors') ?? [],
            'success'         => flash('contact_success'),
        ]);
    }

    public function requestVehicle(Request $request): Response
    {
        return $this->render('public/pages/request-vehicle', $request, t('pages.request_vehicle.title'), [
            'brands'  => $this->safe(fn () => $this->brands->allActive(), []),
            'old'     => flash('_old') ?? [],
            'errors'  => flash('_errors') ?? [],
            'success' => flash('request_success'),
        ]);
    }

    public function privacy(Request $request): Response
    {
        return $this->render('public/pages/privacy', $request, t('pages.privacy.title'));
    }

    /**
     * Interactive cost calculator. Renders the form server-side with the
     * current rate settings inlined so the JS can recompute live without
     * a round-trip; falling back to the server-side estimate when ?price=X
     * is passed (or JS is disabled).
     */
    public function costCalculator(Request $request): Response
    {
        $rates = [
            'shipping_base_usd'     => (float) $this->settings->get('estimator_shipping_base_usd', 1500.0),
            'customs_rate'          => (float) $this->settings->get('estimator_customs_rate', 0.30),
            'tva_rate'              => (float) $this->settings->get('estimator_tva_rate', 0.19),
            'service_fee_flat_usd'  => (float) $this->settings->get('estimator_service_fee_flat_usd', 500.0),
            'service_fee_percent'   => (float) $this->settings->get('estimator_service_fee_percent', 0.02),
            'fx_usd_to_dzd'         => (float) $this->settings->get('fx_usd_to_dzd', 135.0),
            'fx_usd_to_krw'         => (float) $this->settings->get('fx_usd_to_krw', 1380.0),
        ];

        $currency = strtolower((string) $request->input('currency', 'usd'));
        if (! in_array($currency, ['usd', 'krw'], true)) {
            $currency = 'usd';
        }
        $priceRaw = $request->input('price', '');
        $priceIn  = is_numeric($priceRaw) ? max(0.0, (float) $priceRaw) : null;

        // Normalise to USD for the estimator. 'krw' mode reads input as 만원
        // (= 10,000 KRW per unit), which is how Koreans actually quote prices.
        $priceUsd = null;
        $priceKrw = null;
        if ($priceIn !== null) {
            if ($currency === 'krw') {
                $priceKrw = $priceIn * 10000;
                $priceUsd = $rates['fx_usd_to_krw'] > 0 ? $priceKrw / $rates['fx_usd_to_krw'] : null;
            } else {
                $priceUsd = $priceIn;
                $priceKrw = $priceIn * $rates['fx_usd_to_krw'];
            }
        }
        $estimate = $priceUsd !== null ? $this->estimator->estimate($priceUsd) : null;

        return $this->render('public/pages/cost-calculator', $request, t('pages.cost_calculator.title'), [
            'currency'  => $currency,
            'price_in'  => $priceIn,
            'price_usd' => $priceUsd,
            'price_krw' => $priceKrw,
            'estimate'  => $estimate,
            'rates'     => $rates,
        ]);
    }

    public function terms(Request $request): Response
    {
        return $this->render('public/pages/terms', $request, t('pages.terms.title'));
    }

    private function render(string $template, Request $request, string $title, array $extra = []): Response
    {
        $locale = current_locale();
        $data = array_merge([
            'page_title' => $title . ' · ' . t('common.brand.name'),
            'meta_desc'  => $extra['meta_desc'] ?? null,
            'breadcrumb' => [
                ['name' => t('vehicle.detail.breadcrumb.home'),
                 'url'  => url(locale_url('/'))],
                ['name' => $title,
                 'url'  => url(locale_url('/' . ltrim(str_replace('public/pages/', '', $template), '/')))],
            ],
        ], $extra);

        return Response::html($this->view->render($template, $data));
    }

    /** Run a closure; swallow DB errors so the page still renders when DB is missing. */
    private function safe(\Closure $cb, mixed $fallback = []): mixed
    {
        try {
            return $cb();
        } catch (\Throwable) {
            return $fallback;
        }
    }
}
