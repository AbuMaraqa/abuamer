<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use App\Services\Catalog\CategoryTreeService;
use App\Support\Slug;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreCategoryRequest extends FormRequest
{
    /**
     * Translated fields accepted for every locale.
     */
    private const array TRANSLATED_FIELDS = ['name', 'slug', 'description', 'seo_title', 'seo_description'];

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Category::class);
    }

    /**
     * Derive a missing slug from the name and normalise typed slugs ("Marble Effect" → "marble-effect").
     */
    protected function prepareForValidation(): void
    {
        $translations = [];

        foreach ($this->locales() as $locale) {
            $translation = (array) $this->input($locale, []);
            $source = filled($translation['slug'] ?? null) ? $translation['slug'] : ($translation['name'] ?? null);

            if (is_string($source) && filled($source)) {
                $translation['slug'] = Slug::make($source, $locale);
            }

            $translations[$locale] = $translation;
        }

        $this->merge($translations);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'parent_id' => ['nullable', 'integer', Rule::exists('categories', 'id')],
            'status' => ['required', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:min_width=300,min_height=200'],
        ];

        foreach ($this->locales() as $locale) {
            $rules[$locale] = ['required', 'array'];
            $rules["{$locale}.name"] = ['required', 'string', 'max:255'];
            $rules["{$locale}.slug"] = ['required', 'string', 'max:255', 'regex:'.Slug::PATTERN];
            $rules["{$locale}.description"] = ['nullable', 'string', 'max:5000'];
            $rules["{$locale}.seo_title"] = ['nullable', 'string', 'max:255'];
            $rules["{$locale}.seo_description"] = ['nullable', 'string', 'max:500'];
        }

        return $rules;
    }

    /**
     * Slugs must be unique among the category's future siblings, in each language.
     *
     * @return array<int, callable>
     */
    public function after(CategoryTreeService $tree): array
    {
        return [
            function (Validator $validator) use ($tree) {
                if ($validator->errors()->has('parent_id')) {
                    return;
                }

                foreach ($this->locales() as $locale) {
                    $slug = $this->input("{$locale}.slug");

                    if ($validator->errors()->has("{$locale}.slug") || ! is_string($slug)) {
                        continue;
                    }

                    if ($tree->siblingSlugExists($this->parentId(), $locale, $slug, $this->ignoredCategoryId())) {
                        $validator->errors()->add("{$locale}.slug", __('Another category under the same parent already uses this slug.'));
                    }
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        $attributes = [];

        foreach ($this->locales() as $locale) {
            $language = __(config("laravellocalization.supportedLocales.{$locale}.name"));

            $attributes["{$locale}.name"] = __('Name').' ('.$language.')';
            $attributes["{$locale}.slug"] = __('Slug').' ('.$language.')';
            $attributes["{$locale}.description"] = __('Description').' ('.$language.')';
            $attributes["{$locale}.seo_title"] = __('SEO title').' ('.$language.')';
            $attributes["{$locale}.seo_description"] = __('SEO description').' ('.$language.')';
        }

        return $attributes;
    }

    public function parentId(): ?int
    {
        return $this->filled('parent_id') ? $this->integer('parent_id') : null;
    }

    /**
     * Translated attributes keyed by locale, ready for the Translatable model.
     *
     * @return array<string, array<string, string|null>>
     */
    public function translations(): array
    {
        return collect($this->locales())
            ->mapWithKeys(fn (string $locale): array => [
                $locale => collect(self::TRANSLATED_FIELDS)
                    ->mapWithKeys(fn (string $field): array => [$field => $this->validated("{$locale}.{$field}")])
                    ->all(),
            ])
            ->all();
    }

    /**
     * The category excluded from the sibling slug check (the one being edited).
     */
    protected function ignoredCategoryId(): ?int
    {
        return null;
    }

    /**
     * @return list<string>
     */
    protected function locales(): array
    {
        return config('translatable.locales');
    }
}
