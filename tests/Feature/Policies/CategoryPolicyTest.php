<?php

use App\Enums\Permission;
use App\Models\Category;

it('grants each ability only with its permission', function (string $ability, Permission $permission) {
    $category = Category::factory()->create();
    $arguments = in_array($ability, ['viewAny', 'create', 'move'], true) ? Category::class : $category;

    $userWithPermission = userWithPermissions($permission);
    $userWithoutPermission = userWithPermissions();

    expect($userWithPermission->can($ability, $arguments))->toBeTrue()
        ->and($userWithoutPermission->can($ability, $arguments))->toBeFalse();
})->with([
    'view the tree' => ['viewAny', Permission::CategoriesView],
    'create' => ['create', Permission::CategoriesCreate],
    'update' => ['update', Permission::CategoriesUpdate],
    'delete' => ['delete', Permission::CategoriesDelete],
    'move and reorder' => ['move', Permission::CategoriesMove],
]);

it('grants every ability to admins', function () {
    $admin = admin();
    $category = Category::factory()->create();

    expect($admin->can('viewAny', Category::class))->toBeTrue()
        ->and($admin->can('delete', $category))->toBeTrue()
        ->and($admin->can('move', Category::class))->toBeTrue();
});
