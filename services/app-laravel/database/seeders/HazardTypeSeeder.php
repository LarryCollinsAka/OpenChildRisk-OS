<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HazardTypeSeeder extends Seeder
{
    /**
     * Seed hazard types for child risk assessment.
     * 
     * Categories (max 2 levels):
     * - Disease (cholera, malaria, measles)
     * - Climate (flood, drought, heat)
     * - Conflict (armed conflict, displacement)
     * - Nutrition (malnutrition)
     * - WASH (water contamination)
     */
    public function run(): void
    {
        $hazards = [
            // DISEASE CATEGORY
            [
                'id' => $diseaseId = Str::uuid()->toString(),
                'name' => 'Disease',
                'code' => 'disease',
                'description' => 'Infectious diseases affecting children',
                'parent_id' => null,
                'category' => 'disease',
                'risk_engine' => null,
                'default_severity' => 7,
                'typical_time_window_days' => 7,
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Cholera',
                'code' => 'cholera',
                'description' => 'Waterborne bacterial infection causing severe diarrhea',
                'parent_id' => $diseaseId,
                'category' => 'disease',
                'risk_engine' => 'cholera',
                'default_severity' => 8,
                'typical_time_window_days' => 5,
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Malaria',
                'code' => 'malaria',
                'description' => 'Mosquito-borne parasitic infection',
                'parent_id' => $diseaseId,
                'category' => 'disease',
                'risk_engine' => 'malaria',
                'default_severity' => 7,
                'typical_time_window_days' => 7,
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Measles',
                'code' => 'measles',
                'description' => 'Highly contagious viral infection',
                'parent_id' => $diseaseId,
                'category' => 'disease',
                'risk_engine' => 'measles',
                'default_severity' => 7,
                'typical_time_window_days' => 10,
            ],

            // CLIMATE CATEGORY
            [
                'id' => $climateId = Str::uuid()->toString(),
                'name' => 'Climate',
                'code' => 'climate',
                'description' => 'Climate-related hazards',
                'parent_id' => null,
                'category' => 'climate',
                'risk_engine' => null,
                'default_severity' => 6,
                'typical_time_window_days' => 14,
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Flood',
                'code' => 'flood',
                'description' => 'Heavy rainfall causing flooding',
                'parent_id' => $climateId,
                'category' => 'climate',
                'risk_engine' => 'flood',
                'default_severity' => 7,
                'typical_time_window_days' => 3,
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Drought',
                'code' => 'drought',
                'description' => 'Prolonged lack of rainfall',
                'parent_id' => $climateId,
                'category' => 'climate',
                'risk_engine' => 'drought',
                'default_severity' => 6,
                'typical_time_window_days' => 30,
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Extreme Heat',
                'code' => 'heat',
                'description' => 'Dangerous high temperatures',
                'parent_id' => $climateId,
                'category' => 'climate',
                'risk_engine' => 'heat',
                'default_severity' => 6,
                'typical_time_window_days' => 5,
            ],

            // CONFLICT CATEGORY
            [
                'id' => $conflictId = Str::uuid()->toString(),
                'name' => 'Conflict',
                'code' => 'conflict',
                'description' => 'Armed conflict and displacement',
                'parent_id' => null,
                'category' => 'conflict',
                'risk_engine' => null,
                'default_severity' => 9,
                'typical_time_window_days' => 1,
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Armed Conflict',
                'code' => 'armed_conflict',
                'description' => 'Active fighting affecting civilians',
                'parent_id' => $conflictId,
                'category' => 'conflict',
                'risk_engine' => 'conflict',
                'default_severity' => 9,
                'typical_time_window_days' => 1,
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Displacement',
                'code' => 'displacement',
                'description' => 'Population forced to flee homes',
                'parent_id' => $conflictId,
                'category' => 'conflict',
                'risk_engine' => 'displacement',
                'default_severity' => 8,
                'typical_time_window_days' => 3,
            ],

            // NUTRITION CATEGORY
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Malnutrition',
                'code' => 'malnutrition',
                'description' => 'Severe acute malnutrition in children',
                'parent_id' => null,
                'category' => 'nutrition',
                'risk_engine' => 'malnutrition',
                'default_severity' => 8,
                'typical_time_window_days' => 14,
            ],
        ];

        foreach ($hazards as $hazard) {
            DB::table('hazard_types')->insert([
                ...$hazard,
                'active' => true,
                'metadata' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('✔ Seeded ' . count($hazards) . ' hazard types');
    }
}