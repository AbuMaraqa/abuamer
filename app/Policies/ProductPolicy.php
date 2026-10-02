<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Product;
use App\Models\User;

/**
 * Admins pass every check through the Gate::before hook in AppServiceProvider.
 */
class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo(Permission::ProductsView->value);
    }

    public function create(User $user): bool
    {
        return $user->checkPermissionTo(Permission::ProductsCreate->value);
    }

    public function update(User $user, Product $product): bool
    {
        return $user->checkPermissionTo(Permission::ProductsUpdate->value);
    }

    /**
     * Moving several products to another category at once.
     */
    public function updateAny(User $user): bool
    {
        return $user->checkPermissionTo(Permission::ProductsUpdate->value);
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->checkPermissionTo(Permission::ProductsDelete->value);
    }
}
