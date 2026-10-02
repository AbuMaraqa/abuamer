<?php

namespace App\Http\Resources;

use App\Models\Product;
use App\Services\Catalog\CategoryTreeService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A product as shown in listings and cards.
 *
 * @mixin Product
 */
class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $category = app(CategoryTreeService::class)->tree()->find($this->category_id);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'url' => route('products.show', ['slug' => $this->slug]),
            'sku' => $this->sku,
            'short_description' => $this->short_description,
            'status' => $this->status,
            'featured' => $this->featured,
            'category' => $category ? ['id' => $category->id, 'name' => $category->name] : null,
            'image' => $this->whenLoaded('media', fn (): ?array => $this->coverImage($request)),
        ];
    }

    /**
     * The main image, or the first gallery image when no main image was uploaded.
     *
     * @return array<string, mixed>|null
     */
    private function coverImage(Request $request): ?array
    {
        $media = $this->getFirstMedia(Product::MAIN_IMAGE_COLLECTION) ?? $this->getFirstMedia(Product::GALLERY_COLLECTION);

        return $media ? MediaResource::make($media)->resolve($request) : null;
    }
}
