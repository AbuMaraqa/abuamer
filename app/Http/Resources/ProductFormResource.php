<?php

namespace App\Http\Resources;

use App\Models\Product;
use App\Models\ProductSpecification;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Every editable value of a product, in all languages, for the control panel form.
 *
 * @mixin Product
 */
class ProductFormResource extends JsonResource
{
    private const array TRANSLATED_FIELDS = ['name', 'slug', 'short_description', 'description', 'seo_title', 'seo_description'];

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $locales = config('translatable.locales');
        $translations = $this->translations->keyBy('locale');
        $mainImage = $this->getFirstMedia(Product::MAIN_IMAGE_COLLECTION);

        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'brand_id' => $this->brand_id,
            'sku' => $this->sku,
            'status' => $this->status,
            'featured' => $this->featured,
            'sort_order' => $this->sort_order,
            'url' => route('products.show', ['slug' => $this->slug]),
            ...collect($locales)->mapWithKeys(fn (string $locale): array => [
                $locale => collect(self::TRANSLATED_FIELDS)
                    ->mapWithKeys(fn (string $field): array => [$field => $translations[$locale]->{$field} ?? ''])
                    ->all(),
            ]),
            'main_image' => $mainImage ? MediaResource::make($mainImage)->resolve($request) : null,
            'gallery' => MediaResource::collection($this->getMedia(Product::GALLERY_COLLECTION))->resolve($request),
            'documents' => DocumentResource::collection($this->getMedia(Product::DOCUMENTS_COLLECTION))->resolve($request),
            'specifications' => $this->specifications->map(fn (ProductSpecification $specification): array => [
                'id' => $specification->id,
                ...collect($locales)->mapWithKeys(fn (string $locale): array => [
                    $locale => [
                        'label' => $specification->translate($locale)?->label ?? '',
                        'value' => $specification->translate($locale)?->value ?? '',
                    ],
                ]),
            ])->all(),
        ];
    }
}
