<?php

declare(strict_types=1);

use App\Core\Config;
use App\Core\Container;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Session;
use App\Core\View;
use App\Services\I18n\LocaleResolver;
use App\Services\I18n\Translator;

return function (Container $container, Config $config): void {
    $container->singleton(Session::class, function () use ($config): Session {
        return new Session([
            'name'      => (string) env('SESSION_NAME',     'kae_session'),
            'lifetime'  => (int)    env('SESSION_LIFETIME', 7200),
            'secure'    => filter_var(env('SESSION_SECURE', false), FILTER_VALIDATE_BOOLEAN),
            'samesite'  => (string) env('SESSION_SAMESITE', 'Lax'),
            'save_path' => BASE_PATH . '/storage/sessions',
        ]);
    });

    $container->singleton(Csrf::class, function (Container $c): Csrf {
        return new Csrf($c->get(Session::class));
    });

    $container->singleton(Database::class, function () use ($config): Database {
        return new Database((array) $config->get('database', []));
    });

    $container->singleton(View::class, function () use ($config): View {
        $view = new View((string) $config->get('app.paths.views'));
        // Defaults so error pages render even before LocaleMiddleware has run
        $view->share('locale',            (string) $config->get('locales.default', 'ar'));
        $view->share('dir',               in_array($config->get('locales.default'), (array) $config->get('locales.rtl', ['ar']), true) ? 'rtl' : 'ltr');
        $view->share('available_locales', (array)  $config->get('locales.available', ['ar', 'fr', 'en']));
        return $view;
    });

    $container->singleton(LocaleResolver::class, function () use ($config): LocaleResolver {
        return new LocaleResolver(
            (array)  $config->get('locales.available', ['ar', 'fr', 'en']),
            (string) $config->get('locales.default',   'ar'),
        );
    });

    $container->singleton(Translator::class, function () use ($config): Translator {
        return new Translator(
            langPath:        (string) $config->get('app.paths.lang'),
            defaultLocale:   (string) $config->get('locales.default',  'ar'),
            fallbackLocale:  (string) $config->get('locales.fallback', 'en'),
        );
    });
};
