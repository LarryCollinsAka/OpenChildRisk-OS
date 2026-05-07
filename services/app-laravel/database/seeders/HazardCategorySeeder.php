<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HazardCategorySeeder extends Seeder
{
    /**
     * Seed hazard categories.
     * 
     * Categories group related hazard types for better organization.
     * Based on UNICEF operational frameworks and humanitarian standards.
     */
    public function run(): void
    {
        $categories = [
            [
                'id' => Str::uuid()->toString(),
                'code' => 'disease',
                'name' => 'Disease & Health',
                'description' => 'Infectious diseases and health emergencies affecting children',
                'icon' => 'fa-virus',
                'color' => '#dc2626', // Red
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'code' => 'climate',
                'name' => 'Climate & Weather',
                'description' => 'Climate-related hazards including floods, droughts, and extreme weather',
                'icon' => 'fa-cloud-rain',
                'color' => '#2563eb', // Blue
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'code' => 'conflict',
                'name' => 'Conflict & Displacement',
                'description' => 'Armed conflict, violence, and forced displacement',
                'icon' => 'fa-shield-halved',
                'color' => '#ea580c', // Orange
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'code' => 'infrastructure',
                'name' => 'Infrastructure & Services',
                'description' => 'Disruption of critical infrastructure and services',
                'icon' => 'fa-building',
                'color' => '#7c3aed', // Purple
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'code' => 'nutrition',
                'name' => 'Nutrition & Food Security',
                'description' => 'Malnutrition, food insecurity, and related emergencies',
                'icon' => 'fa-wheat-awn',
                'color' => '#16a34a', // Green
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('hazard_categories')->insert($categories);

        $this->command->info('✔ Seeded ' . count($categories) . ' hazard categories');
    }
}