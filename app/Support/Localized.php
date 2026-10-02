<?php

namespace App\Support;

final class Localized
{
    /**
     * Pick the current locale's text from a per-locale array (as stored in settings),
     * falling back to the locale's fallback language and then to any non-empty value.
     *
     * @param  array<string, string|null>  $values
     */
    public static function value(array $values, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        foreach ([$locale, Locales::fallbackFor($locale)] as $candidate) {
            if (filled($values[$candidate] ?? null)) {
                return (string) $values[$candidate];
            }
        }

        return (string) collect($values)->first(fn (?string $value): bool => filled($value), '');
    }
}
