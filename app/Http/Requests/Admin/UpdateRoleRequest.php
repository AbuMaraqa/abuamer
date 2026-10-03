<?php

namespace App\Http\Requests\Admin;

use App\Enums\Role as RoleName;
use Spatie\Permission\Models\Role;

/**
 * The administrator role always has every permission and cannot be edited; the other
 * built-in roles keep their name, which the application refers to.
 */
class UpdateRoleRequest extends StoreRoleRequest
{
    public function authorize(): bool
    {
        return parent::authorize() && $this->role()->name !== RoleName::Admin->value;
    }

    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        if (RoleName::tryFrom($this->role()->name) !== null) {
            $this->merge(['name' => $this->role()->name]);
        }
    }

    public function role(): Role
    {
        return $this->route('role');
    }

    protected function ignoredRoleId(): ?int
    {
        return $this->role()->id;
    }
}
