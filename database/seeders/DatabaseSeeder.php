<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database for local development.
     *
     * Model events stay enabled: translations and media are persisted by model event listeners.
     */
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        User::factory()->create([
            'name' => 'Nasaq Admin',
            'email' => 'admin@nasaq.test',
        ])->assignRole(Role::Admin);

        $this->call([CompanySeeder::class, CatalogSeeder::class]);
    }
}
