<?php

declare(strict_types=1);

/**
 * Manual PSR-4 autoloader for App\* namespace.
 * Production runs without Composer (cheap shared hosting friendly).
 */

if (! defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (! str_starts_with($class, $prefix)) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file = BASE_PATH . '/app/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

// Helpers (depend on app() / container — but defined eagerly)
require BASE_PATH . '/app/Helpers/helpers.php';

// If Composer dev tools are installed, load them too (for PHPUnit, Whoops)
$composerAutoload = BASE_PATH . '/vendor/autoload.php';
if (is_file($composerAutoload)) {
    require $composerAutoload;
}
