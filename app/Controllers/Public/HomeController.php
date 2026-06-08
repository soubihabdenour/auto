<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Repositories\BrandRepository;
use App\Repositories\ModelRepository;
use App\Repositories\TestimonialRepository;
use App\Repositories\VehicleRepository;
use App\Services\I18n\LocaleResolver;
use App\Services\Setting\SettingService;

final class HomeController
{
    public function __construct(
        private View                  $view,
        private LocaleResolver        $localeResolver,
        private VehicleRepository     $vehicles,
        private BrandRepository       $brands,
        private ModelRepository       $models,
        private TestimonialRepository $testimonials,
        private SettingService        $settings,
    ) {}

    public function redirectToDefaultLocale(Request $request): Response
    {
        $locale = $this->localeResolver->resolve($request);
        return Response::redirect('/' . $locale . '/');
    }

    public function index(Request $request): Response
    {
        $locale = current_locale();

        // Pull data with graceful fallback so the page still renders if the DB
        // hasn't been provisioned (Phase 0 demo state).
        $featured     = $this->safe(fn () => $this->vehicles->findFeatured(8, $locale));
        if (empty($featured)) {
            $featured = $this->safe(fn () => $this->vehicles->findLatest(8, $locale));
        }
        $testimonials = $this->safe(fn () => $this->testimonials->published($locale, 6));
        $brands       = $this->safe(fn () => $this->brands->allActive());

        $tagline = (string) $this->safe(fn () => (string) $this->settings->get(
            'site_tagline_' . $locale,
            t('home.hero.subheadline')
        ), t('home.hero.subheadline'));

        $html = $this->view->render('public/home', [
            'page_title'   => t('common.brand.name') . ' — ' . t('home.hero.short_title'),
            'featured'     => $featured,
            'testimonials' => $testimonials,
            'brands'       => $brands,
            'tagline'      => $tagline,
        ]);
        return Response::html($html);
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
