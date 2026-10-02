<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Category;
use App\Models\User;

/**
 * Admins pass every check through the Gate::before hook in AppServiceProvider.
 */
class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo(Permission::CategoriesView->value);
    }

    public function create(User $user): bool
    {
        return $user->checkPermissionTo(Permission::CategoriesCreate->value);
    }

    public function update(User $user, Category $category): bool
    {
        return $user->checkPermissionTo(Permission::CategoriesUpdate->value);
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->checkPermissionTo(Permission::CategoriesDelete->value);
    }

    /**
     * Moving covers re-parenting a category and reordering siblings.
     */
    public function move(User $user): bool
    {
        return $user->checkPermissionTo(Permission::CategoriesMove->value);
    }
}
