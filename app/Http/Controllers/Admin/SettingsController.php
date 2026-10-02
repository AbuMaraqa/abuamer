<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Permission;
use App\Enums\SiteFont;
use App\Http\Controllers\Controller;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Requests\Admin\UpdateSettingsRequest;
use App\Http\Resources\MediaResource;
use App\Models\Company;
use App\Settings\ContactSettings;
use App\Settings\SeoSettings;
use App\Settings\SiteSettings;
use App\Settings\SocialSettings;
use App\Support\MediaSync;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    /**
     * Branding images edited here, keyed by request input name.
     */
    private const array BRANDING = [
        'logo' => Company::LOGO_COLLECTION,
        'logo_light' => Company::LOGO_LIGHT_COLLECTION,
        'favicon' => Company::FAVICON_COLLECTION,
    ];

    public function edit(ContactSettings $contact, SocialSettings $social, SeoSettings $seo, SiteSettings $site): Response
    {
        Gate::authorize(Permission::SettingsManage->value);

        $company = Company::current();

        return Inertia::render('Admin/Settings/Edit', [
            'settings' => [
                'contact' => $contact->toArray(),
                'social' => $social->toArray(),
                'seo' => $seo->toArray(),
                'site' => $site->toArray(),
            ],
            'branding' => collect(self::BRANDING)
                ->map(fn (string $collection): ?array => ($media = $company->getFirstMedia($collection))
                    ? MediaResource::make($media)->resolve()
                    : null)
                ->all(),
            'fonts' => array_map(
                fn (SiteFont $font): array => ['value' => $font->value, 'label' => $font->label()],
                SiteFont::cases(),
            ),
        ]);
    }

    public function update(UpdateSettingsRequest $request, ContactSettings $contact, SocialSettings $social, SeoSettings $seo, SiteSettings $site): RedirectResponse
    {
        $contact->fill($request->group('contact'))->save();
        $social->fill($request->group('social'))->save();
        $seo->fill($request->group('seo'))->save();
        $site->fill([
            'default_locale' => $request->validated('site.default_locale'),
            'maintenance_mode' => $request->boolean('site.maintenance_mode'),
            'font' => $request->validated('site.font'),
        ])->save();

        $company = Company::current();

        foreach (self::BRANDING as $input => $collection) {
            MediaSync::single($company, $collection, $request->file($input), $request->boolean("remove_{$input}"));
        }

        HandleInertiaRequests::refreshSiteData();
        Inertia::flash('toast', ['type' => 'success', 'message' => __('Settings saved.')]);

        return to_route('admin.settings.edit');
    }
}
