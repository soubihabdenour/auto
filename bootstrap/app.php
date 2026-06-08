<?php

declare(strict_types=1);

use App\Core\Config;
use App\Core\Container;
use App\Core\Env;

// 1. Load .env (if present)
Env::load(BASE_PATH . '/.env');

// 2. Boot the container + config
$container = new Container();
$GLOBALS['app_container'] = $container;

$config = new Config(BASE_PATH . '/config');
$container->instance(Config::class, $config);
$container->instance(Container::class, $container);

// 3. Timezone + error display
date_default_timezone_set((string) $config->get('app.timezone', 'UTC'));
$isDebug = (bool) $config->get('app.debug', false);
error_reporting(E_ALL);
ini_set('display_errors', $isDebug ? '1' : '0');
ini_set('log_errors',     '1');
ini_set('error_log',      BASE_PATH . '/storage/logs/php_error.log');

// 4. Register bindings via providers. Order matters: framework -> middleware ->
//    repositories -> business services -> SEO -> HTTP (router/app).
$providers = [
    __DIR__ . '/providers/framework.php',
    __DIR__ . '/providers/middleware.php',
    __DIR__ . '/providers/repositories.php',
    __DIR__ . '/providers/services.php',
    __DIR__ . '/providers/seo.php',
    __DIR__ . '/providers/http.php',
];

foreach ($providers as $providerFile) {
    (require $providerFile)($container, $config);
}

return $container;
