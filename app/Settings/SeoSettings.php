<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

/**
 * Site-wide defaults used when a page has no SEO texts of its own. Stored per locale.
 */
class SeoSettings extends Settings
{
    /** @var array<string, string> */
    public array $meta_title;

    /** @var array<string, string> */
    public array $meta_description;

    /** @var array<string, string> */
    public array $meta_keywords;

    public static function group(): string
    {
        return 'seo';
    }
}
