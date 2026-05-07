<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HazardTypeSeeder extends Seeder
{
    public function run(): void
    {
        // Get category IDs
        $disease = DB::table('hazard_categories')->where('code', 'disease')->first();
        $climate = DB::table('hazard_categories')->where('code', 'climate')->first();
        $conflict = DB::table('hazard_categories')->where('code', 'conflict')->first();
        $nutrition = DB::table('hazard_categories')->where('code', 'nutrition')->first();

        if (!$disease || !$climate || !$conflict || !$nutrition) {
            $this->command->error('Hazard categories not found. Run HazardCategorySeeder first.');
            return;
        }

        $hazards = [
            // DISEASE HAZARDS
            [
                'id' => Str::uuid()->toString(),
                'category_id' => $disease->id,
                'name' => 'Cholera',
                'code' => 'cholera',
                'description' => 'Waterborne bacterial infection causing severe diarrhea',
                'parent_id' => null,
                'category' => 'disease',
                'risk_engine' => 'cholera',
                'default_severity' => 8,
                'typical_time_window_days' => 5,
            ],
            [
                'id' => Str::uuid()->toString(),
                'category_id' => $disease->id,
                'name' => 'Malaria',
                'code' => 'malaria',
                'description' => 'Mosquito-borne parasitic infection',
                'parent_id' => null,
                'category' => 'disease',
                'risk_engine' => 'malaria',
                'default_severity' => 7,
                'typical_time_window_days' => 7,
            ],
            [
                'id' => Str::uuid()->toString(),
                'category_id' => $disease->id,
                'name' => 'Measles',
                'code' => 'measles',
                'description' => 'Highly contagious viral infection',
                'parent_id' => null,
                'category' => 'disease',
                'risk_engine' => 'measles',
                'default_severity' => 7,
                'typical_time_window_days' => 10,
            ],
            [
                'id' => Str::uuid()->toString(),
                'category_id' => $disease->id,
                'name' => 'Diarrheal Diseases',
                'code' => 'diarrhea',
                'description' => 'Various causes of acute diarrhea in children',
                'parent_id' => null,
                'category' => 'disease',
                'risk_engine' => 'diarrhea',
                'default_severity' => 6,
                'typical_time_window_days' => 3,
            ],

            // CLIMATE HAZARDS
            [
                'id' => Str::uuid()->toString(),
                'category_id' => $climate->id,
                'name' => 'Flood',
                'code' => 'flood',
                'description' => 'Heavy rainfall causing flooding',
                'parent_id' => null,
                'category' => 'climate',
                'risk_engine' => 'flood',
                'default_severity' => 7,
                'typical_time_window_days' => 3,
            ],
            [
                'id' => Str::uuid()->toString(),
                'category_id' => $climate->id,
                'name' => 'Drought',
                'code' => 'drought',
                'description' => 'Prolonged lack of rainfall',
                'parent_id' => null,
                'category' => 'climate',
                'risk_engine' => 'drought',
                'default_severity' => 6,
                'typical_time_window_days' => 30,
            ],
            [
                'id' => Str::uuid()->toString(),
                'category_id' => $climate->id,
                'name' => 'Extreme Heat',
                'code' => 'heat',
                'description' => 'Dangerous high temperatures',
                'parent_id' => null,
                'category' => 'climate',
                'risk_engine' => 'heat',
                'default_severity' => 6,
                'typical_time_window_days' => 5,
            ],

            // CONFLICT HAZARDS
            [
                'id' => Str::uuid()->toString(),
                'category_id' => $conflict->id,
                'name' => 'Armed Conflict',
                'code' => 'armed_conflict',
                'description' => 'Active fighting affecting civilians',
                'parent_id' => null,
                'category' => 'conflict',
                'risk_engine' => 'conflict',
                'default_severity' => 9,
                'typical_time_window_days' => 1,
            ],
            [
                'id' => Str::uuid()->toString(),
                'category_id' => $conflict->id,
                'name' => 'Displacement',
                'code' => 'displacement',
                'description' => 'Population forced to flee homes',
                'parent_id' => null,
                'category' => 'conflict',
                'risk_engine' => 'displacement',
                'default_severity' => 8,
                'typical_time_window_days' => 3,
            ],

            // NUTRITION HAZARDS
            [
                'id' => Str::uuid()->toString(),
                'category_id' => $nutrition->id,
                'name' => 'Severe Acute Malnutrition',
                'code' => 'sam',
                'description' => 'Severe wasting in children requiring urgent treatment',
                'parent_id' => null,
                'category' => 'nutrition',
                'risk_engine' => 'malnutrition',
                'default_severity' => 9,
                'typical_time_window_days' => 7,
            ],
            [
                'id' => Str::uuid()->toString(),
                'category_id' => $nutrition->id,
                'name' => 'Food Insecurity',
                'code' => 'food_insecurity',
                'description' => 'Lack of access to adequate food',
                'parent_id' => null,
                'category' => 'nutrition',
                'risk_engine' => 'food_security',
                'default_severity' => 7,
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

        $this->command->info('✔ Seeded ' . count($hazards) . ' hazard types across categories');
    }
}