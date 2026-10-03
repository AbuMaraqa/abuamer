<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Permission;
use App\Enums\Role as RoleName;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Http\Resources\RoleResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

/**
 * Staff accounts of the control panel.
 */
class UserController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize(Permission::UsersManage->value);

        $term = $request->string('q')->trim()->value();

        $users = User::query()
            ->with('roles')
            ->when($term, fn (Builder $query, string $term) => $query->where(fn (Builder $query) => $query
                ->where('name', 'like', '%'.addcslashes($term, '\\%_').'%')
                ->orWhere('email', 'like', '%'.addcslashes($term, '\\%_').'%')))
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(config('catalog.admin_per_page'))
            ->withQueryString();

        return Inertia::render('Admin/Users/Index', [
            'users' => UserResource::collection($users),
            'filters' => ['q' => $term],
        ]);
    }

    public function create(): Response
    {
        Gate::authorize(Permission::UsersManage->value);

        return Inertia::render('Admin/Users/Create', [
            'roles' => $this->roleOptions(),
            'permissions' => Permission::assignableGroups(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = DB::transaction(function () use ($request): User {
            $user = User::create($request->userAttributes());
            $user->syncRoles([$request->role()]);

            return $user;
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('User ":name" added.', ['name' => $user->name])]);

        return to_route('admin.users.index');
    }

    public function edit(User $user): Response
    {
        Gate::authorize(Permission::UsersManage->value);

        return Inertia::render('Admin/Users/Edit', [
            'user' => UserResource::make($user->load('roles')),
            'roles' => $this->roleOptions(),
            'permissions' => Permission::assignableGroups(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        DB::transaction(function () use ($request, $user) {
            $user->fill($request->userAttributes())->save();
            $user->syncRoles([$request->role()]);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('User ":name" saved.', ['name' => $user->name])]);

        return to_route('admin.users.index');
    }

    /**
     * Delete a staff account. Nobody can delete their own account, so the administrator
     * deleting always remains.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        Gate::authorize(Permission::UsersManage->value);

        if ($user->is($request->user())) {
            throw ValidationException::withMessages(['user' => __('You cannot delete your own account.')]);
        }

        $user->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('User ":name" deleted.', ['name' => $user->name])]);

        return to_route('admin.users.index');
    }

    /**
     * The roles to choose from, administrator first, each with its permissions.
     */
    private function roleOptions(): AnonymousResourceCollection
    {
        $roles = Role::query()
            ->with('permissions')
            ->get()
            ->sortBy(fn (Role $role): array => [RoleName::tryFrom($role->name) === null, RoleName::tryFrom($role->name)?->label() ?? $role->name])
            ->values();

        return RoleResource::collection($roles);
    }
}
