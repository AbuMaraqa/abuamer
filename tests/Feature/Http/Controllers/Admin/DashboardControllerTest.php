<?php

use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Product;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('shows catalog and inbox figures', function () {
    $category = Category::factory()->create();
    Product::factory()->for($category)->count(2)->create();
    Product::factory()->for($category)->inactive()->featured()->create();
    ContactMessage::factory()->create();
    ContactMessage::factory()->read()->create();

    $response = $this->actingAs(admin())->get(route('admin.dashboard'));

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Admin/Dashboard')
        ->where('stats', [
            'categories' => 1,
            'products' => 3,
            'hiddenProducts' => 1,
            'featuredProducts' => 1,
            'unreadMessages' => 1,
        ]));
});

it('is available to every signed-in user', function () {
    $response = $this->actingAs(User::factory()->create())->get(route('admin.dashboard'));

    $response->assertOk();
});
