<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBrandRequest;
use App\Http\Requests\Admin\UpdateBrandRequest;
use App\Http\Resources\BrandFormResource;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use App\Support\MediaSync;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class BrandController extends Controller
{
    public function index(): Response
    {
        Gate::authorize(Permission::BrandsManage->value);

        return Inertia::render('Admin/Brands/Index', [
            'brands' => BrandResource::collection(Brand::query()->with('media')->withCount('products')->ordered()->get()),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize(Permission::BrandsManage->value);

        return Inertia::render('Admin/Brands/Create');
    }

    public function store(StoreBrandRequest $request): RedirectResponse
    {
        $brand = Brand::create([
            ...$request->brandAttributes(),
            'sort_order' => (int) Brand::query()->max('sort_order') + 1,
        ]);

        MediaSync::single($brand, Brand::LOGO_COLLECTION, $request->file('logo'));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Brand ":name" added.', ['name' => $brand->name])]);

        return to_route('admin.brands.index');
    }

    public function edit(Brand $brand): Response
    {
        Gate::authorize(Permission::BrandsManage->value);

        return Inertia::render('Admin/Brands/Edit', [
            'brand' => BrandFormResource::make($brand->load(['translations', 'media'])->loadCount('products')),
        ]);
    }

    public function update(UpdateBrandRequest $request, Brand $brand): RedirectResponse
    {
        $brand->fillWithTranslations($request->brandAttributes())->save();

        MediaSync::single($brand, Brand::LOGO_COLLECTION, $request->file('logo'), $request->boolean('remove_logo'));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Brand ":name" saved.', ['name' => $brand->name])]);

        return to_route('admin.brands.index');
    }

    /**
     * Delete a brand. Its products stay in the catalog without a brand.
     */
    public function destroy(Brand $brand): RedirectResponse
    {
        Gate::authorize(Permission::BrandsManage->value);

        $brand->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Brand ":name" deleted.', ['name' => $brand->name])]);

        return to_route('admin.brands.index');
    }
}
