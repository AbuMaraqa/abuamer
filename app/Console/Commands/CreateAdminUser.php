<?php

namespace App\Console\Commands;

use App\Enums\Role;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

#[Signature('app:create-admin-user')]
#[Description('Create a control panel user and assign it a role')]
class CreateAdminUser extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->callSilently('db:seed', ['--class' => RolesAndPermissionsSeeder::class, '--force' => true]);

        $name = text(label: 'Name', required: true);

        $email = text(
            label: 'Email',
            required: true,
            validate: fn (string $value): ?string => Validator::make(
                ['email' => $value],
                ['email' => ['email', 'unique:users,email']],
            )->errors()->first('email') ?: null,
        );

        $password = password(
            label: 'Password',
            required: true,
            validate: fn (string $value): ?string => mb_strlen($value) < 8 ? 'The password must be at least 8 characters.' : null,
        );

        $role = select(
            label: 'Role',
            options: array_column(Role::cases(), 'name', 'value'),
            default: Role::Admin->value,
        );

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);

        $user->assignRole($role);

        $this->components->info("User [{$user->email}] created with the [{$role}] role.");

        return self::SUCCESS;
    }
}
