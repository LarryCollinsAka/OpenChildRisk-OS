<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Database Seeder
 * 
 * Master seeder that orchestrates all database seeding in the correct order.
 * 
 * Seeding Order Logic:
 * 1. Foundation data (Languages, Geography, Organizations)
 * 2. Taxonomy systems (Hazards, Workers, Population Groups, Indicators)
 * 3. Security (Roles, Permissions, Users)
 * 4. Operational data (Districts, Data Sources)
 * 
 * This order ensures foreign key constraints are satisfied.
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting Database Seeding...');
        $this->command->newLine();

        $this->call([
            // ================================================================
            // PHASE 1: FOUNDATION DATA (Must run first)
            // ================================================================
            // These have no dependencies
            LanguageSeeder::class,              // Languages for translations
            GeographySeeder::class,             // Countries, States, Cities
            OrganizationSeeder::class, 
            

            // ================================================================
            // PHASE 2: TAXONOMY SYSTEMS
            // ================================================================
            // Classification systems that other data depends on
            HazardTaxonomySeeder::class,        // Hazard categories + types (COMBINED)
            WorkerTypeSeeder::class,            // Field worker classifications
            PopulationGroupSeeder::class,       // Vulnerability groups
            IndicatorSeeder::class,             // Risk indicators (36 indicators)

            // ================================================================
            // PHASE 3: SECURITY & ACCESS CONTROL
            // ================================================================
            // Users need roles/permissions to exist
            RolePermissionSeeder::class,        // Roles and permissions
            UserSeeder::class,                  // Admin users

            // ================================================================
            // PHASE 4: OPERATIONAL DATA
            // ================================================================
            // Real operational data for specific regions
            DataSourceSeeder::class,            // CHIRPS, DHIS2, ACLED, etc.
            FarNorthCameroonDistrictsSeeder::class,  // Far North districts
            BertouaDistrictsSeeder::class,      // East Region districts (if exists)
            DistrictTranslationsSeeder::class,  // District translations
        ]);

        $this->command->newLine();
        $this->command->info('✅ Database Seeding Complete!');
        $this->command->newLine();
    }
}