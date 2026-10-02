<?php

namespace App\Http\Resources;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Every editable value of a category, in all languages, for the control panel form.
 *
 * @mixin Category
 */
class CategoryFormResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $translations = $this->translations->keyBy('locale');

        return [
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'status' => $this->status,
            ...collect(config('translatable.locales'))->mapWithKeys(fn (string $locale): array => [
                $locale => [
                    'name' => $translations[$locale]->name ?? '',
                    'slug' => $translations[$locale]->slug ?? '',
                    'description' => $translations[$locale]->description ?? '',
                    'seo_title' => $translations[$locale]->seo_title ?? '',
                    'seo_description' => $translations[$locale]->seo_description ?? '',
                ],
            ]),
            'image' => ($media = $this->getFirstMedia(Category::IMAGE_COLLECTION))
                ? MediaResource::make($media)->resolve($request)
                : null,
        ];
    }
}
