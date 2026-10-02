<?php

namespace App\Http\Resources;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The company profile in the current language, for the public pages.
 *
 * @mixin Company
 */
class CompanyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name ?: config('app.name'),
            'tagline' => $this->tagline,
            'hero_title' => $this->hero_title,
            'hero_subtitle' => $this->hero_subtitle,
            'introduction' => $this->introduction,
            'story' => $this->story,
            'vision' => $this->vision,
            'mission' => $this->mission,
            'cta_title' => $this->cta_title,
            'cta_text' => $this->cta_text,
            'founded_year' => $this->founded_year,
            'hero_image' => $this->mediaData(Company::HERO_COLLECTION, $request),
            'about_image' => $this->mediaData(Company::ABOUT_COLLECTION, $request),
            'gallery' => MediaResource::collection($this->getMedia(Company::GALLERY_COLLECTION))->resolve($request),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function mediaData(string $collection, Request $request): ?array
    {
        $media = $this->getFirstMedia($collection);

        return $media ? MediaResource::make($media)->resolve($request) : null;
    }
}
