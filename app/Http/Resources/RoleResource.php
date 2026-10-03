<?php

namespace App\Http\Resources;

use App\Enums\Role as RoleName;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\Permission\Models\Role;

/**
 * A role with its permissions. Built-in roles are named in the current language;
 * roles created in the control panel keep the name they were given.
 *
 * @mixin Role
 */
class RoleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $builtIn = RoleName::tryFrom($this->name);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'label' => $builtIn?->label() ?? $this->name,
            'is_admin' => $builtIn === RoleName::Admin,
            'is_built_in' => $builtIn !== null,
            'permissions' => $this->whenLoaded('permissions', fn (): array => $this->permissions->pluck('name')->values()->all()),
            'users_count' => $this->whenCounted('users'),
        ];
    }
}
