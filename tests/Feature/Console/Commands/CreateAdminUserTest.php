<?php

use App\Enums\Role;
use App\Models\User;

it('creates a user with the selected role', function () {
    $this->artisan('app:create-admin-user')
        ->expectsQuestion('Name', 'Nasaq Admin')
        ->expectsQuestion('Email', 'admin@nasaq.test')
        ->expectsQuestion('Password', 'secret-password')
        ->expectsQuestion('Role', Role::Editor->value)
        ->assertSuccessful();

    $user = User::firstWhere('email', 'admin@nasaq.test');
    expect($user->name)->toBe('Nasaq Admin')
        ->and($user->hasRole(Role::Editor))->toBeTrue();
});
