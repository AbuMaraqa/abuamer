<?php

namespace App\Support;

use Closure;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

final class LocalizedUrl
{
    /**
     * The URL of a named route in the given locale.
     *
     * Localized routes are registered for the current request's locale only, so the
     * URL is generated for the current locale and then re-prefixed.
     *
     * @param  array<string, mixed>  $parameters
     */
    public static function route(string $locale, string $name, array $parameters = []): string
    {
        return LaravelLocalization::getLocalizedURL($locale, route($name, $parameters), [], true);
    }

    /**
     * The URL of a named route in every supported locale, for pages whose
     * parameters (translated slugs) differ per locale.
     *
     * @param  Closure(string): array<string, mixed>  $parameters  Receives the locale.
     * @return array<string, string>
     */
    public static function alternates(string $name, Closure $parameters): array
    {
        return collect(LaravelLocalization::getSupportedLanguagesKeys())
            ->mapWithKeys(fn (string $locale): array => [$locale => self::route($locale, $name, $parameters($locale))])
            ->all();
    }
}
