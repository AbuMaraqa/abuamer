<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateCompanyRequest;
use App\Http\Resources\CompanyFormResource;
use App\Models\Company;
use App\Models\CompanyHighlight;
use App\Services\CompanyService;
use App\Support\MediaSync;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class CompanyController extends Controller
{
    /**
     * Show the company content editor (identity, home page, about page).
     */
    public function edit(): Response
    {
        Gate::authorize(Permission::CompanyManage->value);

        return Inertia::render('Admin/Company/Edit', [
            'company' => new CompanyFormResource(
                Company::current(),
                CompanyHighlight::query()->with('translations')->ordered()->get(),
            ),
            'featureIcons' => CompanyHighlight::FEATURE_ICONS,
        ]);
    }

    public function update(UpdateCompanyRequest $request, CompanyService $companyService): RedirectResponse
    {
        $company = Company::current();

        $companyService->save(
            $company,
            $request->validated('founded_year'),
            $request->translations(),
            $request->highlights(),
        );

        foreach ([Company::LOGO_COLLECTION => 'logo', Company::FAVICON_COLLECTION => 'favicon', Company::HERO_COLLECTION => 'hero_image', Company::ABOUT_COLLECTION => 'about_image'] as $collection => $input) {
            MediaSync::single($company, $collection, $request->file($input), $request->boolean("remove_{$input}"));
        }

        MediaSync::gallery($company, Company::GALLERY_COLLECTION, $request->galleryIds(), $request->galleryUploads());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Company content saved.')]);

        return to_route('admin.company.edit');
    }
}
