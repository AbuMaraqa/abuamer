<?php

namespace App\Http\Resources;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A brand as shown on the website and in the control panel lists. The logo and the
 * description are included when the media and translations are loaded.
 *
 * @mixin Brand
 */
class BrandResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'url' => route('brands.show', ['brand' => $this->slug]),
            // An ISO 3166 code; the browser writes the country name in the page's language.
            'country' => $this->country,
            'website' => $this->website,
            'status' => $this->status,
            'sort_order' => $this->sort_order,
            'description' => $this->whenLoaded('translations', fn (): ?string => $this->description),
            'logo' => $this->whenLoaded('media', fn (): ?array => $this->logoData($request)),
            'products_count' => $this->whenCounted('products'),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function logoData(Request $request): ?array
    {
        $media = $this->getFirstMedia(Brand::LOGO_COLLECTION);

        return $media ? MediaResource::make($media)->resolve($request) : null;
    }
}
