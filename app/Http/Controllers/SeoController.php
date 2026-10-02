<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\Catalog\CategoryTree;
use App\Services\Catalog\CategoryTreeService;
use App\Support\LocalizedUrl;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class SeoController extends Controller
{
    /**
     * Every public page in every language, each listing its translations (hreflang).
     * Cached for an hour; a new product or category appears within that time.
     */
    public function sitemap(CategoryTreeService $categoryTree): Response
    {
        $xml = Cache::remember('seo.sitemap', now()->addHour(), fn (): string => view('sitemap', [
            'entries' => $this->entries($categoryTree->tree()),
        ])->render());

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots(): Response
    {
        $lines = ['User-agent: *'];

        foreach (LaravelLocalization::getSupportedLanguagesKeys() as $locale) {
            $lines[] = "Disallow: /{$locale}/admin";
            $lines[] = "Disallow: /{$locale}/login";
        }

        $lines[] = '';
        $lines[] = 'Sitemap: '.route('sitemap');

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    /**
     * @return list<array{urls: array<string, string>, lastmod: Carbon|null}>
     */
    private function entries(CategoryTree $tree): array
    {
        $entries = [];

        foreach (['home', 'about', 'contact', 'products.index'] as $routeName) {
            $entries[] = ['urls' => LocalizedUrl::alternates($routeName, fn (): array => []), 'lastmod' => null];
        }

        $visibleIds = $tree->visibleIds();

        foreach ($visibleIds as $categoryId) {
            $entries[] = [
                'urls' => LocalizedUrl::alternates('products.category', fn (string $locale): array => ['path' => $tree->slugPath($categoryId, $locale)]),
                'lastmod' => null,
            ];
        }

        Product::query()
            ->active()
            ->inCategories($visibleIds)
            ->with('translations')
            ->lazyById(500)
            ->each(function (Product $product) use (&$entries) {
                $entries[] = [
                    'urls' => LocalizedUrl::alternates('products.show', fn (string $locale): array => ['slug' => $product->translate($locale, true)->slug]),
                    'lastmod' => $product->updated_at,
                ];
            });

        return $entries;
    }
}
