<?php

namespace App\Http\Controllers;

use App\Http\Resources\BrandResource;
use App\Http\Resources\ProductResource;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\Catalog\CategoryTreeService;
use App\Support\Breadcrumbs;
use App\Support\Seo\SeoMeta;
use App\Support\Seo\StructuredData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BrandController extends Controller
{
    public function __construct(private readonly CategoryTreeService $categoryTree) {}

    /**
     * Every visible brand with the number of its products on the website.
     */
    public function index(): Response
    {
        $visibleIds = $this->categoryTree->tree()->visibleIds();

        $brands = Brand::query()
            ->active()
            ->with(['translations', 'media'])
            ->withCount(['products' => fn (Builder $products) => $products->active()->inCategories($visibleIds)])
            ->ordered()
            ->get();

        $breadcrumbs = Breadcrumbs::forBrands();

        return Inertia::render('Brands/Index', [
            'seo' => SeoMeta::make()
                ->title(__('Brands'))
                ->description(__('The international brands of sanitary ware, mixers and tiles that we carry.'))
                ->withStructuredData(StructuredData::breadcrumbs($breadcrumbs)),
            'brands' => BrandResource::collection($brands),
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * A brand page: its story and its products, filterable by collection and searchable.
     */
    public function show(Request $request, Brand $brand): Response
    {
        abort_unless($brand->status, 404);

        $tree = $this->categoryTree->tree();
        $visibleIds = $tree->visibleIds();

        // The collections (and departments) in which this brand has products, offered as filters.
        $brandCategoryIds = Product::query()
            ->active()
            ->where('brand_id', $brand->id)
            ->inCategories($visibleIds)
            ->distinct()
            ->pluck('category_id')
            ->all();

        $collections = $tree->collections()
            ->filter(fn (Category $collection): bool => array_intersect($tree->subtreeIds($collection->id, activeOnly: true), $brandCategoryIds) !== [])
            ->values();

        $categoryId = $request->integer('category') ?: null;
        $categoryIds = $categoryId !== null && $collections->contains('id', $categoryId)
            ? $tree->subtreeIds($categoryId, activeOnly: true)
            : $visibleIds;

        $products = Product::query()
            ->active()
            ->where('brand_id', $brand->id)
            ->inCategories($categoryIds)
            ->when($request->string('q')->trim()->value(), fn (Builder $query, string $term) => $query->search($term))
            ->with(['translations', 'media'])
            ->ordered()
            ->paginate(config('catalog.per_page'))
            ->withQueryString();

        $brand->load(['translations', 'media']);
        $breadcrumbs = Breadcrumbs::forBrand($brand);

        return Inertia::render('Brands/Show', [
            'seo' => SeoMeta::make()
                ->title($brand->name)
                ->description($brand->description ?: __('Explore the :brand products available at our showroom.', ['brand' => $brand->name]))
                ->image($brand->getFirstMediaUrl(Brand::LOGO_COLLECTION) ?: null)
                ->noindex($request->filled('q') || $request->filled('category'))
                ->withStructuredData(StructuredData::breadcrumbs($breadcrumbs)),
            'brand' => BrandResource::make($brand),
            'collections' => $collections->map(fn (Category $collection): array => ['id' => $collection->id, 'name' => $collection->name])->all(),
            'products' => ProductResource::collection($products),
            'filters' => [
                'q' => $request->string('q')->trim()->value(),
                'category' => $categoryId,
            ],
            'breadcrumbs' => $breadcrumbs,
        ]);
    }
}
