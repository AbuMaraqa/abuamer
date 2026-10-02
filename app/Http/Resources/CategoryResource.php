<?php

namespace App\Http\Resources;

use App\Models\Category;
use App\Services\Catalog\CategoryTreeService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A category node. Children are included recursively when the `children` relation is
 * filled (see CategoryTree::nested()), so the same resource renders a tree of any depth.
 *
 * @mixin Category
 */
class CategoryResource extends JsonResource
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
            'parent_id' => $this->parent_id,
            'name' => $this->name,
            'names' => $this->translations->pluck('name', 'locale'),
            'slug' => $this->slug,
            'url' => route('products.category', [
                'path' => app(CategoryTreeService::class)->tree()->slugPath($this->id, app()->getLocale()),
            ]),
            'status' => $this->status,
            'sort_order' => $this->sort_order,
            'image' => $this->whenLoaded('media', fn (): ?array => $this->imageData($request)),
            'products_count' => $this->whenHas('products_count'),
            'children' => $this->whenLoaded('children', fn (): array => static::collection($this->children)->resolve($request)),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function imageData(Request $request): ?array
    {
        $media = $this->getFirstMedia(Category::IMAGE_COLLECTION);

        return $media ? MediaResource::make($media)->resolve($request) : null;
    }
}
