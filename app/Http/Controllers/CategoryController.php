<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryDetailResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use App\Services\Catalog\CategoryTreeService;
use App\Support\Breadcrumbs;
use App\Support\LocalizedUrl;
use App\Support\Seo\SeoMeta;
use App\Support\Seo\StructuredData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    /**
     * Show a category page addressed by its nested slug path, e.g. /ar/products/بورسلان/تأثير-الرخام.
     */
    public function show(Request $request, string $path, CategoryTreeService $categoryTree): Response
    {
        $tree = $categoryTree->tree();
        $category = $tree->findBySlugPath(explode('/', trim($path, '/')), app()->getLocale());

        abort_if($category === null || ! $tree->isVisible($category->id), 404);

        $productCategoryIds = config('catalog.include_descendant_products')
            ? $tree->subtreeIds($category->id, activeOnly: true)
            : [$category->id];

        $products = Product::query()
            ->active()
            ->inCategories($productCategoryIds)
            ->when($request->string('q')->trim()->value(), fn (Builder $query, string $term) => $query->search($term))
            ->with(['translations', 'media'])
            ->ordered()
            ->paginate(config('catalog.per_page'))
            ->withQueryString();

        $relatedCategories = $tree->children($category->parent_id)
            ->filter(fn (Category $sibling): bool => $sibling->status && $sibling->id !== $category->id)
            ->values();

        $detail = Category::query()->with(['translations', 'media'])->findOrFail($category->id);
        $breadcrumbs = Breadcrumbs::forCategory($tree, $category);
        $localeUrls = LocalizedUrl::alternates('products.category', fn (string $locale): array => [
            'path' => $tree->slugPath($category->id, $locale),
        ]);

        return Inertia::render('Catalog/Category', [
            'seo' => SeoMeta::make()
                ->title($detail->seo_title ?: $detail->name)
                ->description($detail->seo_description ?: $detail->description)
                ->image($detail->getFirstMedia(Category::IMAGE_COLLECTION)?->getAvailableFullUrl(['large']))
                ->alternates($localeUrls)
                ->noindex($request->filled('q'))
                ->withStructuredData(StructuredData::breadcrumbs($breadcrumbs)),
            'category' => CategoryDetailResource::make($detail),
            'children' => CategoryResource::collection($tree->children($category->id)->filter->status->values()->load('media')),
            'relatedCategories' => CategoryResource::collection($relatedCategories->load('media')),
            'products' => ProductResource::collection($products),
            'filters' => ['q' => $request->string('q')->trim()->value()],
            'breadcrumbs' => $breadcrumbs,
            'localeUrls' => $localeUrls,
        ]);
    }
}
