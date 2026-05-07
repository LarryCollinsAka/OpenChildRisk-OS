<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * 
     * Order matters:
     * 1. Languages (required by translations)
     * 2. Geography (countries → states → cities)
     * 3. Organizations (UNICEF)
     * 4. Hazard types (cholera, malaria, etc.)
     * 5. Roles & Permissions
     * 6. Users (with roles)
     */
    public function run(): void
    {
        $this->call([
            LanguageSeeder::class,
            GeographySeeder::class,
            OrganizationSeeder::class,
            HazardCategorySeeder::class, 
            HazardTypeSeeder::class,
            RolePermissionSeeder::class,
            UserSeeder::class,
        ]);
    }
}