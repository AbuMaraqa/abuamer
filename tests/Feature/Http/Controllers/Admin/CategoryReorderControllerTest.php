<?php

use App\Enums\Permission;
use App\Models\Category;

it('applies a new order to the root categories', function () {
    $categories = createCategoryTree(['A' => [], 'B' => [], 'C' => []]);
    $newOrder = [$categories['C']->id, $categories['A']->id, $categories['B']->id];

    $this->actingAs(admin())->patch(route('admin.categories.reorder'), ['parent_id' => null, 'ids' => $newOrder]);

    expect(Category::query()->roots()->ordered()->pluck('id')->all())->toBe($newOrder);
});

it('rejects a list that is not exactly the children of the parent', function () {
    $categories = createCategoryTree(['Parent' => ['A' => [], 'B' => []]]);

    $response = $this->actingAs(admin())->patch(route('admin.categories.reorder'), [
        'parent_id' => $categories['Parent']->id,
        'ids' => [$categories['A']->id],
    ]);

    $response->assertSessionHasErrors('ids');
});

it('forbids users who cannot move categories', function () {
    $categories = createCategoryTree(['A' => [], 'B' => []]);

    $response = $this->actingAs(userWithPermissions(Permission::CategoriesUpdate))->patch(route('admin.categories.reorder'), [
        'ids' => [$categories['B']->id, $categories['A']->id],
    ]);

    $response->assertForbidden();
});
