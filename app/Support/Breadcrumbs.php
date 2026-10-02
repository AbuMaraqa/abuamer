<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Product;
use App\Services\Catalog\CategoryTree;

/**
 * Breadcrumb trails generated from the category ancestry, never hard-coded:
 * Home / Products / Porcelain / Marble Effect / Calacatta / Gold / Product.
 */
final class Breadcrumbs
{
    /**
     * @return list<array{label: string, url: string|null}>
     */
    public static function forCatalog(): array
    {
        return [
            ['label' => __('Home'), 'url' => route('home')],
            ['label' => __('Products'), 'url' => route('products.index')],
        ];
    }

    /**
     * @return list<array{label: string, url: string|null}>
     */
    public static function forCategory(CategoryTree $tree, Category $category): array
    {
        $locale = app()->getLocale();

        return [
            ...self::forCatalog(),
            ...$tree->path($category->id)
                ->map(fn (Category $node): array => [
                    'label' => $node->name,
                    'url' => route('products.category', ['path' => $tree->slugPath($node->id, $locale)]),
                ])
                ->all(),
        ];
    }

    /**
     * @return list<array{label: string, url: string|null}>
     */
    public static function forProduct(CategoryTree $tree, Product $product): array
    {
        $category = $tree->find($product->category_id);

        return [
            ...($category ? self::forCategory($tree, $category) : self::forCatalog()),
            ['label' => $product->name, 'url' => null],
        ];
    }
}
