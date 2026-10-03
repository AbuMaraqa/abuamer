<?php

namespace App\Http\Resources;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Every editable value of a brand, in all languages, for the control panel form.
 *
 * @mixin Brand
 */
class BrandFormResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $translations = $this->translations->keyBy('locale');
        $logo = $this->getFirstMedia(Brand::LOGO_COLLECTION);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'country' => $this->country,
            'website' => $this->website,
            'status' => $this->status,
            'url' => route('brands.show', ['brand' => $this->slug]),
            ...collect(config('translatable.locales'))->mapWithKeys(fn (string $locale): array => [
                $locale => ['description' => $translations[$locale]->description ?? ''],
            ]),
            'logo' => $logo ? MediaResource::make($logo)->resolve($request) : null,
            'products_count' => $this->whenCounted('products'),
        ];
    }
}
