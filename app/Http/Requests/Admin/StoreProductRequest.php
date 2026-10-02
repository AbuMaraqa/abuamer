<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use App\Support\Slug;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

/**
 * The form always submits the complete desired state of a product: translations,
 * the ordered list of specifications, and the ordered list of gallery images to keep.
 */
class StoreProductRequest extends FormRequest
{
    private const array TRANSLATED_FIELDS = ['name', 'slug', 'short_description', 'description', 'seo_title', 'seo_description'];

    private const array IMAGE_RULES = ['image', 'mimes:jpg,jpeg,png,webp', 'max:8192', 'dimensions:min_width=600,min_height=600'];

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Product::class);
    }

    /**
     * Derive missing slugs from the names and normalise typed ones.
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

        $this->merge([
            ...$translations,
            'sku' => filled($this->input('sku')) ? mb_strtoupper(trim((string) $this->input('sku'))) : null,
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
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'sku' => ['nullable', 'string', 'max:64', 'regex:/^[A-Z0-9][A-Z0-9._\-\/]*$/', Rule::unique('products', 'sku')->ignore($this->ignoredProductId())],
            'status' => ['required', 'boolean'],
            'featured' => ['required', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:1000000'],

            'main_image' => ['nullable', ...self::IMAGE_RULES],
            'remove_main_image' => ['boolean'],
            'gallery' => ['array'],
            'gallery.*' => ['integer', 'distinct'],
            'gallery_uploads' => ['array', 'max:20'],
            'gallery_uploads.*' => self::IMAGE_RULES,

            'specifications' => ['array', 'max:50'],
            'specifications.*.id' => ['nullable', 'integer'],
        ];

        foreach ($this->locales() as $locale) {
            $rules[$locale] = ['required', 'array'];
            $rules["{$locale}.name"] = ['required', 'string', 'max:255'];
            $rules["{$locale}.slug"] = [
                'required',
                'string',
                'max:255',
                'regex:'.Slug::PATTERN,
                Rule::unique('product_translations', 'slug')->where('locale', $locale)->ignore($this->ignoredProductId(), 'product_id'),
            ];
            $rules["{$locale}.short_description"] = ['nullable', 'string', 'max:500'];
            $rules["{$locale}.description"] = ['nullable', 'string', 'max:20000'];
            $rules["{$locale}.seo_title"] = ['nullable', 'string', 'max:255'];
            $rules["{$locale}.seo_description"] = ['nullable', 'string', 'max:500'];
            $rules["specifications.*.{$locale}.label"] = ['required', 'string', 'max:100'];
            $rules["specifications.*.{$locale}.value"] = ['required', 'string', 'max:255'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        $attributes = [
            'sku' => __('Code (SKU)'),
            'gallery_uploads.*' => __('Gallery image'),
        ];

        foreach ($this->locales() as $locale) {
            $language = __(config("laravellocalization.supportedLocales.{$locale}.name"));

            foreach (['name' => 'Name', 'slug' => 'Slug', 'short_description' => 'Short description', 'description' => 'Description', 'seo_title' => 'SEO title', 'seo_description' => 'SEO description'] as $field => $label) {
                $attributes["{$locale}.{$field}"] = __($label).' ('.$language.')';
            }

            $attributes["specifications.*.{$locale}.label"] = __('Specification name').' ('.$language.')';
            $attributes["specifications.*.{$locale}.value"] = __('Specification value').' ('.$language.')';
        }

        return $attributes;
    }

    /**
     * @return array<string, mixed>
     */
    public function productAttributes(): array
    {
        return [
            'category_id' => $this->integer('category_id'),
            'sku' => $this->validated('sku'),
            'status' => $this->boolean('status'),
            'featured' => $this->boolean('featured'),
            'sort_order' => $this->integer('sort_order'),
            ...$this->translations(),
        ];
    }

    /**
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
     * Specifications in display order, each with its translations keyed by locale.
     *
     * @return list<array{id: int|null, translations: array<string, array{label: string, value: string}>}>
     */
    public function specifications(): array
    {
        return collect($this->validated('specifications', []))
            ->map(fn (array $specification): array => [
                'id' => isset($specification['id']) ? (int) $specification['id'] : null,
                'translations' => collect($this->locales())
                    ->mapWithKeys(fn (string $locale): array => [$locale => [
                        'label' => $specification[$locale]['label'],
                        'value' => $specification[$locale]['value'],
                    ]])
                    ->all(),
            ])
            ->values()
            ->all();
    }

    /**
     * Ids of existing gallery images to keep, in display order.
     *
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

    protected function ignoredProductId(): ?int
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
