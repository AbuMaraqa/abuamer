<?php

use App\Models\User;
use App\Settings\SiteSettings;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    app(SiteSettings::class)->fill(['maintenance_mode' => true])->save();
});

it('shows the maintenance page to visitors', function () {
    $response = $this->get(route('products.index'));

    $response->assertServiceUnavailable()
        ->assertInertia(fn (Assert $page) => $page->component('Maintenance'));
});

it('lets signed-in users browse the website', function () {
    $response = $this->actingAs(User::factory()->create())->get(route('home'));

    $response->assertOk();
});

it('keeps the login page available', function () {
    $response = $this->get(route('login'));

    $response->assertOk();
});
