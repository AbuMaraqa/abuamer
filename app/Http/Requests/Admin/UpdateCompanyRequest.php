<?php

namespace App\Http\Requests\Admin;

use App\Enums\HighlightType;
use App\Enums\Permission;
use App\Models\CompanyHighlight;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

/**
 * The company form submits the complete desired state: texts in every language,
 * the ordered lists of values, features and statistics, and the images.
 */
class UpdateCompanyRequest extends FormRequest
{
    /**
     * Translated text fields with their maximum lengths.
     */
    private const array TEXT_FIELDS = [
        'name' => 255,
        'tagline' => 255,
        'hero_title' => 255,
        'hero_subtitle' => 1000,
        'introduction' => 3000,
        'story' => 10000,
        'vision' => 3000,
        'mission' => 3000,
        'cta_title' => 255,
        'cta_text' => 1000,
    ];

    /**
     * Highlight lists submitted by the form, keyed by input name.
     */
    public const array HIGHLIGHT_LISTS = [
        'values' => HighlightType::Value,
        'features' => HighlightType::Feature,
        'statistics' => HighlightType::Statistic,
    ];

    private const array PHOTO_RULES = ['image', 'mimes:jpg,jpeg,png,webp', 'max:10240'];

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can(Permission::CompanyManage->value);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'founded_year' => ['nullable', 'integer', 'min:1900', 'max:'.now()->year],

            // SVG is not accepted: it can carry scripts and would be served from the site's origin.
            'logo' => ['nullable', 'image', 'mimes:png,webp', 'max:2048'],
            'favicon' => ['nullable', 'image', 'mimes:png', 'max:1024', 'dimensions:min_width=48,ratio=1'],
            'hero_image' => ['nullable', ...self::PHOTO_RULES, 'dimensions:min_width=1600,min_height=900'],
            'about_image' => ['nullable', ...self::PHOTO_RULES, 'dimensions:min_width=800,min_height=600'],
            'remove_logo' => ['boolean'],
            'remove_favicon' => ['boolean'],
            'remove_hero_image' => ['boolean'],
            'remove_about_image' => ['boolean'],
            'gallery' => ['array'],
            'gallery.*' => ['integer', 'distinct'],
            'gallery_uploads' => ['array', 'max:30'],
            'gallery_uploads.*' => [...self::PHOTO_RULES, 'dimensions:min_width=800,min_height=600'],

            'values' => ['array', 'max:12'],
            'features' => ['array', 'max:12'],
            'features.*.icon' => ['required', Rule::in(CompanyHighlight::FEATURE_ICONS)],
            'statistics' => ['array', 'max:8'],
            'statistics.*.value' => ['required', 'string', 'max:40'],
        ];

        foreach (array_keys(self::HIGHLIGHT_LISTS) as $list) {
            $rules["{$list}.*.id"] = ['nullable', 'integer'];
        }

        foreach ($this->locales() as $locale) {
            $rules[$locale] = ['required', 'array'];

            foreach (self::TEXT_FIELDS as $field => $maxLength) {
                $rules["{$locale}.{$field}"] = [$field === 'name' ? 'required' : 'nullable', 'string', "max:{$maxLength}"];
            }

            foreach (array_keys(self::HIGHLIGHT_LISTS) as $list) {
                $rules["{$list}.*.{$locale}.title"] = ['required', 'string', 'max:120'];
                $rules["{$list}.*.{$locale}.description"] = ['nullable', 'string', 'max:500'];
            }
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        $attributes = [];

        foreach ($this->locales() as $locale) {
            $language = __(config("laravellocalization.supportedLocales.{$locale}.name"));
            $attributes["{$locale}.name"] = __('Company name').' ('.$language.')';

            foreach (array_keys(self::HIGHLIGHT_LISTS) as $list) {
                $attributes["{$list}.*.{$locale}.title"] = __('Title').' ('.$language.')';
            }
        }

        return $attributes;
    }

    /**
     * @return array<string, array<string, string|null>>
     */
    public function translations(): array
    {
        return collect($this->locales())
            ->mapWithKeys(fn (string $locale): array => [
                $locale => collect(array_keys(self::TEXT_FIELDS))
                    ->mapWithKeys(fn (string $field): array => [$field => $this->validated("{$locale}.{$field}")])
                    ->all(),
            ])
            ->all();
    }

    /**
     * Each highlight list in display order, keyed by type.
     *
     * @return array<string, list<array{id: int|null, icon: string|null, value: string|null, translations: array<string, array{title: string, description: string|null}>}>>
     */
    public function highlights(): array
    {
        $lists = [];

        foreach (self::HIGHLIGHT_LISTS as $input => $type) {
            $lists[$type->value] = collect($this->validated($input, []))
                ->map(fn (array $item): array => [
                    'id' => isset($item['id']) ? (int) $item['id'] : null,
                    'icon' => $type === HighlightType::Feature ? $item['icon'] : null,
                    'value' => $type === HighlightType::Statistic ? $item['value'] : null,
                    'translations' => collect($this->locales())
                        ->mapWithKeys(fn (string $locale): array => [$locale => [
                            'title' => $item[$locale]['title'],
                            'description' => $item[$locale]['description'] ?? null,
                        ]])
                        ->all(),
                ])
                ->values()
                ->all();
        }

        return $lists;
    }

    /**
     * @return list<int>
     */
    public function galleryIds(): array
    {
        return array_map('intval', $this->validated('gallery', []));
    }

    /**
     * @return list<UploadedFile>
     */
    public function galleryUploads(): array
    {
        return array_values($this->file('gallery_uploads', []));
    }

    /**
     * @return list<string>
     */
    private function locales(): array
    {
        return config('translatable.locales');
    }
}
