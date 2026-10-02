<?php

namespace App\Http\Resources;

use App\Enums\SlideLinkType;
use App\Models\Slide;
use App\Services\Catalog\CategoryTreeService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A slide in the current language, with its button link resolved for this language.
 *
 * @mixin Slide
 */
class SlideResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $image = $this->getFirstMedia(Slide::IMAGE_COLLECTION);
        $mobileImage = $this->getFirstMedia(Slide::MOBILE_IMAGE_COLLECTION);
        $url = $this->linkUrl();

        return [
            'id' => $this->id,
            'eyebrow' => $this->eyebrow,
            'title' => $this->title,
            'text' => $this->text,
            'button' => $url !== null && filled($this->button_label) ? ['label' => $this->button_label, 'url' => $url] : null,
            'image' => $image ? MediaResource::make($image)->resolve($request) : null,
            'mobileImage' => $mobileImage ? MediaResource::make($mobileImage)->resolve($request) : null,
            'status' => $this->status,
        ];
    }

    /**
     * The button's destination, or null when there is none or its category is not visible.
     */
    private function linkUrl(): ?string
    {
        if ($this->link_type === SlideLinkType::Custom) {
            return filled($this->button_url) ? $this->button_url : null;
        }

        if ($this->link_type !== SlideLinkType::Category || $this->category_id === null) {
            return null;
        }

        $tree = app(CategoryTreeService::class)->tree();

        return $tree->has($this->category_id) && $tree->isVisible($this->category_id)
            ? route('products.category', ['path' => $tree->slugPath($this->category_id, app()->getLocale())])
            : null;
    }
}
