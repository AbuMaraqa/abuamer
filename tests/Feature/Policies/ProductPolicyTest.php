<?php

use App\Enums\Permission;
use App\Models\Product;

it('grants each ability only with its permission', function (string $ability, Permission $permission) {
    $product = Product::factory()->create();
    $arguments = in_array($ability, ['viewAny', 'create', 'updateAny'], true) ? Product::class : $product;

    expect(userWithPermissions($permission)->can($ability, $arguments))->toBeTrue()
        ->and(userWithPermissions()->can($ability, $arguments))->toBeFalse();
})->with([
    'list' => ['viewAny', Permission::ProductsView],
    'create' => ['create', Permission::ProductsCreate],
    'update' => ['update', Permission::ProductsUpdate],
    'move several' => ['updateAny', Permission::ProductsUpdate],
    'delete' => ['delete', Permission::ProductsDelete],
]);
