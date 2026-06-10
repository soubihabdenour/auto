<?php

return [
    'name'             => env('APP_NAME', 'ADY Motors'),
    'env'              => env('APP_ENV', 'production'),
    'debug'            => filter_var(env('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN),
    'url'              => rtrim((string) env('APP_URL', 'http://localhost'), '/'),
    'timezone'         => env('APP_TIMEZONE', 'Africa/Algiers'),
    'key'              => env('APP_KEY', ''),

    'paths' => [
        'base'      => BASE_PATH,
        'app'       => BASE_PATH . '/app',
        'config'    => BASE_PATH . '/config',
        'resources' => BASE_PATH . '/resources',
        'views'     => BASE_PATH . '/resources/views',
        'lang'      => BASE_PATH . '/resources/lang',
        'storage'   => BASE_PATH . '/storage',
        'public'    => BASE_PATH . '/public',
        'uploads'   => BASE_PATH . '/public/uploads',
    ],
];
