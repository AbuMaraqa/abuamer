<?php

namespace App\Settings;

use App\Enums\SiteFont;
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

    /**
     * The body font of the website (a SiteFont value).
     */
    public string $font;

    public static function group(): string
    {
        return 'site';
    }

    public function font(): SiteFont
    {
        return SiteFont::tryFrom($this->font) ?? SiteFont::IbmPlexSansArabic;
    }
}
