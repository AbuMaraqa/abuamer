<?php

use App\Enums\Permission;
use App\Enums\Role as RoleName;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;

it('lists the built-in roles first with their users counted', function () {
    $admin = admin();
    Role::create(['name' => 'Sales staff']);

    $response = $this->actingAs($admin)->get(route('admin.roles.index'));

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Admin/Roles/Index')
        ->where('roles.0.name', 'admin')
        ->where('roles.0.label', 'مدير النظام')
        ->where('roles.0.users_count', 1)
        ->where('roles.2.name', 'Sales staff')
        ->where('roles.2.is_built_in', false)
        ->has('permissions'));
});

it('creates a role with its permissions and the ones they depend on', function () {
    $admin = admin();

    $response = $this->actingAs($admin)->post(route('admin.roles.store'), [
        'name' => 'Sales staff',
        'permissions' => [Permission::ProductsUpdate->value, Permission::MessagesManage->value],
    ]);

    $response->assertRedirect(route('admin.roles.index'));
    expect(Role::findByName('Sales staff')->permissions->pluck('name')->sort()->values()->all())
        ->toBe(['messages.manage', 'products.update', 'products.view']);
});

it('never grants user management to a role', function () {
    $admin = admin();

    $response = $this->actingAs($admin)->post(route('admin.roles.store'), [
        'name' => 'Sneaky',
        'permissions' => [Permission::UsersManage->value],
    ]);

    $response->assertSessionHasErrors('permissions.0');
});

it('changes the permissions of a role, which apply to its users at once', function () {
    $admin = admin();
    $role = Role::create(['name' => 'Sales staff'])->givePermissionTo(Permission::ProductsView->value);
    $employee = User::factory()->create()->assignRole($role);

    $this->actingAs($admin)->put(route('admin.roles.update', $role), [
        'name' => 'Showroom staff',
        'permissions' => [Permission::SettingsManage->value],
    ])->assertSessionHasNoErrors();

    expect($role->fresh()->name)->toBe('Showroom staff')
        ->and($employee->fresh()->can(Permission::SettingsManage->value))->toBeTrue()
        ->and($employee->fresh()->can(Permission::ProductsView->value))->toBeFalse();
});

it('keeps the name of a built-in role and refuses to change the administrator role', function () {
    $admin = admin();

    $this->actingAs($admin)->put(route('admin.roles.update', Role::findByName(RoleName::Editor->value)), [
        'name' => 'Renamed',
        'permissions' => [Permission::ProductsView->value],
    ])->assertSessionHasNoErrors();

    $this->actingAs($admin)->put(route('admin.roles.update', Role::findByName(RoleName::Admin->value)), [
        'name' => 'admin',
        'permissions' => [],
    ])->assertForbidden();

    expect(Role::findByName(RoleName::Editor->value)->permissions->pluck('name')->all())->toBe(['products.view'])
        ->and(Role::findByName(RoleName::Admin->value)->permissions)->toHaveCount(count(Permission::cases()));
});

it('deletes a role without users only', function () {
    $admin = admin();
    $used = Role::create(['name' => 'Used']);
    User::factory()->create()->assignRole($used);
    $unused = Role::create(['name' => 'Unused']);

    $this->actingAs($admin)->delete(route('admin.roles.destroy', $used))->assertSessionHasErrors('role');
    $this->actingAs($admin)->delete(route('admin.roles.destroy', $unused))->assertRedirect(route('admin.roles.index'));
    $this->actingAs($admin)->delete(route('admin.roles.destroy', Role::findByName(RoleName::Editor->value)))->assertForbidden();

    $this->assertModelExists($used);
    $this->assertModelMissing($unused);
});

it('keeps the changes made to built-in roles when the roles are synced again', function () {
    admin();
    Role::findByName(RoleName::Editor->value)->syncPermissions([Permission::ProductsView->value]);

    $this->seed(RolesAndPermissionsSeeder::class);

    expect(Role::findByName(RoleName::Editor->value)->permissions->pluck('name')->all())->toBe(['products.view']);
});
