<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductFormResource;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\Catalog\CategoryTreeService;
use App\Services\Catalog\ProductService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function __construct(
        private readonly CategoryTreeService $categoryTree,
        private readonly ProductService $products,
    ) {}

    /**
     * List products with server-side search and filters.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Product::class);

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'integer'],
            'descendants' => ['nullable', 'boolean'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'featured' => ['nullable', Rule::in(['yes', 'no'])],
        ]);

        $tree = $this->categoryTree->tree();
        $categoryId = isset($filters['category']) && $tree->has((int) $filters['category']) ? (int) $filters['category'] : null;
        $withDescendants = $request->boolean('descendants', true);

        $products = Product::query()
            ->with(['translations', 'media'])
            ->when($filters['q'] ?? null, fn (Builder $query, string $term) => $query->search($term))
            ->when($categoryId, fn (Builder $query, int $id) => $query->inCategories($withDescendants ? $tree->subtreeIds($id) : [$id]))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status === 'active'))
            ->when($filters['featured'] ?? null, fn (Builder $query, string $featured) => $query->where('featured', $featured === 'yes'))
            ->orderByDesc('id')
            ->paginate(config('catalog.admin_per_page'))
            ->withQueryString();

        return Inertia::render('Admin/Products/Index', [
            'products' => ProductResource::collection($products),
            'categories' => CategoryResource::collection($tree->nested()),
            'filters' => [
                'q' => $filters['q'] ?? '',
                'category' => $categoryId,
                'descendants' => $withDescendants,
                'status' => $filters['status'] ?? null,
                'featured' => $filters['featured'] ?? null,
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        Gate::authorize('create', Product::class);

        $categoryId = $request->integer('category_id') ?: null;

        return Inertia::render('Admin/Products/Create', [
            'categoryId' => $categoryId !== null && $this->categoryTree->tree()->has($categoryId) ? $categoryId : null,
            'categories' => CategoryResource::collection($this->categoryTree->tree()->nested()),
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $product = $this->products->save(
            new Product,
            $request->productAttributes(),
            $request->specifications(),
            mainImage: $request->file('main_image'),
            galleryUploads: $request->galleryUploads(),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Product ":name" created.', ['name' => $product->name])]);

        return to_route('admin.products.edit', $product);
    }

    public function edit(Product $product): Response
    {
        Gate::authorize('update', $product);

        return Inertia::render('Admin/Products/Edit', [
            'product' => ProductFormResource::make($product->load(['translations', 'media', 'specifications.translations'])),
            'categories' => CategoryResource::collection($this->categoryTree->tree()->nested()),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->products->save(
            $product,
            $request->productAttributes(),
            $request->specifications(),
            mainImage: $request->file('main_image'),
            removeMainImage: $request->boolean('remove_main_image'),
            keptGalleryIds: $request->galleryIds(),
            galleryUploads: $request->galleryUploads(),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Product ":name" saved.', ['name' => $product->name])]);

        return to_route('admin.products.edit', $product);
    }

    public function destroy(Product $product): RedirectResponse
    {
        Gate::authorize('delete', $product);

        $product->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Product ":name" deleted.', ['name' => $product->name])]);

        return to_route('admin.products.index');
    }
}
