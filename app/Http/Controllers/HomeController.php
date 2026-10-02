<?php

namespace App\Http\Controllers;

use App\Enums\HighlightType;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\CompanyHighlightResource;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\SlideResource;
use App\Models\Company;
use App\Models\CompanyHighlight;
use App\Models\Product;
use App\Models\Slide;
use App\Services\Catalog\CategoryTreeService;
use App\Support\Seo\SeoMeta;
use App\Support\Seo\StructuredData;
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
            ->ordered()
            ->limit(8)
            ->get();

        return Inertia::render('Home', [
            'seo' => SeoMeta::make()->withStructuredData(StructuredData::organization($company)),
            'company' => CompanyResource::make($company),
            'slides' => SlideResource::collection(Slide::query()->active()->with(['translations', 'media'])->ordered()->get()),
            'collections' => CategoryResource::collection($tree->collections()->load('media')),
            'featuredProducts' => ProductResource::collection($featuredProducts),
            'features' => CompanyHighlightResource::collection($highlights->where('type', HighlightType::Feature)->values()),
            'statistics' => CompanyHighlightResource::collection($highlights->where('type', HighlightType::Statistic)->values()),
        ]);
    }
}
