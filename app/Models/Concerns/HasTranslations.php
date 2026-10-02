<?php

namespace App\Models\Concerns;

use App\Support\Locales;
use Astrotomic\Translatable\Translatable;

/**
 * Astrotomic translations with a fallback per language: Hebrew content that was left
 * empty is shown in English, English in Arabic and Arabic in English.
 */
trait HasTranslations
{
    use Translatable;

    /**
     * Fill the attributes, treating the translations given as the complete set: the stored
     * translations of the languages left out (optional languages cleared in a form) are
     * deleted, so those languages fall back again.
     *
     * @param  array<string, mixed>  $attributes  Columns and translations keyed by locale.
     */
    public function fillWithTranslations(array $attributes): static
    {
        $removedLocales = array_values(array_diff($this->getLocalesHelper()->all(), array_keys($attributes)));

        if ($this->exists && $removedLocales !== []) {
            $this->translations()->whereIn($this->getLocaleKey(), $removedLocales)->delete();

            if ($this->relationLoaded('translations')) {
                $this->setRelation('translations', $this->translations->whereNotIn($this->getLocaleKey(), $removedLocales)->values());
            }
        }

        return $this->fill($attributes);
    }

    /**
     * The package falls back to one global language; pick the one for the language read.
     */
    protected function getFallbackLocale(?string $locale = null): ?string
    {
        return Locales::fallbackFor($locale ?? $this->locale());
    }
}
