<?php

declare(strict_types=1);

use App\Core\Container;
use App\Core\Config;
use App\Core\Csrf;
use App\Services\I18n\Translator;

if (! function_exists('app')) {
    /**
     * Resolve an instance from the global container.
     * Usage: app(MyService::class)  OR  app() to get the container.
     */
    function app(?string $abstract = null): mixed
    {
        /** @var Container $container */
        $container = $GLOBALS['app_container']
            ?? throw new RuntimeException('Application container not bootstrapped.');
        return $abstract === null ? $container : $container->get($abstract);
    }
}

if (! function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        return app(Config::class)->get($key, $default);
    }
}

if (! function_exists('env')) {
    /** Read an env var (falling back to default). */
    function env(string $key, mixed $default = null): mixed
    {
        $value = getenv($key);
        if ($value === false) {
            return $default;
        }
        return match (strtolower($value)) {
            'true', '(true)'   => true,
            'false', '(false)' => false,
            'null', '(null)'   => null,
            'empty', '(empty)' => '',
            default            => $value,
        };
    }
}

if (! function_exists('e')) {
    /** HTML-escape for safe output in templates. */
    function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (! function_exists('t')) {
    /** Translate a key. @param array<string, scalar> $replace */
    function t(string $key, array $replace = []): string
    {
        return app(Translator::class)->get($key, $replace);
    }
}

if (! function_exists('t_arr')) {
    /** Look up a translation key whose value is an array (lists of items). */
    function t_arr(string $key): array
    {
        return app(Translator::class)->getArray($key);
    }
}

if (! function_exists('current_locale')) {
    function current_locale(): string
    {
        return app(Translator::class)->locale();
    }
}

if (! function_exists('url')) {
    /** Absolute URL based on APP_URL config. */
    function url(string $path = ''): string
    {
        $base = (string) config('app.url', '');
        return $base . '/' . ltrim($path, '/');
    }
}

if (! function_exists('locale_url')) {
    /** Prefix a path with the current locale (e.g. "/vehicles" → "/ar/vehicles"). */
    function locale_url(string $path = '', ?string $locale = null): string
    {
        $locale = $locale ?? current_locale();
        $path   = '/' . ltrim($path, '/');
        return '/' . $locale . ($path === '/' ? '' : $path);
    }
}

if (! function_exists('asset')) {
    /** Path to a static asset under public/assets/. */
    function asset(string $path): string
    {
        return '/assets/' . ltrim($path, '/');
    }
}

if (! function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return app(Csrf::class)->token();
    }
}

if (! function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
    }
}

if (! function_exists('method_field')) {
    function method_field(string $method): string
    {
        return '<input type="hidden" name="_method" value="' . e(strtoupper($method)) . '">';
    }
}

if (! function_exists('old')) {
    /** Repopulate form field from flashed old input. */
    function old(string $key, mixed $default = ''): mixed
    {
        $flash = app(\App\Core\Session::class)->getFlash('_old', []);
        return is_array($flash) && array_key_exists($key, $flash) ? $flash[$key] : $default;
    }
}

if (! function_exists('flash')) {
    function flash(string $key, mixed $value = null): mixed
    {
        $session = app(\App\Core\Session::class);
        if ($value === null) {
            return $session->getFlash($key);
        }
        $session->flash($key, $value);
        return $value;
    }
}

if (! function_exists('is_rtl')) {
    function is_rtl(?string $locale = null): bool
    {
        $locale = $locale ?? current_locale();
        $rtl = (array) config('locales.rtl', ['ar']);
        return in_array($locale, $rtl, true);
    }
}

if (! function_exists('format_price')) {
    /**
     * Format a USD amount per Algerian convention (Latin numerals, locale-aware separators).
     * Returns e.g. "$19,500" or "19 500 $".
     */
    function format_price(float $amount, string $currency = 'USD', ?string $locale = null): string
    {
        $locale = $locale ?? current_locale();
        $rounded = (int) round($amount);
        $sep   = $locale === 'fr' ? ' ' : ',';
        $body  = number_format($rounded, 0, '.', $sep);
        return match ($currency) {
            'USD' => $locale === 'fr' ? $body . ' $' : '$' . $body,
            'DZD' => $body . ' DA',
            default => $body . ' ' . $currency,
        };
    }
}

if (! function_exists('format_mileage')) {
    /** "35,420 km" or "35 420 كم". */
    function format_mileage(int $km, ?string $locale = null): string
    {
        $locale = $locale ?? current_locale();
        $sep    = $locale === 'fr' ? ' ' : ',';
        $unit   = $locale === 'ar' ? 'كم' : 'km';
        return number_format($km, 0, '.', $sep) . ' ' . $unit;
    }
}

if (! function_exists('vehicle_url')) {
    /** Localized URL for a vehicle slug. */
    function vehicle_url(string $slug, ?string $locale = null): string
    {
        return locale_url('/vehicles/' . $slug, $locale);
    }
}

if (! function_exists('image_url')) {
    /** Resolve a stored image path. Empty path → placeholder. */
    function image_url(?string $path, string $fallback = '/assets/img/vehicle-placeholder.svg'): string
    {
        if ($path === null || $path === '') {
            return $fallback;
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        return '/uploads/' . ltrim($path, '/');
    }
}

if (! function_exists('vehicle_picture')) {
    /**
     * Emit a <picture> with WebP + JPEG sources sized for vehicle cards / detail.
     *
     * Accepts a stored path like "vehicles/42/large/abcd1234.jpg".
     * Generates srcset for 400/800/1600 widths by swapping the `/large/` segment.
     * Returns null when no image is provided so the caller can fall back to the SVG.
     *
     * @param ?string $path     stored path (large variant)
     * @param string  $alt
     * @param string  $sizes    sizes attribute, e.g. "(min-width:992px) 25vw, 50vw"
     * @param string  $loading  "lazy" or "eager"
     */
    function vehicle_picture(?string $path, string $alt, string $sizes = '(min-width:992px) 25vw, 50vw', string $loading = 'lazy'): string
    {
        if ($path === null || $path === '') {
            $fallback = image_url(null);
            return '<img src="' . e($fallback) . '" alt="' . e($alt) . '" loading="' . e($loading) . '" decoding="async">';
        }
        // Path convention: vehicles/{id}/{role}/{token}.{ext}
        // Build URLs for three roles
        $thumb  = (string) preg_replace('#/(large|medium|thumb|orig)/#', '/thumb/',  $path);
        $medium = (string) preg_replace('#/(large|medium|thumb|orig)/#', '/medium/', $path);
        $large  = (string) preg_replace('#/(large|medium|thumb|orig)/#', '/large/',  $path);

        $url = fn (string $p, string $ext) => image_url(preg_replace('/\.(jpe?g|webp|png)$/i', '.' . $ext, $p));

        $webpSrcset = $url($thumb, 'webp')  . ' 400w, '
                    . $url($medium, 'webp') . ' 800w, '
                    . $url($large, 'webp')  . ' 1600w';
        $jpegSrcset = $url($thumb, 'jpg')   . ' 400w, '
                    . $url($medium, 'jpg')  . ' 800w, '
                    . $url($large, 'jpg')   . ' 1600w';

        return '<picture>'
            . '<source type="image/webp" srcset="' . e($webpSrcset) . '" sizes="' . e($sizes) . '">'
            . '<img src="' . e($url($medium, 'jpg')) . '"'
            . ' srcset="' . e($jpegSrcset) . '"'
            . ' sizes="' . e($sizes) . '"'
            . ' alt="' . e($alt) . '"'
            . ' loading="' . e($loading) . '" decoding="async">'
            . '</picture>';
    }
}
