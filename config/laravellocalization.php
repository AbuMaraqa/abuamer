<?php

return [

    // Arabic is the primary language of the site; English is secondary.
    // The full list of locales is available in the package's published config:
    // vendor/mcamara/laravel-localization/src/config/config.php
    'supportedLocales' => [
        'ar' => ['name' => 'Arabic', 'script' => 'Arab', 'native' => 'العربية', 'regional' => 'ar_AE'],
        'en' => ['name' => 'English', 'script' => 'Latn', 'native' => 'English', 'regional' => 'en_GB'],
    ],

    // Visitors always land on the default (Arabic) locale instead of being
    // redirected based on the browser's Accept-Language header.
    'useAcceptLanguageHeader' => false,

    // Every public URL carries its locale prefix (/ar/..., /en/...), so the
    // default locale has a single canonical URL as well.
    'hideDefaultLocaleInURL' => false,

    'localesOrder' => ['ar', 'en'],

    'localesMapping' => [],

    // Locale suffix for LC_TIME and LC_MONETARY.
    'utf8suffix' => env('LARAVELLOCALIZATION_UTF8SUFFIX', '.UTF-8'),

    // URLs which should not be processed by the localization middleware.
    'urlsIgnored' => [],

    'httpMethodsIgnored' => ['POST', 'PUT', 'PATCH', 'DELETE'],
];
