<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PopulationGroupSeeder extends Seeder
{
    /**
     * Seed population groups.
     * 
     * Based on UNICEF operational frameworks and vulnerability categories.
     */
    public function run(): void
    {
        $groups = [
            // AGE-BASED GROUPS
            [
                'id' => Str::uuid()->toString(),
                'code' => 'under_5',
                'name' => 'Children Under 5',
                'description' => 'Children aged 0-59 months',
                'group_type' => 'age',
                'min_age_months' => 0,
                'max_age_months' => 59,
                'vulnerability_weight' => 1.50, // Higher vulnerability
                'active' => true,
                'display_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'code' => 'adolescent',
                'name' => 'Adolescents',
                'description' => 'Children aged 10-19 years',
                'group_type' => 'age',
                'min_age_months' => 120, // 10 years
                'max_age_months' => 228, // 19 years
                'vulnerability_weight' => 1.00,
                'active' => true,
                'display_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'code' => 'school_age',
                'name' => 'School-Age Children',
                'description' => 'Children aged 5-17 years',
                'group_type' => 'age',
                'min_age_months' => 60,  // 5 years
                'max_age_months' => 204, // 17 years
                'vulnerability_weight' => 1.00,
                'active' => true,
                'display_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // DISABILITY
            [
                'id' => Str::uuid()->toString(),
                'code' => 'children_with_disabilities',
                'name' => 'Children with Disabilities',
                'description' => 'Children with physical, sensory, cognitive, or developmental disabilities',
                'group_type' => 'disability',
                'min_age_months' => null,
                'max_age_months' => null,
                'vulnerability_weight' => 1.80, // Very high vulnerability
                'active' => true,
                'display_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // DISPLACEMENT
            [
                'id' => Str::uuid()->toString(),
                'code' => 'displaced_children',
                'name' => 'Displaced Children',
                'description' => 'Internally displaced children (IDPs)',
                'group_type' => 'displacement',
                'min_age_months' => null,
                'max_age_months' => null,
                'vulnerability_weight' => 1.60,
                'active' => true,
                'display_order' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'code' => 'refugee_children',
                'name' => 'Refugee Children',
                'description' => 'Children who are refugees from other countries',
                'group_type' => 'displacement',
                'min_age_months' => null,
                'max_age_months' => null,
                'vulnerability_weight' => 1.70,
                'active' => true,
                'display_order' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // NUTRITION
            [
                'id' => Str::uuid()->toString(),
                'code' => 'sam_children',
                'name' => 'Children with SAM',
                'description' => 'Severe Acute Malnutrition (wasting)',
                'group_type' => 'nutrition',
                'min_age_months' => 6,
                'max_age_months' => 59,
                'vulnerability_weight' => 2.00, // Maximum vulnerability
                'active' => true,
                'display_order' => 7,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'code' => 'stunted_children',
                'name' => 'Stunted Children',
                'description' => 'Children with chronic malnutrition (stunting)',
                'group_type' => 'nutrition',
                'min_age_months' => 0,
                'max_age_months' => 59,
                'vulnerability_weight' => 1.40,
                'active' => true,
                'display_order' => 8,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // VACCINATION
            [
                'id' => Str::uuid()->toString(),
                'code' => 'zero_dose_children',
                'name' => 'Zero-Dose Children',
                'description' => 'Children who have not received any vaccinations',
                'group_type' => 'vaccination',
                'min_age_months' => 0,
                'max_age_months' => 23,
                'vulnerability_weight' => 1.50,
                'active' => true,
                'display_order' => 9,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'code' => 'under_immunized',
                'name' => 'Under-Immunized Children',
                'description' => 'Children who have not completed full immunization schedule',
                'group_type' => 'vaccination',
                'min_age_months' => 0,
                'max_age_months' => 59,
                'vulnerability_weight' => 1.30,
                'active' => true,
                'display_order' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // GENDER
            [
                'id' => Str::uuid()->toString(),
                'code' => 'adolescent_girls',
                'name' => 'Adolescent Girls',
                'description' => 'Girls aged 10-19 years',
                'group_type' => 'gender',
                'min_age_months' => 120,
                'max_age_months' => 228,
                'vulnerability_weight' => 1.20,
                'active' => true,
                'display_order' => 11,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // TOTAL (for reference)
            [
                'id' => Str::uuid()->toString(),
                'code' => 'total_children',
                'name' => 'Total Children',
                'description' => 'All children aged 0-17 years',
                'group_type' => 'age',
                'min_age_months' => 0,
                'max_age_months' => 204,
                'vulnerability_weight' => 1.00,
                'active' => true,
                'display_order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('population_groups')->insert($groups);

        $this->command->info('✔ Seeded ' . count($groups) . ' population groups');
    }
}