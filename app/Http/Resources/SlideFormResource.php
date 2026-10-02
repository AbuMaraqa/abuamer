<?php

namespace App\Http\Resources;

use App\Models\Slide;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Every editable value of a slide, in all languages, for the control panel form.
 *
 * @mixin Slide
 */
class SlideFormResource extends JsonResource
{
    private const array TRANSLATED_FIELDS = ['eyebrow', 'title', 'text', 'button_label', 'button_url'];

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $translations = $this->translations->keyBy('locale');
        $image = $this->getFirstMedia(Slide::IMAGE_COLLECTION);
        $mobileImage = $this->getFirstMedia(Slide::MOBILE_IMAGE_COLLECTION);

        return [
            'id' => $this->id,
            'status' => $this->status,
            'link_type' => $this->link_type->value,
            'category_id' => $this->category_id,
            ...collect(config('translatable.locales'))->mapWithKeys(fn (string $locale): array => [
                $locale => collect(self::TRANSLATED_FIELDS)
                    ->mapWithKeys(fn (string $field): array => [$field => $translations[$locale]->{$field} ?? ''])
                    ->all(),
            ]),
            'image' => $image ? MediaResource::make($image)->resolve($request) : null,
            'mobile_image' => $mobileImage ? MediaResource::make($mobileImage)->resolve($request) : null,
        ];
    }
}
