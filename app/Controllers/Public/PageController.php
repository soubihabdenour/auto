<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Repositories\BrandRepository;
use App\Repositories\TestimonialRepository;
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
