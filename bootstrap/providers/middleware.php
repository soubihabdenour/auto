<?php

declare(strict_types=1);

use App\Core\Config;
use App\Core\Container;
use App\Core\Csrf;
use App\Core\View;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\LocaleMiddleware;
use App\Middleware\RateLimitMiddleware;
use App\Services\Auth\AuthService;
use App\Services\I18n\LocaleResolver;
use App\Services\I18n\Translator;

return function (Container $container, Config $config): void {
    $container->bind(LocaleMiddleware::class, function (Container $c): LocaleMiddleware {
        return new LocaleMiddleware(
            $c->get(LocaleResolver::class),
            $c->get(Translator::class),
            $c->get(View::class),
        );
    });

    $container->bind(CsrfMiddleware::class, function (Container $c): CsrfMiddleware {
        return new CsrfMiddleware($c->get(Csrf::class));
    });

    $container->bind(AuthMiddleware::class, function (Container $c): AuthMiddleware {
        return new AuthMiddleware($c->get(AuthService::class), $c->get(View::class));
    });

    $container->bind(RateLimitMiddleware::class, function (): RateLimitMiddleware {
        return new RateLimitMiddleware(
            storagePath: BASE_PATH . '/storage/cache',
            maxPerHour:  (int) env('RATE_LIMIT_LEAD_PER_HOUR', 5),
        );
    });
};
