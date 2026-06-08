<?php

declare(strict_types=1);

use App\Core\Application;
use App\Core\Config;
use App\Core\Container;
use App\Core\Router;

return function (Container $container, Config $config): void {
    $container->singleton(Router::class, function (Container $c): Router {
        $router = new Router($c);
        $routes = require BASE_PATH . '/config/routes.php';
        $routes($router);
        return $router;
    });

    $container->singleton(Application::class, function (Container $c) use ($config): Application {
        return new Application(
            $c,
            $c->get(Router::class),
            $config,
        );
    });
};
