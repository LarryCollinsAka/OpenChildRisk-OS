<?php

namespace Database\Seeders;

use App\Models\HazardCategory;
use App\Models\HazardType;
use Illuminate\Database\Seeder;

/**
 * Hazard Taxonomy Seeder
 * 
 * Seeds the complete hazard classification system for OpenChildRisk OS.
 * Covers all major humanitarian hazard categories relevant to child vulnerability:
 * - Climate/Weather hazards (floods, droughts, storms)
 * - Disease outbreaks (cholera, malaria, measles, yellow fever)
 * - Conflict and violence
 * - Infrastructure failures
 * - Nutrition crises
 * 
 * This taxonomy is region-agnostic and supports global deployment.
 */
class HazardTaxonomySeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding Hazard Taxonomy...');
        $this->command->newLine();

        // ====================================================================
        // HAZARD CATEGORIES
        // ====================================================================
        $categories = [
            [
                'code' => 'CLIMATE',
                'name' => 'Climate & Weather',
                'description' => 'Climate-related and meteorological hazards including floods, droughts, storms',
                'icon' => 'cloud-rain',
                'color' => '#3b82f6', // blue
            ],
            [
                'code' => 'DISEASE',
                'name' => 'Disease Outbreaks',
                'description' => 'Infectious disease outbreaks and epidemics affecting child health',
                'icon' => 'activity',
                'color' => '#ef4444', // red
            ],
            [
                'code' => 'CONFLICT',
                'name' => 'Conflict & Violence',
                'description' => 'Armed conflict, civil unrest, and violence affecting civilian populations',
                'icon' => 'alert-triangle',
                'color' => '#f59e0b', // orange
            ],
            [
                'code' => 'INFRASTRUCTURE',
                'name' => 'Infrastructure Failure',
                'description' => 'Failures in critical infrastructure (water, sanitation, electricity, roads)',
                'icon' => 'alert-circle',
                'color' => '#6366f1', // indigo
            ],
            [
                'code' => 'NUTRITION',
                'name' => 'Nutrition Crisis',
                'description' => 'Food insecurity, famine, and acute malnutrition events',
                'icon' => 'alert-octagon',
                'color' => '#8b5cf6', // purple
            ],
        ];

        foreach ($categories as $categoryData) {
            $category = HazardCategory::updateOrCreate(
                ['code' => $categoryData['code']],
                $categoryData
            );

            $this->command->info("✓ Category: {$category->name}");
        }

        $this->command->newLine();

        // ====================================================================
        // HAZARD TYPES
        // ====================================================================

        // Get categories for relationships
        $climate = HazardCategory::where('code', 'CLIMATE')->first();
        $disease = HazardCategory::where('code', 'DISEASE')->first();
        $conflict = HazardCategory::where('code', 'CONFLICT')->first();
        $infrastructure = HazardCategory::where('code', 'INFRASTRUCTURE')->first();
        $nutrition = HazardCategory::where('code', 'NUTRITION')->first();

        $hazardTypes = [
            // ================================================================
            // CLIMATE & WEATHER HAZARDS
            // ================================================================
            [
                'category_id' => $climate->id,
                'category' => 'climate', // ← LOWERCASE (constraint requirement)
                'code' => 'FLOOD',
                'name' => 'Flooding',
                'description' => 'Overflow of water onto normally dry land, often from heavy rainfall or river overflow',
                'default_severity' => 7,
                'typical_time_window_days' => 7,
            ],
            [
                'category_id' => $climate->id,
                'category' => 'climate', // ← LOWERCASE
                'code' => 'DROUGHT',
                'name' => 'Drought',
                'description' => 'Prolonged period of abnormally low rainfall leading to water shortage',
                'default_severity' => 6,
                'typical_time_window_days' => 90,
            ],
            [
                'category_id' => $climate->id,
                'category' => 'climate', // ← LOWERCASE
                'code' => 'STORM',
                'name' => 'Severe Storm',
                'description' => 'Extreme weather event with strong winds, heavy rain, and potential structural damage',
                'default_severity' => 7,
                'typical_time_window_days' => 3,
            ],
            [
                'category_id' => $climate->id,
                'category' => 'climate', // ← LOWERCASE
                'code' => 'HEATWAVE',
                'name' => 'Extreme Heat',
                'description' => 'Prolonged period of excessively hot weather threatening health',
                'default_severity' => 6,
                'typical_time_window_days' => 10,
            ],

            // ================================================================
            // DISEASE OUTBREAKS
            // ================================================================
            [
                'category_id' => $disease->id,
                'category' => 'disease', // ← LOWERCASE
                'code' => 'CHOLERA',
                'name' => 'Cholera Outbreak',
                'description' => 'Acute diarrheal disease caused by contaminated water, often following floods',
                'default_severity' => 8,
                'typical_time_window_days' => 21,
            ],
            [
                'category_id' => $disease->id,
                'category' => 'disease', // ← LOWERCASE
                'code' => 'MALARIA',
                'name' => 'Malaria Outbreak',
                'description' => 'Mosquito-borne disease causing fever and potentially death in children',
                'default_severity' => 7,
                'typical_time_window_days' => 30,
            ],
            [
                'category_id' => $disease->id,
                'category' => 'disease', // ← LOWERCASE
                'code' => 'MEASLES',
                'name' => 'Measles Outbreak',
                'description' => 'Highly contagious viral disease, preventable by vaccination',
                'default_severity' => 8,
                'typical_time_window_days' => 21,
            ],
            [
                'category_id' => $disease->id,
                'category' => 'disease', // ← LOWERCASE
                'code' => 'YELLOW_FEVER',
                'name' => 'Yellow Fever',
                'description' => 'Mosquito-borne viral disease common in tropical regions',
                'default_severity' => 8,
                'typical_time_window_days' => 14,
            ],
            [
                'category_id' => $disease->id,
                'category' => 'disease', // ← LOWERCASE
                'code' => 'MENINGITIS',
                'name' => 'Meningitis Outbreak',
                'description' => 'Bacterial infection of brain/spinal cord membranes, common in Sahel during dry season',
                'default_severity' => 9,
                'typical_time_window_days' => 14,
            ],
            [
                'category_id' => $disease->id,
                'category' => 'disease', // ← LOWERCASE
                'code' => 'TYPHOID',
                'name' => 'Typhoid Fever',
                'description' => 'Bacterial infection from contaminated food/water',
                'default_severity' => 7,
                'typical_time_window_days' => 21,
            ],

            // ================================================================
            // CONFLICT & VIOLENCE
            // ================================================================
            [
                'category_id' => $conflict->id,
                'category' => 'conflict', // ← LOWERCASE
                'code' => 'ARMED_CONFLICT',
                'name' => 'Armed Conflict',
                'description' => 'Violent confrontation between armed groups affecting civilian safety',
                'default_severity' => 9,
                'typical_time_window_days' => 30,
            ],
            [
                'category_id' => $conflict->id,
                'category' => 'conflict', // ← LOWERCASE
                'code' => 'DISPLACEMENT',
                'name' => 'Population Displacement',
                'description' => 'Forced movement of populations due to conflict or disaster',
                'default_severity' => 8,
                'typical_time_window_days' => 60,
            ],
            [
                'category_id' => $conflict->id,
                'category' => 'conflict', // ← LOWERCASE
                'code' => 'CIVIL_UNREST',
                'name' => 'Civil Unrest',
                'description' => 'Protests, riots, or social disturbances affecting service delivery',
                'default_severity' => 6,
                'typical_time_window_days' => 7,
            ],

            // ================================================================
            // INFRASTRUCTURE FAILURES
            // ================================================================
            // Note: 'infrastructure' not in constraint, using 'other'
            [
                'category_id' => $infrastructure->id,
                'category' => 'other', // ← 'infrastructure' not in constraint, use 'other'
                'code' => 'WASH_FAILURE',
                'name' => 'WASH System Failure',
                'description' => 'Breakdown of water, sanitation, and hygiene infrastructure',
                'default_severity' => 7,
                'typical_time_window_days' => 14,
            ],
            [
                'category_id' => $infrastructure->id,
                'category' => 'other', // ← Use 'other'
                'code' => 'HEALTH_FACILITY_CLOSURE',
                'name' => 'Health Facility Closure',
                'description' => 'Temporary or permanent closure of health facilities reducing care access',
                'default_severity' => 7,
                'typical_time_window_days' => 30,
            ],
            [
                'category_id' => $infrastructure->id,
                'category' => 'other', // ← Use 'other'
                'code' => 'ROAD_INACCESSIBILITY',
                'name' => 'Road Inaccessibility',
                'description' => 'Roads blocked or impassable, preventing aid delivery',
                'default_severity' => 6,
                'typical_time_window_days' => 14,
            ],

            // ================================================================
            // NUTRITION CRISES
            // ================================================================
            [
                'category_id' => $nutrition->id,
                'category' => 'nutrition', // ← LOWERCASE
                'code' => 'ACUTE_MALNUTRITION',
                'name' => 'Acute Malnutrition Crisis',
                'description' => 'Sudden increase in severe acute malnutrition rates',
                'default_severity' => 9,
                'typical_time_window_days' => 60,
            ],
            [
                'category_id' => $nutrition->id,
                'category' => 'nutrition', // ← LOWERCASE
                'code' => 'FOOD_INSECURITY',
                'name' => 'Food Insecurity',
                'description' => 'Lack of access to sufficient, safe, and nutritious food',
                'default_severity' => 7,
                'typical_time_window_days' => 90,
            ],
        ];
        
        foreach ($hazardTypes as $typeData) {
            $type = HazardType::updateOrCreate(
                ['code' => $typeData['code']],
                $typeData
            );

            $this->command->info("  ✓ {$type->name} ({$type->code})");
        }

        $this->command->newLine();
        $this->command->info('═══════════════════════════════════════');
        $this->command->info('Hazard Taxonomy Seeded!');
        $this->command->info('═══════════════════════════════════════');
        $this->command->line('Categories: ' . HazardCategory::count());
        $this->command->line('Hazard Types: ' . HazardType::count());
        $this->command->newLine();
    }
}