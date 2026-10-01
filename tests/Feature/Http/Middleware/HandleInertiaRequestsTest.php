<?php

use App\Enums\Role;
use App\Models\User;
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
        ])
        ->where('translations.Products', 'المنتجات'));
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

    $response->assertSee('<html lang="ar" dir="rtl">', escape: false);
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
