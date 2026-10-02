<?php

use App\Enums\Role;
use App\Models\User;
use App\Settings\ContactSettings;
use Database\Seeders\RolesAndPermissionsSeeder;
use Inertia\Testing\AssertableInertia as Assert;

it('shares the Arabic locale with a right-to-left direction', function () {
    $response = $this->get(route('home'));

    $response->assertInertia(fn (Assert $page) => $page
        ->where('locale.current', 'ar')
        ->where('locale.direction', 'rtl')
        ->where('localeUrls', [
            'ar' => url('ar'),
            'en' => url('en'),
            'he' => url('he'),
        ])
        ->where('translations.Products', 'المنتجات'));
});

it('shares the Hebrew locale with a right-to-left direction and Hebrew interface texts', function () {
    $this->useRoutingLocale('he');

    $response = $this->get(route('home'));

    $response->assertInertia(fn (Assert $page) => $page
        ->where('locale.current', 'he')
        ->where('locale.direction', 'rtl')
        ->where('translations.Products', 'מוצרים'));
});

it('renders Hebrew documents right to left', function () {
    $this->useRoutingLocale('he');

    $response = $this->get(route('home'));

    $response->assertSeeInOrder(['<html', 'lang="he"', 'dir="rtl"', '<head>'], escape: false);
});

it('tells the forms which content languages are optional and what they fall back to', function () {
    $response = $this->get(route('home'));

    $response->assertInertia(fn (Assert $page) => $page->where('locale.supported', fn ($locales) => collect($locales)
        ->map(fn (array $locale) => [$locale['code'], $locale['direction'], $locale['required'], $locale['fallback']])
        ->all() === [
            ['ar', 'rtl', true, 'en'],
            ['en', 'ltr', true, 'ar'],
            ['he', 'rtl', false, 'en'],
        ]));
});

it('shows the English contact address on Hebrew pages until a Hebrew one is entered', function () {
    $this->useRoutingLocale('he');
    app(ContactSettings::class)->fill([
        'address' => ['ar' => 'طريق الملك فهد، الرياض', 'en' => 'King Fahd Road, Riyadh', 'he' => ''],
    ])->save();

    $response = $this->get(route('home'));

    $response->assertInertia(fn (Assert $page) => $page->where('site.contact.address', 'King Fahd Road, Riyadh'));
});

it('shares the English locale with a left-to-right direction', function () {
    $this->useRoutingLocale('en');

    $response = $this->get(route('home'));

    $response->assertInertia(fn (Assert $page) => $page
        ->where('locale.current', 'en')
        ->where('locale.direction', 'ltr')
        ->where('translations', []));
});

it('renders the document with the locale language and direction', function () {
    $response = $this->get(route('home'));

    $response->assertSeeInOrder(['<html', 'lang="ar"', 'dir="rtl"', '<head>'], escape: false);
});

it('shares the permissions an editor holds', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $editor = User::factory()->create()->assignRole(Role::Editor);

    $response = $this->actingAs($editor)->get(route('admin.dashboard'));

    $response->assertInertia(fn (Assert $page) => $page
        ->where('auth.user.can', fn ($permissions) => collect($permissions)->only([
            'categories.move',
            'products.delete',
            'settings.manage',
        ])->all() === [
            'categories.move' => true,
            'products.delete' => true,
            'settings.manage' => false,
        ]));
});

it('grants every permission to an admin', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $admin = User::factory()->create()->assignRole(Role::Admin);

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertInertia(fn (Assert $page) => $page
        ->where('auth.user.can', fn ($permissions) => collect($permissions)->every(fn (bool $granted) => $granted)));
});
