<?php

namespace App\Http\Requests\Admin;

use App\Enums\Permission;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class StoreUserRequest extends FormRequest
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
        $this->merge([
            'name' => trim((string) $this->input('name', '')),
            'email' => mb_strtolower(trim((string) $this->input('email', ''))),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->ignoredUserId())],
            'password' => [$this->passwordRequired() ? 'required' : 'nullable', 'string', 'confirmed', Password::min(8)->letters()->numbers()],
            'role_id' => ['required', 'integer', Rule::exists('roles', 'id')],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => __('Name'),
            'email' => __('Email'),
            'password' => __('Password'),
            'role_id' => __('Role'),
        ];
    }

    /**
     * The account's columns; the password only when one was typed.
     *
     * @return array<string, mixed>
     */
    public function userAttributes(): array
    {
        return array_filter([
            'name' => $this->validated('name'),
            'email' => $this->validated('email'),
            'is_active' => $this->boolean('is_active'),
            'password' => $this->validated('password'),
        ], fn (mixed $value): bool => $value !== null);
    }

    public function role(): Role
    {
        return Role::findById($this->integer('role_id'));
    }

    protected function passwordRequired(): bool
    {
        return true;
    }

    protected function ignoredUserId(): ?int
    {
        return null;
    }
}
