<?php

$available = array_map('trim', explode(',', (string) env('APP_LOCALES', 'ar,fr,en')));

return [
    'default'   => env('APP_LOCALE', 'ar'),
    'fallback'  => env('APP_FALLBACK_LOCALE', 'en'),
    'available' => $available,
    'rtl'       => ['ar'],
    'native'    => [
        'ar' => 'العربية',
        'fr' => 'Français',
        'en' => 'English',
    ],
    'flag' => [
        'ar' => '🇩🇿',
        'fr' => '🇫🇷',
        'en' => '🇬🇧',
    ],
];
