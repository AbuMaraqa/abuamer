<?php

namespace Database\Seeders;

use App\Enums\Permission as PermissionName;
use App\Enums\Role as RoleName;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Sync the permissions and the built-in roles defined by the application enums.
     * Safe to run repeatedly, including on production deployments: the administrator
     * always receives every permission, while the other built-in roles get their
     * default permissions only when they are created, so changes made to them in the
     * control panel are kept.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (PermissionName::cases() as $permission) {
            Permission::findOrCreate($permission->value);
        }

        foreach (RoleName::cases() as $roleName) {
            $role = Role::query()->where('name', $roleName->value)->first();

            if ($role === null || $roleName === RoleName::Admin) {
                Role::findOrCreate($roleName->value)->syncPermissions(
                    array_map(fn (PermissionName $permission): string => $permission->value, $roleName->permissions()),
                );
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
