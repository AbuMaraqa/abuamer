<?php

use App\Enums\Permission;
use App\Models\Category;
use App\Models\Product;

it('moves the selected products to another category', function () {
    $from = Category::factory()->create();
    $to = Category::factory()->create();
    $moved = Product::factory()->count(2)->for($from)->create();
    $untouched = Product::factory()->for($from)->create();

    $response = $this->actingAs(admin())->patch(route('admin.products.category'), [
        'ids' => $moved->pluck('id')->all(),
        'category_id' => $to->id,
    ]);

    $response->assertInertiaFlash('toast.type', 'success');
    expect(Product::query()->where('category_id', $to->id)->pluck('id')->sort()->values()->all())->toBe($moved->pluck('id')->sort()->values()->all())
        ->and($untouched->fresh()->category_id)->toBe($from->id);
});

it('makes the previous category deletable once it is empty', function () {
    $from = Category::factory()->create();
    $to = Category::factory()->create();
    $product = Product::factory()->for($from)->create();
    $admin = admin();

    $this->actingAs($admin)->patch(route('admin.products.category'), ['ids' => [$product->id], 'category_id' => $to->id]);
    $this->actingAs($admin)->delete(route('admin.categories.destroy', $from));

    $this->assertModelMissing($from);
});

it('requires an existing destination category', function () {
    $product = Product::factory()->create();

    $response = $this->actingAs(admin())->patch(route('admin.products.category'), ['ids' => [$product->id], 'category_id' => 999]);

    $response->assertSessionHasErrors('category_id');
});

it('forbids users who cannot update products', function () {
    $product = Product::factory()->create();
    $to = Category::factory()->create();

    $response = $this->actingAs(userWithPermissions(Permission::ProductsView))
        ->patch(route('admin.products.category'), ['ids' => [$product->id], 'category_id' => $to->id]);

    $response->assertForbidden();
});
