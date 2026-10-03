<?php

namespace App\Http\Controllers;

use App\Enums\HighlightType;
use App\Http\Resources\BrandResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\CompanyHighlightResource;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\SlideResource;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Company;
use App\Models\CompanyHighlight;
use App\Models\Product;
use App\Models\Slide;
use App\Services\Catalog\CategoryTree;
use App\Services\Catalog\CategoryTreeService;
use App\Support\Seo\SeoMeta;
use App\Support\Seo\StructuredData;
use Illuminate\Database\Eloquent\Collection;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    /**
     * Show the home page.
     */
    public function __invoke(CategoryTreeService $categoryTree): Response
    {
        $tree = $categoryTree->tree();
        $company = Company::current();
        $highlights = CompanyHighlight::query()->with('translations')->ordered()->get();

        $featuredProducts = Product::query()
            ->active()
            ->featured()
            ->inCategories($tree->visibleIds())
            ->with(['translations', 'media'])
            ->withVisibleBrand()
            ->ordered()
            ->limit(8)
            ->get();

        $brands = Brand::query()
            ->active()
            ->withProductsIn($tree->visibleIds())
            ->with('media')
            ->ordered()
            ->get();

        return Inertia::render('Home', [
            'seo' => SeoMeta::make()->withStructuredData(StructuredData::organization($company)),
            'company' => CompanyResource::make($company),
            'slides' => SlideResource::collection(Slide::query()->active()->with(['translations', 'media'])->ordered()->get()),
            'departments' => CategoryResource::collection($this->departments($tree)),
            'collections' => CategoryResource::collection($tree->collections()->load('media')),
            'featuredProducts' => ProductResource::collection($featuredProducts),
            'brands' => BrandResource::collection($brands),
            'features' => CompanyHighlightResource::collection($highlights->where('type', HighlightType::Feature)->values()),
            'statistics' => CompanyHighlightResource::collection($highlights->where('type', HighlightType::Statistic)->values()),
        ]);
    }

    /**
     * The main product lines, such as "Tiles & Marble" and "Sanitary Ware", each with its
     * collections: the visible root categories, when there are several and they are
     * divided into collections. Empty otherwise, and the collections are shown instead.
     *
     * @return Collection<int, Category>
     */
    private function departments(CategoryTree $tree): Collection
    {
        if ($tree->collectionsParentId() !== null) {
            return new Collection;
        }

        $departments = $tree->nested(activeOnly: true, maxDepth: 2);

        return $departments->count() > 1 && $departments->contains(fn (Category $department): bool => $department->children->isNotEmpty())
            ? $departments->load('media')
            : new Collection;
    }
}
