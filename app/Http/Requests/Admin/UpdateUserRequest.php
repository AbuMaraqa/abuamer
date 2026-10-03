<?php

namespace App\Http\Requests\Admin;

use App\Enums\Role as RoleName;
use App\Models\User;
use Illuminate\Validation\Validator;

/**
 * The password is changed only when a new one is typed. Administrators cannot
 * deactivate themselves or give up their own role, so there is always at least one
 * active administrator: the one making the change.
 */
class UpdateUserRequest extends StoreUserRequest
{
    public function account(): User
    {
        return $this->route('user');
    }

    /**
     * @return list<callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty() || ! $this->account()->is($this->user())) {
                    return;
                }

                if (! $this->boolean('is_active')) {
                    $validator->errors()->add('is_active', __('You cannot deactivate your own account.'));
                }

                if ($this->role()->name !== RoleName::Admin->value) {
                    $validator->errors()->add('role_id', __('You cannot remove your own administrator role.'));
                }
            },
        ];
    }

    protected function passwordRequired(): bool
    {
        return false;
    }

    protected function ignoredUserId(): ?int
    {
        return $this->account()->id;
    }
}
