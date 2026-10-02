<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingsRequest;
use App\Settings\ContactSettings;
use App\Settings\SeoSettings;
use App\Settings\SiteSettings;
use App\Settings\SocialSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function edit(ContactSettings $contact, SocialSettings $social, SeoSettings $seo, SiteSettings $site): Response
    {
        Gate::authorize(Permission::SettingsManage->value);

        return Inertia::render('Admin/Settings/Edit', [
            'settings' => [
                'contact' => $contact->toArray(),
                'social' => $social->toArray(),
                'seo' => $seo->toArray(),
                'site' => $site->toArray(),
            ],
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
        ])->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Settings saved.')]);

        return to_route('admin.settings.edit');
    }
}
