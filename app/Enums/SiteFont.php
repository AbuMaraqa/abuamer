<?php

namespace App\Enums;

/**
 * Body fonts the website can use. Each value is the font's alias in vite.config.js,
 * which also names its CSS variable (--font-{value}).
 */
enum SiteFont: string
{
    case IbmPlexSansArabic = 'ibm-plex-sans-arabic';
    case Tajawal = 'tajawal';

    public function label(): string
    {
        return match ($this) {
            self::IbmPlexSansArabic => 'IBM Plex Sans Arabic',
            self::Tajawal => 'Tajawal',
        };
    }

    /**
     * Font aliases the public pages load: the chosen body font, the display fonts and
     * the Hebrew fonts that supply the glyphs the others lack.
     *
     * @return list<string>
     */
    public function publicFontAliases(): array
    {
        return [$this->value, 'display-latin', 'display-arabic', 'hebrew-body', 'hebrew-display'];
    }
}
