<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Http\Resources\CategoryFormResource;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\Product;
use App\Services\Catalog\CategoryTreeService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function __construct(private readonly CategoryTreeService $categoryTree) {}

    /**
     * Show the category tree manager.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Category::class);

        $productCounts = Product::query()->toBase()
            ->selectRaw('category_id, count(*) as aggregate')
            ->groupBy('category_id')
            ->pluck('aggregate', 'category_id');

        return Inertia::render('Admin/Categories/Index', [
            'tree' => CategoryResource::collection($this->withProductCounts($this->categoryTree->tree()->nested(), $productCounts)),
            'highlightId' => $request->integer('highlight') ?: null,
        ]);
    }

    /**
     * Show the form for a new category, optionally preselecting its parent ("Add child").
     */
    public function create(Request $request): Response
    {
        Gate::authorize('create', Category::class);

        $parentId = $request->integer('parent_id') ?: null;

        return Inertia::render('Admin/Categories/Create', [
            'parentId' => $parentId !== null && $this->categoryTree->tree()->has($parentId) ? $parentId : null,
            'categories' => CategoryResource::collection($this->categoryTree->tree()->nested()),
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $category = Category::create([
            'parent_id' => $request->parentId(),
            'status' => $request->boolean('status'),
            'sort_order' => $this->categoryTree->nextSortOrder($request->parentId()),
            ...$request->translations(),
        ]);

        if ($request->hasFile('image')) {
            $category->addMediaFromRequest('image')->toMediaCollection(Category::IMAGE_COLLECTION);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Category ":name" created.', ['name' => $category->name])]);

        return to_route('admin.categories.index', ['highlight' => $category->id]);
    }

    public function edit(Category $category): Response
    {
        Gate::authorize('update', $category);

        return Inertia::render('Admin/Categories/Edit', [
            'category' => CategoryFormResource::make($category->load('translations', 'media')),
            'categories' => CategoryResource::collection($this->categoryTree->tree()->nested()),
        ]);
    }

    /**
     * Update a category. Choosing a different parent moves it (with its subtree)
     * to the end of the new parent's children.
     */
    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        DB::transaction(function () use ($request, $category) {
            $category->update([
                'status' => $request->boolean('status'),
                ...$request->translations(),
            ]);

            if ($request->parentId() !== $category->parent_id) {
                $this->categoryTree->move($category, $request->parentId(), PHP_INT_MAX);
            }
        });

        if ($request->hasFile('image')) {
            $category->addMediaFromRequest('image')->toMediaCollection(Category::IMAGE_COLLECTION);
        } elseif ($request->boolean('remove_image')) {
            $category->clearMediaCollection(Category::IMAGE_COLLECTION);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Category ":name" updated.', ['name' => $category->name])]);

        return to_route('admin.categories.index', ['highlight' => $category->id]);
    }

    /**
     * Delete a category. Its child categories are deleted only with explicit consent,
     * and categories that still contain products are never deleted.
     */
    public function destroy(Request $request, Category $category): RedirectResponse
    {
        Gate::authorize('delete', $category);

        $name = $category->name;

        $this->categoryTree->delete($category, $request->boolean('with_descendants'));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Category ":name" deleted.', ['name' => $name])]);

        return to_route('admin.categories.index');
    }

    /**
     * Attach each node's direct product count, recursively.
     *
     * @param  Collection<int, Category>  $nodes
     * @param  SupportCollection<int, int>  $productCounts
     * @return Collection<int, Category>
     */
    private function withProductCounts(Collection $nodes, SupportCollection $productCounts): Collection
    {
        return $nodes->each(function (Category $node) use ($productCounts) {
            $node->setAttribute('products_count', (int) ($productCounts[$node->id] ?? 0));
            $this->withProductCounts($node->children, $productCounts);
        });
    }
}
