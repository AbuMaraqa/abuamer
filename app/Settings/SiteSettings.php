<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class SiteSettings extends Settings
{
    /**
     * The language visitors land on when the URL has no language.
     */
    public string $default_locale;

    /**
     * When enabled, visitors see a maintenance page; signed-in users keep full access.
     */
    public bool $maintenance_mode;

    public static function group(): string
    {
        return 'site';
    }
}
