<?php

namespace App\Support;

final class Localized
{
    /**
     * Pick the current locale's text from a per-locale array (as stored in settings),
     * falling back to Arabic and then to any non-empty value.
     *
     * @param  array<string, string|null>  $values
     */
    public static function value(array $values, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return (string) (filled($values[$locale] ?? null)
            ? $values[$locale]
            : (filled($values['ar'] ?? null) ? $values['ar'] : collect($values)->first(fn (?string $value): bool => filled($value), '')));
    }
}
