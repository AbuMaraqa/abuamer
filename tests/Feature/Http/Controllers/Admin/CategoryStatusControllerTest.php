<?php

use App\Enums\Permission;
use App\Models\Category;

it('disables a category', function () {
    $category = Category::factory()->create();

    $this->actingAs(admin())->patch(route('admin.categories.status', $category), ['status' => false]);

    expect($category->fresh()->status)->toBeFalse();
});

it('requires a boolean status', function () {
    $category = Category::factory()->create();

    $response = $this->actingAs(admin())->patch(route('admin.categories.status', $category), ['status' => 'maybe']);

    $response->assertSessionHasErrors('status');
});

it('forbids users who cannot update categories', function () {
    $category = Category::factory()->create();

    $response = $this->actingAs(userWithPermissions(Permission::CategoriesView))
        ->patch(route('admin.categories.status', $category), ['status' => false]);

    $response->assertForbidden();
    expect($category->fresh()->status)->toBeTrue();
});
