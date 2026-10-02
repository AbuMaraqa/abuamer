<?php

namespace App\Support;

/**
 * The content languages: required ones must always be filled in, optional ones (such as
 * Hebrew) may be left empty and then fall back to another language.
 */
final class Locales
{
    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return config('translatable.locales');
    }

    /**
     * @return list<string>
     */
    public static function required(): array
    {
        return config('translatable.required_locales');
    }

    public static function isRequired(string $locale): bool
    {
        return in_array($locale, self::required(), true);
    }

    /**
     * Whether the language is written in a non-Latin script whose letters slugs keep.
     */
    public static function keepsScriptInSlugs(string $locale): bool
    {
        return self::script($locale) !== 'Latn';
    }

    /**
     * @return 'rtl'|'ltr'
     */
    public static function direction(string $locale): string
    {
        return in_array(self::script($locale), ['Arab', 'Hebr'], true) ? 'rtl' : 'ltr';
    }

    /**
     * The ISO 15924 script code from the localization config, e.g. "Arab", "Hebr" or "Latn".
     */
    public static function script(string $locale): string
    {
        return config("laravellocalization.supportedLocales.{$locale}.script", 'Latn');
    }

    public static function fallbackFor(string $locale): string
    {
        return config("translatable.fallbacks.{$locale}", self::required()[0]);
    }
}
