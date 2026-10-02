<?php

namespace App\Http\Requests\Concerns;

use App\Support\Locales;

/**
 * Content is written in every required language; an optional language (Hebrew) may be
 * left empty as a whole, but once one of its fields is filled its main fields are needed.
 */
trait ValidatesTranslations
{
    /**
     * @return list<string>
     */
    protected function locales(): array
    {
        return Locales::all();
    }

    /**
     * Rules for the array that holds one language's fields.
     *
     * @return list<string>
     */
    protected function languageRules(string $locale): array
    {
        return [Locales::isRequired($locale) ? 'required' : 'nullable', 'array'];
    }

    /**
     * Presence rules for a main field: required in a required language; in an optional
     * language, required as soon as any of the given sibling fields is filled.
     *
     * @param  string  $prefix  The language's input path, e.g. "he" or "specifications.*.he".
     * @param  list<string>  $siblings  The language's other fields.
     * @return list<string>
     */
    protected function requiredInLanguage(string $locale, string $prefix, array $siblings): array
    {
        if (Locales::isRequired($locale)) {
            return ['required'];
        }

        return ['nullable', 'required_with:'.collect($siblings)->map(fn (string $field): string => "{$prefix}.{$field}")->implode(',')];
    }

    /**
     * Only the main fields of optional languages use "required_with"; say what to do instead
     * of listing every other field of that language.
     *
     * @return array<string, string>
     */
    protected function translationMessages(): array
    {
        return ['required_with' => __('Fill in :attribute too, or leave all fields of that language empty.')];
    }

    /**
     * Drop the optional languages whose fields were all left empty.
     *
     * @template TValues of array<string, mixed>
     *
     * @param  array<string, TValues>  $translations  Keyed by locale.
     * @return array<string, TValues>
     */
    protected function withoutEmptyLanguages(array $translations): array
    {
        return array_filter(
            $translations,
            fn (array $values, string $locale): bool => Locales::isRequired($locale) || collect($values)->contains(fn (mixed $value): bool => filled($value)),
            ARRAY_FILTER_USE_BOTH,
        );
    }
}
