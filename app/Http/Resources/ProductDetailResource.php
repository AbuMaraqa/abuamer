<?php

namespace App\Http\Resources;

use App\Models\Brand;
use App\Models\Product;
use Illuminate\Http\Request;

/**
 * A product with its full content, gallery, specifications, brand and downloadable
 * documents, for the product page.
 *
 * @mixin Product
 */
class ProductDetailResource extends ProductResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            ...parent::toArray($request),
            'description' => $this->description,
            'seo_title' => $this->seo_title,
            'seo_description' => $this->seo_description,
            'gallery' => $this->whenLoaded('media', fn (): array => MediaResource::collection(
                $this->getMedia(Product::MAIN_IMAGE_COLLECTION)->concat($this->getMedia(Product::GALLERY_COLLECTION)),
            )->resolve($request)),
            'specifications' => ProductSpecificationResource::collection($this->whenLoaded('specifications')),
            'brand' => $this->whenLoaded('brand', fn (Brand $brand): array => BrandResource::make($brand)->resolve($request)),
            'documents' => $this->whenLoaded('media', fn (): array => DocumentResource::collection(
                $this->getMedia(Product::DOCUMENTS_COLLECTION),
            )->resolve($request)),
        ];
    }
}
