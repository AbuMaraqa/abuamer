<?php

namespace App\Http\Resources;

use App\Enums\HighlightType;
use App\Models\Company;
use App\Models\CompanyHighlight;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Every editable value of the company profile, in all languages, for the control panel.
 *
 * @mixin Company
 */
class CompanyFormResource extends JsonResource
{
    private const array TEXT_FIELDS = ['name', 'tagline', 'hero_title', 'hero_subtitle', 'introduction', 'story', 'vision', 'mission', 'cta_title', 'cta_text'];

    /**
     * @param  Collection<int, CompanyHighlight>  $highlights
     */
    public function __construct(Company $company, private readonly Collection $highlights)
    {
        parent::__construct($company);
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $locales = config('translatable.locales');
        $translations = $this->translations->keyBy('locale');

        return [
            'founded_year' => $this->founded_year,
            ...collect($locales)->mapWithKeys(fn (string $locale): array => [
                $locale => collect(self::TEXT_FIELDS)
                    ->mapWithKeys(fn (string $field): array => [$field => $translations[$locale]->{$field} ?? ''])
                    ->all(),
            ]),
            'logo' => $this->mediaData(Company::LOGO_COLLECTION, $request),
            'favicon' => $this->mediaData(Company::FAVICON_COLLECTION, $request),
            'hero_image' => $this->mediaData(Company::HERO_COLLECTION, $request),
            'about_image' => $this->mediaData(Company::ABOUT_COLLECTION, $request),
            'gallery' => MediaResource::collection($this->getMedia(Company::GALLERY_COLLECTION))->resolve($request),
            'values' => $this->highlightsOfType(HighlightType::Value, $locales),
            'features' => $this->highlightsOfType(HighlightType::Feature, $locales),
            'statistics' => $this->highlightsOfType(HighlightType::Statistic, $locales),
        ];
    }

    /**
     * @param  list<string>  $locales
     * @return list<array<string, mixed>>
     */
    private function highlightsOfType(HighlightType $type, array $locales): array
    {
        return $this->highlights
            ->where('type', $type)
            ->map(fn (CompanyHighlight $highlight): array => [
                'id' => $highlight->id,
                'icon' => $highlight->icon,
                'value' => $highlight->value ?? '',
                ...collect($locales)->mapWithKeys(fn (string $locale): array => [
                    $locale => [
                        'title' => $highlight->translate($locale)?->title ?? '',
                        'description' => $highlight->translate($locale)?->description ?? '',
                    ],
                ]),
            ])
            ->values()
            ->all();
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
