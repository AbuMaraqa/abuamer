<?php

namespace App\Http\Requests\Admin;

use App\Enums\Permission;
use App\Http\Requests\Concerns\ValidatesTranslations;
use App\Support\Slug;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreBrandRequest extends FormRequest
{
    use ValidatesTranslations;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can(Permission::BrandsManage->value);
    }

    /**
     * Derive a missing slug from the name and normalise the typed values.
     */
    protected function prepareForValidation(): void
    {
        $source = filled($this->input('slug')) ? (string) $this->input('slug') : (string) $this->input('name', '');

        // Brand names are usually Latin ("Villeroy & Boch" → "villeroy-boch"); a name written
        // only in another script keeps its letters, as the other slugs of the website do.
        $slug = Str::slug($source) ?: Slug::make($source, 'ar');

        $this->merge([
            'name' => trim((string) $this->input('name', '')),
            'slug' => $slug !== '' ? $slug : null,
            'country' => filled($this->input('country')) ? mb_strtoupper(trim((string) $this->input('country'))) : null,
            'website' => filled($this->input('website')) ? trim((string) $this->input('website')) : null,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:100', Rule::unique('brands', 'name')->ignore($this->ignoredBrandId())],
            'slug' => ['required', 'string', 'max:120', 'regex:'.Slug::PATTERN, Rule::unique('brands', 'slug')->ignore($this->ignoredBrandId())],
            'country' => ['nullable', 'string', 'regex:/^[A-Z]{2}$/'],
            'website' => ['nullable', 'string', 'max:255', 'url:http,https'],
            'status' => ['required', 'boolean'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_logo' => ['boolean'],
        ];

        foreach ($this->locales() as $locale) {
            $rules[$locale] = ['nullable', 'array'];
            $rules["{$locale}.description"] = ['nullable', 'string', 'max:3000'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        $attributes = [
            'name' => __('Brand name'),
            'slug' => __('Slug'),
            'country' => __('Country of origin'),
            'website' => __('Website'),
            'logo' => __('Logo'),
        ];

        foreach ($this->locales() as $locale) {
            $attributes["{$locale}.description"] = __('Description').' ('.__(config("laravellocalization.supportedLocales.{$locale}.name")).')';
        }

        return $attributes;
    }

    /**
     * Columns and translations keyed by locale; optional languages left empty are omitted.
     *
     * @return array<string, mixed>
     */
    public function brandAttributes(): array
    {
        return [
            'name' => $this->validated('name'),
            'slug' => $this->validated('slug'),
            'country' => $this->validated('country'),
            'website' => $this->validated('website'),
            'status' => $this->boolean('status'),
            ...$this->withoutEmptyLanguages(collect($this->locales())
                ->mapWithKeys(fn (string $locale): array => [$locale => ['description' => $this->validated("{$locale}.description")]])
                ->all()),
        ];
    }

    protected function ignoredBrandId(): ?int
    {
        return null;
    }
}
