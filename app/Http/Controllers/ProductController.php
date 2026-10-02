<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductDetailResource;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\Catalog\CategoryTreeService;
use App\Support\Breadcrumbs;
use App\Support\LocalizedUrl;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function __construct(private readonly CategoryTreeService $categoryTree) {}

    /**
     * The catalog: root categories and a searchable, paginated list of every visible product.
     */
    public function index(Request $request): Response
    {
        $tree = $this->categoryTree->tree();
        $visibleIds = $tree->visibleIds();

        $categoryId = $request->integer('category') ?: null;
        $categoryIds = $categoryId !== null && in_array($categoryId, $visibleIds, true)
            ? $tree->subtreeIds($categoryId, activeOnly: true)
            : $visibleIds;

        $products = Product::query()
            ->active()
            ->inCategories($categoryIds)
            ->when($request->string('q')->trim()->value(), fn (Builder $query, string $term) => $query->search($term))
            ->when($request->boolean('featured'), fn (Builder $query) => $query->featured())
            ->with(['translations', 'media'])
            ->ordered()
            ->paginate(config('catalog.per_page'))
            ->withQueryString();

        return Inertia::render('Catalog/Index', [
            'categories' => CategoryResource::collection($tree->roots()->filter->status->values()->load('media')),
            'products' => ProductResource::collection($products),
            'filters' => [
                'q' => $request->string('q')->trim()->value(),
                'category' => $categoryId,
                'featured' => $request->boolean('featured'),
            ],
            'breadcrumbs' => Breadcrumbs::forCatalog(),
        ]);
    }

    /**
     * Show a product page. A slug from another language redirects to this language's URL.
     */
    public function show(string $slug): Response|RedirectResponse
    {
        $locale = app()->getLocale();
        $tree = $this->categoryTree->tree();

        $product = Product::query()->active()->whereTranslation('slug', $slug, $locale)->first();

        if ($product === null) {
            $product = Product::query()->active()->whereTranslation('slug', $slug)->with('translations')->first();
            $localizedSlug = $product?->translate($locale)?->slug;

            abort_if($localizedSlug === null, 404);

            return redirect()->route('products.show', ['slug' => $localizedSlug], 301);
        }

        abort_unless($tree->isVisible($product->category_id), 404);

        $product->load(['translations', 'media', 'specifications.translations']);

        $relatedProducts = Product::query()
            ->active()
            ->where('category_id', $product->category_id)
            ->whereKeyNot($product->id)
            ->with(['translations', 'media'])
            ->ordered()
            ->limit(4)
            ->get();

        return Inertia::render('Catalog/Product', [
            'product' => ProductDetailResource::make($product),
            'relatedProducts' => ProductResource::collection($relatedProducts),
            'breadcrumbs' => Breadcrumbs::forProduct($tree, $product),
            'localeUrls' => LocalizedUrl::alternates('products.show', fn (string $locale): array => [
                'slug' => $product->translate($locale, true)->slug,
            ]),
        ]);
    }
}
