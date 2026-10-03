<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Permission;
use App\Enums\Role as RoleName;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

/**
 * Roles group the permissions given to staff, e.g. "Sales staff" may edit products
 * and read messages but not change the website settings.
 */
class RoleController extends Controller
{
    public function index(): Response
    {
        Gate::authorize(Permission::UsersManage->value);

        $roles = Role::query()
            ->with('permissions')
            ->withCount('users')
            ->get()
            ->sortBy(fn (Role $role): array => [RoleName::tryFrom($role->name) === null, $role->id])
            ->values();

        return Inertia::render('Admin/Roles/Index', [
            'roles' => RoleResource::collection($roles),
            'permissions' => Permission::assignableGroups(),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize(Permission::UsersManage->value);

        return Inertia::render('Admin/Roles/Create', [
            'permissions' => Permission::assignableGroups(),
        ]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $role = DB::transaction(function () use ($request): Role {
            $role = Role::create(['name' => $request->validated('name')]);
            $role->syncPermissions($request->permissionNames());

            return $role;
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Role ":name" added.', ['name' => $role->name])]);

        return to_route('admin.roles.index');
    }

    public function edit(Role $role): Response
    {
        Gate::authorize(Permission::UsersManage->value);

        abort_if($role->name === RoleName::Admin->value, 403);

        return Inertia::render('Admin/Roles/Edit', [
            'role' => RoleResource::make($role->load('permissions')->loadCount('users')),
            'permissions' => Permission::assignableGroups(),
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        DB::transaction(function () use ($request, $role) {
            $role->update(['name' => $request->validated('name')]);
            $role->syncPermissions($request->permissionNames());
        });

        $label = RoleName::tryFrom($role->name)?->label() ?? $role->name;
        Inertia::flash('toast', ['type' => 'success', 'message' => __('Role ":name" saved.', ['name' => $label])]);

        return to_route('admin.roles.index');
    }

    /**
     * Delete a role created in the control panel, once no user has it.
     */
    public function destroy(Role $role): RedirectResponse
    {
        Gate::authorize(Permission::UsersManage->value);

        abort_if(RoleName::tryFrom($role->name) !== null, 403);

        if ($role->users()->exists()) {
            throw ValidationException::withMessages(['role' => __('Give its users another role before deleting this role.')]);
        }

        $role->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Role ":name" deleted.', ['name' => $role->name])]);

        return to_route('admin.roles.index');
    }
}
