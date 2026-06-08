<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Exception\NotFoundException;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Services\I18n\LocaleResolver;
use App\Services\I18n\Translator;
use Closure;

final class LocaleMiddleware implements MiddlewareInterface
{
    public function __construct(
        private LocaleResolver $resolver,
        private Translator     $translator,
        private View           $view,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        // If the route URL provided an explicit locale segment, reject when
        // it's not one of the supported locales — prevents /admin matching
        // the /{locale} pattern and silently rendering the homepage.
        $urlLocale = $request->route('locale');
        if ($urlLocale !== null && ! $this->resolver->isSupported($urlLocale)) {
            throw new NotFoundException("Unsupported locale: {$urlLocale}");
        }

        $locale = $this->resolver->resolve($request);
        $this->translator->setLocale($locale);

        // Make locale + dir available to every view automatically
        $this->view->share('locale', $locale);
        $this->view->share('dir', in_array($locale, ['ar'], true) ? 'rtl' : 'ltr');
        $this->view->share('available_locales', $this->resolver->available());

        // Persist locale in a long-lived cookie (re-set every request to refresh expiry)
        if (! headers_sent()) {
            setcookie('locale', $locale, [
                'expires'  => time() + 31_536_000,
                'path'     => '/',
                'samesite' => 'Lax',
                'secure'   => (bool) ($_SERVER['HTTPS'] ?? false),
                'httponly' => false,
            ]);
        }

        return $next($request);
    }
}
