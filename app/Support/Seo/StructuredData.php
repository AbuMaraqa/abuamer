<?php

namespace App\Support\Seo;

use App\Models\Company;
use App\Models\Product;
use App\Settings\ContactSettings;
use App\Settings\SocialSettings;

/**
 * schema.org objects (JSON-LD) that help search engines understand the pages.
 */
final class StructuredData
{
    /**
     * @return array<string, mixed>
     */
    public static function organization(Company $company): array
    {
        $contact = app(ContactSettings::class);

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $company->name ?: config('app.name'),
            'url' => route('home'),
            'logo' => $company->getFirstMediaUrl(Company::LOGO_COLLECTION) ?: null,
            'foundingDate' => $company->founded_year ? (string) $company->founded_year : null,
            'email' => $contact->email ?: null,
            'telephone' => $contact->phone ?: null,
            'sameAs' => array_values(app(SocialSettings::class)->links()) ?: null,
        ]);
    }

    /**
     * @param  list<array{label: string, url: string|null}>  $breadcrumbs
     * @return array<string, mixed>
     */
    public static function breadcrumbs(array $breadcrumbs): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($breadcrumbs)
                ->values()
                ->map(fn (array $item, int $index): array => array_filter([
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => $item['label'],
                    'item' => $item['url'] ?? url()->current(),
                ]))
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function product(Product $product, string $brand): array
    {
        $images = $product->getMedia(Product::MAIN_IMAGE_COLLECTION)
            ->concat($product->getMedia(Product::GALLERY_COLLECTION))
            ->map->getAvailableFullUrl(['large'])
            ->values()
            ->all();

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->name,
            'description' => $product->short_description ?: $product->description,
            'sku' => $product->sku,
            'image' => $images ?: null,
            'brand' => ['@type' => 'Brand', 'name' => $brand],
            'url' => url()->current(),
        ]);
    }
}
