<?php

namespace App\Http\Requests\Admin;

use App\Enums\Role as RoleName;
use App\Models\User;
use Illuminate\Validation\Validator;

/**
 * The password is changed only when a new one is typed. Administrators cannot lock
 * themselves out, and the last active administrator cannot lose that role.
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
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $account = $this->account();
                $staysAdministrator = $this->role()->name === RoleName::Admin->value;
                $staysActive = $this->boolean('is_active');

                if ($account->is($this->user())) {
                    if (! $staysActive) {
                        $validator->errors()->add('is_active', __('You cannot deactivate your own account.'));
                    }

                    if ($account->isAdministrator() && ! $staysAdministrator) {
                        $validator->errors()->add('role_id', __('You cannot remove your own administrator role.'));
                    }

                    return;
                }

                $isLastAdministrator = $account->is_active && $account->isAdministrator()
                    && User::query()->active()->role(RoleName::Admin)->whereKeyNot($account->id)->doesntExist();

                if ($isLastAdministrator && (! $staysAdministrator || ! $staysActive)) {
                    $validator->errors()->add('role_id', __('This is the only active administrator. Make someone else an administrator first.'));
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
