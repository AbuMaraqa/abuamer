<?php

use App\Enums\Permission;
use App\Models\Category;

it('moves a category into another parent at the requested position', function () {
    $categories = createCategoryTree(['Floor Tiles' => ['Marble' => []], 'Porcelain' => ['Stone Effect' => [], 'Wood Effect' => []]]);

    $response = $this->actingAs(admin())
        ->from(route('admin.categories.index'))
        ->patch(route('admin.categories.move', $categories['Marble']), [
            'parent_id' => $categories['Porcelain']->id,
            'sort_order' => 2,
        ]);

    $response->assertRedirect(route('admin.categories.index'));
    expect(Category::query()->where('parent_id', $categories['Porcelain']->id)->ordered()->pluck('id')->all())->toBe([
        $categories['Stone Effect']->id,
        $categories['Marble']->id,
        $categories['Wood Effect']->id,
    ]);
});

it('moves a category to the root level', function () {
    $categories = createCategoryTree(['Porcelain' => ['Marble Effect' => []]]);

    $this->actingAs(admin())->patch(route('admin.categories.move', $categories['Marble Effect']), [
        'parent_id' => null,
        'sort_order' => 1,
    ]);

    expect($categories['Marble Effect']->fresh()->parent_id)->toBeNull();
});

it('rejects moving a category into one of its descendants', function () {
    $categories = createCategoryTree(['A' => ['B' => ['C' => []]]]);

    $response = $this->actingAs(admin())->patch(route('admin.categories.move', $categories['A']), [
        'parent_id' => $categories['C']->id,
        'sort_order' => 1,
    ]);

    $response->assertSessionHasErrors(['parent_id' => 'لا يمكن نقل التصنيف إلى نفسه أو إلى أحد تصنيفاته الفرعية.']);
    expect($categories['A']->fresh()->parent_id)->toBeNull();
});

it('rejects moving a category into itself', function () {
    $category = Category::factory()->create();

    $response = $this->actingAs(admin())->patch(route('admin.categories.move', $category), [
        'parent_id' => $category->id,
        'sort_order' => 1,
    ]);

    $response->assertSessionHasErrors('parent_id');
});

it('rejects a parent that does not exist', function () {
    $category = Category::factory()->create();

    $response = $this->actingAs(admin())->patch(route('admin.categories.move', $category), [
        'parent_id' => 999,
        'sort_order' => 1,
    ]);

    $response->assertSessionHasErrors('parent_id');
});

it('rejects a position below one', function () {
    $category = Category::factory()->create();

    $response = $this->actingAs(admin())->patch(route('admin.categories.move', $category), [
        'parent_id' => null,
        'sort_order' => 0,
    ]);

    $response->assertSessionHasErrors('sort_order');
});

it('forbids users who cannot move categories', function () {
    $categories = createCategoryTree(['A' => [], 'B' => []]);

    $response = $this->actingAs(userWithPermissions(Permission::CategoriesUpdate))
        ->patch(route('admin.categories.move', $categories['A']), [
            'parent_id' => $categories['B']->id,
            'sort_order' => 1,
        ]);

    $response->assertForbidden();
    expect($categories['A']->fresh()->parent_id)->toBeNull();
});
