<?php

declare(strict_types=1);

use App\Core\Application;

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/bootstrap/autoload.php';
/** @var \App\Core\Container $container */
$container = require BASE_PATH . '/bootstrap/app.php';

$container->get(Application::class)->run();
