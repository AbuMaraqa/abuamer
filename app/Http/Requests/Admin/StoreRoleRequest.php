<?php

namespace App\Http\Requests\Admin;

use App\Enums\Permission;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can(Permission::UsersManage->value);
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['name' => trim((string) $this->input('name', ''))]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:60', Rule::unique('roles', 'name')->ignore($this->ignoredRoleId())],
            'permissions' => ['array'],
            'permissions.*' => ['string', 'distinct', Rule::in(array_map(fn (Permission $permission): string => $permission->value, Permission::assignable()))],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => __('Role name'),
            'permissions.*' => __('Permission'),
        ];
    }

    /**
     * The chosen permissions, with the ones they depend on (adding products needs the product list).
     *
     * @return list<string>
     */
    public function permissionNames(): array
    {
        $permissions = array_map(fn (string $name): Permission => Permission::from($name), $this->validated('permissions', []));

        foreach ($permissions as $permission) {
            if ($permission->requires() !== null) {
                $permissions[] = $permission->requires();
            }
        }

        return array_values(array_unique(array_map(fn (Permission $permission): string => $permission->value, $permissions)));
    }

    protected function ignoredRoleId(): ?int
    {
        return null;
    }
}
