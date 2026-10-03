<?php

use App\Enums\Permission;
use App\Enums\Role as RoleName;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;

function role(RoleName|string $name): Role
{
    return Role::findByName($name instanceof RoleName ? $name->value : $name);
}

it('lists the users with their roles, active users first', function () {
    $admin = admin();
    User::factory()->inactive()->create(['name' => 'Aaron Former'])->assignRole(RoleName::Editor);
    User::factory()->create(['name' => 'Sara Sales'])->assignRole(RoleName::Editor);

    $response = $this->actingAs($admin)->get(route('admin.users.index'));

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Admin/Users/Index')
        ->where('users.meta.total', 3)
        ->where('users.data.2.name', 'Aaron Former')
        ->where('users.data.2.is_active', false)
        ->where('users.data', fn ($users) => collect($users)->firstWhere('id', $admin->id)['is_current'] === true));
});

it('searches users by name or email', function () {
    $admin = admin();
    User::factory()->create(['name' => 'Sara Sales', 'email' => 'sara@nasaq.test']);
    User::factory()->create(['name' => 'Omar Stock', 'email' => 'omar@nasaq.test']);

    $response = $this->actingAs($admin)->get(route('admin.users.index', ['q' => 'sara@']));

    $response->assertInertia(fn (Assert $page) => $page->where('users.meta.total', 1)->where('users.data.0.name', 'Sara Sales'));
});

it('adds a staff account with a role', function () {
    $admin = admin();

    $response = $this->actingAs($admin)->post(route('admin.users.store'), [
        'name' => 'Sara Sales',
        'email' => ' Sara@Nasaq.test ',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
        'role_id' => role(RoleName::Editor)->id,
        'is_active' => true,
    ]);

    $response->assertRedirect(route('admin.users.index'));
    $user = User::query()->where('email', 'sara@nasaq.test')->sole();
    expect($user->hasRole(RoleName::Editor))->toBeTrue()
        ->and(Hash::check('secret123', $user->password))->toBeTrue()
        ->and($user->can(Permission::ProductsCreate->value))->toBeTrue()
        ->and($user->can(Permission::SettingsManage->value))->toBeFalse();
});

it('requires a strong, confirmed password and a unique email', function () {
    $admin = admin();

    $response = $this->actingAs($admin)->post(route('admin.users.store'), [
        'name' => 'Copy',
        'email' => $admin->email,
        'password' => 'short',
        'password_confirmation' => 'other',
        'role_id' => role(RoleName::Editor)->id,
        'is_active' => true,
    ]);

    $response->assertSessionHasErrors(['email', 'password']);
});

it('changes the role and keeps the password when none is typed', function () {
    $admin = admin();
    $user = User::factory()->create()->assignRole(RoleName::Editor);
    $password = $user->password;

    $this->actingAs($admin)->put(route('admin.users.update', $user), [
        'name' => 'Promoted',
        'email' => $user->email,
        'password' => '',
        'password_confirmation' => '',
        'role_id' => role(RoleName::Admin)->id,
        'is_active' => true,
    ])->assertSessionHasNoErrors();

    expect($user->fresh()->hasRole(RoleName::Admin))->toBeTrue()
        ->and($user->fresh()->hasRole(RoleName::Editor))->toBeFalse()
        ->and($user->fresh()->password)->toBe($password);
});

it('does not let administrators lock themselves out', function () {
    $admin = admin();

    $response = $this->actingAs($admin)->put(route('admin.users.update', $admin), [
        'name' => $admin->name,
        'email' => $admin->email,
        'role_id' => role(RoleName::Editor)->id,
        'is_active' => false,
    ]);

    $response->assertSessionHasErrors(['role_id', 'is_active']);
    expect($admin->fresh()->hasRole(RoleName::Admin))->toBeTrue();
});

it('deletes another user but not your own account', function () {
    $admin = admin();
    $user = User::factory()->create()->assignRole(RoleName::Editor);

    $this->actingAs($admin)->delete(route('admin.users.destroy', $admin))->assertSessionHasErrors('user');
    $this->actingAs($admin)->delete(route('admin.users.destroy', $user))->assertRedirect(route('admin.users.index'));

    $this->assertModelMissing($user);
    $this->assertModelExists($admin);
});

it('reserves user management to administrators', function () {
    admin();
    $editor = User::factory()->create()->assignRole(RoleName::Editor);

    $this->actingAs($editor)->get(route('admin.users.index'))->assertForbidden();
    $this->actingAs($editor)->post(route('admin.users.store'), ['name' => 'X'])->assertForbidden();
    $this->actingAs($editor)->get(route('admin.roles.index'))->assertForbidden();
});
