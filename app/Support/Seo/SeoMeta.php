<?php

namespace App\Support\Seo;

use App\Models\Company;
use App\Settings\SeoSettings;
use App\Support\Localized;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

/**
 * Search engine and social sharing metadata for one page.
 *
 * Controllers set what is specific to the page; everything else falls back to the
 * SEO settings and the company profile. The array form is shared with Inertia as the
 * "seo" prop, rendered in the root Blade view for crawlers, and kept up to date on
 * client-side navigation by the SeoHead component.
 *
 * @implements Arrayable<string, mixed>
 */
final class SeoMeta implements Arrayable
{
    private ?string $title = null;

    private ?string $description = null;

    private ?string $image = null;

    private string $type = 'website';

    private bool $indexable = true;

    /**
     * @var array<string, string>|null
     */
    private ?array $alternates = null;

    /**
     * @var list<array<string, mixed>>
     */
    private array $structuredData = [];

    public static function make(): self
    {
        return new self;
    }

    /**
     * The page title without the site name, which is appended automatically.
     */
    public function title(?string $title): self
    {
        $this->title = filled($title) ? $title : null;

        return $this;
    }

    public function description(?string $description): self
    {
        $this->description = filled($description) ? Str::limit(Str::squish($description), 160) : null;

        return $this;
    }

    public function image(?string $url): self
    {
        $this->image = filled($url) ? $url : null;

        return $this;
    }

    public function type(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Keep the page out of search results (internal search results, private pages).
     */
    public function noindex(bool $noindex = true): self
    {
        $this->indexable = ! $noindex;

        return $this;
    }

    /**
     * The page's URL in every language, when it differs by more than the prefix (translated slugs).
     *
     * @param  array<string, string>  $urls
     */
    public function alternates(array $urls): self
    {
        $this->alternates = $urls;

        return $this;
    }

    /**
     * @param  array<string, mixed>  $schema  A schema.org object (JSON-LD).
     */
    public function withStructuredData(array $schema): self
    {
        $this->structuredData[] = $schema;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $company = Company::current();
        $settings = app(SeoSettings::class);
        $siteName = $company->name ?: config('app.name');
        $alternates = $this->alternateUrls();

        return [
            'title' => $this->title !== null
                ? "{$this->title} | {$siteName}"
                : (Localized::value($settings->meta_title) ?: $siteName),
            'description' => $this->description
                ?? (Str::limit(Str::squish(Localized::value($settings->meta_description) ?: (string) $company->introduction), 160) ?: null),
            'keywords' => Localized::value($settings->meta_keywords) ?: null,
            'canonical' => $this->canonicalUrl(),
            'robots' => $this->indexable ? 'index, follow' : 'noindex, follow',
            'alternates' => $alternates,
            // The current request's locale replaces app.locale, so ask mcamara for the default.
            'defaultUrl' => $alternates[LaravelLocalization::getDefaultLocale()] ?? reset($alternates),
            'image' => $this->image
                ?? $company->getFirstMedia(Company::HERO_COLLECTION)?->getAvailableFullUrl(['large'])
                ?? ($company->getFirstMediaUrl(Company::LOGO_COLLECTION) ?: null),
            'type' => $this->type,
            'siteName' => $siteName,
            'locale' => LaravelLocalization::getCurrentLocaleRegional() ?: app()->getLocale(),
            'alternateLocales' => collect(LaravelLocalization::getSupportedLocales())
                ->except(app()->getLocale())
                ->map(fn (array $properties, string $code): string => $properties['regional'] ?: $code)
                ->values()
                ->all(),
            'structuredData' => $this->structuredData,
        ];
    }

    /**
     * The current URL without query parameters, except the page number of a listing.
     */
    private function canonicalUrl(): string
    {
        $page = (int) request()->query('page', 1);

        return url()->current().($page > 1 ? '?page='.$page : '');
    }

    /**
     * @return array<string, string>
     */
    private function alternateUrls(): array
    {
        $urls = $this->alternates ?? collect(LaravelLocalization::getSupportedLanguagesKeys())
            ->mapWithKeys(fn (string $locale): array => [$locale => LaravelLocalization::getLocalizedURL($locale, null, [], true)])
            ->all();

        // Query strings (filters, search terms) never belong in hreflang URLs.
        return array_map(fn (string $url): string => Str::before($url, '?'), $urls);
    }
}
