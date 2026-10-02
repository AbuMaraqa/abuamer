<?php

namespace App\Support;

use Illuminate\Support\Str;

final class Slug
{
    /**
     * A URL slug in the script of its language.
     *
     * Str::slug() transliterates to ASCII by default, which would strip every Arabic
     * letter, so Arabic slugs keep their script ("بورسلان-فاخر").
     */
    public static function make(string $value, string $locale): string
    {
        // Tile sizes are written "60×120"; keep them readable instead of collapsing to "60120".
        $value = str_replace('×', 'x', $value);

        return Str::slug($value, '-', $locale === 'ar' ? null : $locale);
    }

    /**
     * Letters and digits in any script, separated by single hyphens.
     */
    public const string PATTERN = '/^[\p{L}\p{N}]+(?:-[\p{L}\p{N}]+)*$/u';
}
