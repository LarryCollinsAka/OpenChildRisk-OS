<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WorkerTypeSeeder extends Seeder
{
    /**
     * Seed field worker types.
     * 
     * Different worker types have different capabilities and response areas.
     * This enables smart alert routing and capacity planning.
     */
    public function run(): void
    {
        $workerTypes = [
            [
                'id' => Str::uuid()->toString(),
                'code' => 'chw',
                'name' => 'Community Health Worker',
                'description' => 'Frontline community-based health worker providing basic health services',
                'specialization_area' => 'health',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'code' => 'nurse',
                'name' => 'Nurse / Clinical Officer',
                'description' => 'Licensed nurse or clinical officer at health facility',
                'specialization_area' => 'health',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'code' => 'wash_technician',
                'name' => 'WASH Technician',
                'description' => 'Water, sanitation, and hygiene specialist',
                'specialization_area' => 'wash',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'code' => 'nutrition_worker',
                'name' => 'Nutrition Worker',
                'description' => 'Nutrition specialist managing malnutrition cases',
                'specialization_area' => 'nutrition',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'code' => 'vaccination_officer',
                'name' => 'Vaccination Officer',
                'description' => 'EPI officer managing vaccination campaigns',
                'specialization_area' => 'health',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'code' => 'ngo_coordinator',
                'name' => 'NGO Field Coordinator',
                'description' => 'Field coordinator managing multi-sectoral programs',
                'specialization_area' => 'coordination',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'code' => 'district_officer',
                'name' => 'District Health Officer',
                'description' => 'District-level health system manager',
                'specialization_area' => 'health',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'code' => 'social_worker',
                'name' => 'Social Worker',
                'description' => 'Child protection and psychosocial support specialist',
                'specialization_area' => 'protection',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('worker_types')->insert($workerTypes);

        $this->command->info('✔ Seeded ' . count($workerTypes) . ' worker types');
    }
}