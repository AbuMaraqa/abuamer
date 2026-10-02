<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSlideRequest;
use App\Http\Requests\Admin\UpdateSlideRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\SlideFormResource;
use App\Http\Resources\SlideResource;
use App\Models\Slide;
use App\Services\Catalog\CategoryTreeService;
use App\Support\MediaSync;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class SlideController extends Controller
{
    public function __construct(private readonly CategoryTreeService $categoryTree) {}

    public function index(): Response
    {
        Gate::authorize(Permission::CompanyManage->value);

        return Inertia::render('Admin/Slides/Index', [
            'slides' => SlideResource::collection(Slide::query()->with(['translations', 'media'])->ordered()->get()),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize(Permission::CompanyManage->value);

        return Inertia::render('Admin/Slides/Create', [
            'categories' => CategoryResource::collection($this->categoryTree->tree()->nested()),
        ]);
    }

    public function store(StoreSlideRequest $request): RedirectResponse
    {
        $slide = Slide::create([
            ...$request->slideAttributes(),
            'sort_order' => (int) Slide::query()->max('sort_order') + 1,
        ]);

        MediaSync::single($slide, Slide::IMAGE_COLLECTION, $request->file('image'));
        MediaSync::single($slide, Slide::MOBILE_IMAGE_COLLECTION, $request->file('mobile_image'));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Slide added.')]);

        return to_route('admin.slides.index');
    }

    public function edit(Slide $slide): Response
    {
        Gate::authorize(Permission::CompanyManage->value);

        return Inertia::render('Admin/Slides/Edit', [
            'slide' => SlideFormResource::make($slide->load(['translations', 'media'])),
            'categories' => CategoryResource::collection($this->categoryTree->tree()->nested()),
        ]);
    }

    public function update(UpdateSlideRequest $request, Slide $slide): RedirectResponse
    {
        $slide->fillWithTranslations($request->slideAttributes())->save();

        MediaSync::single($slide, Slide::IMAGE_COLLECTION, $request->file('image'));
        MediaSync::single($slide, Slide::MOBILE_IMAGE_COLLECTION, $request->file('mobile_image'), $request->boolean('remove_mobile_image'));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Slide saved.')]);

        return to_route('admin.slides.index');
    }

    public function destroy(Slide $slide): RedirectResponse
    {
        Gate::authorize(Permission::CompanyManage->value);

        $slide->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Slide deleted.')]);

        return to_route('admin.slides.index');
    }
}
