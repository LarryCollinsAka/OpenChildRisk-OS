<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class IndicatorSeeder extends Seeder
{
    /**
     * Seed indicators.
     * 
     * Based on UNICEF frameworks, WHO indicators, and humanitarian standards.
     * 50+ indicators across 9 categories.
     */
    public function run(): void
    {
        // Get population group IDs for linking
        $under5 = DB::table('population_groups')->where('code', 'under_5')->value('id');
        $sam = DB::table('population_groups')->where('code', 'sam_children')->value('id');
        $zeroDose = DB::table('population_groups')->where('code', 'zero_dose_children')->value('id');
        $schoolAge = DB::table('population_groups')->where('code', 'school_age')->value('id');
        
        $indicators = [
            // ====================
            // HEALTH INDICATORS (7)
            // ====================
            
            // Vaccination
            [
                'code' => 'vaccination_coverage_dpt3',
                'name' => 'DPT3 Vaccination Coverage',
                'description' => 'Percentage of children who received 3 doses of DPT vaccine',
                'category' => 'health',
                'subcategory' => 'vaccination',
                'data_type' => 'percentage',
                'unit' => '%',
                'min_value' => 0,
                'max_value' => 100,
                'polarity' => 'positive',
                'critical_threshold' => 50,
                'warning_threshold' => 70,
                'primary_population_group_id' => $under5,
            ],
            [
                'code' => 'vaccination_coverage_measles',
                'name' => 'Measles Vaccination Coverage',
                'description' => 'Percentage of children vaccinated against measles',
                'category' => 'health',
                'subcategory' => 'vaccination',
                'data_type' => 'percentage',
                'unit' => '%',
                'min_value' => 0,
                'max_value' => 100,
                'polarity' => 'positive',
                'critical_threshold' => 60,
                'warning_threshold' => 80,
                'primary_population_group_id' => $under5,
            ],
            [
                'code' => 'zero_dose_children_percentage',
                'name' => 'Zero-Dose Children Rate',
                'description' => 'Percentage of children who have not received any vaccines',
                'category' => 'health',
                'subcategory' => 'vaccination',
                'data_type' => 'percentage',
                'unit' => '%',
                'min_value' => 0,
                'max_value' => 100,
                'polarity' => 'negative',
                'critical_threshold' => 20,
                'warning_threshold' => 10,
                'primary_population_group_id' => $zeroDose,
            ],
            
            // Disease Prevalence
            [
                'code' => 'cholera_incidence_rate',
                'name' => 'Cholera Incidence Rate',
                'description' => 'Number of cholera cases per 1,000 population',
                'category' => 'health',
                'subcategory' => 'disease',
                'data_type' => 'rate',
                'unit' => 'per_1000',
                'min_value' => 0,
                'max_value' => null,
                'polarity' => 'negative',
                'critical_threshold' => 5,
                'warning_threshold' => 2,
                'primary_population_group_id' => null,
            ],
            [
                'code' => 'malaria_prevalence',
                'name' => 'Malaria Prevalence',
                'description' => 'Percentage of population with confirmed malaria',
                'category' => 'health',
                'subcategory' => 'disease',
                'data_type' => 'percentage',
                'unit' => '%',
                'min_value' => 0,
                'max_value' => 100,
                'polarity' => 'negative',
                'critical_threshold' => 30,
                'warning_threshold' => 15,
                'primary_population_group_id' => null,
            ],
            [
                'code' => 'diarrhea_incidence_rate',
                'name' => 'Diarrheal Disease Incidence',
                'description' => 'Cases of diarrheal diseases per 1,000 children',
                'category' => 'health',
                'subcategory' => 'disease',
                'data_type' => 'rate',
                'unit' => 'per_1000',
                'min_value' => 0,
                'max_value' => null,
                'polarity' => 'negative',
                'critical_threshold' => 50,
                'warning_threshold' => 25,
                'primary_population_group_id' => $under5,
            ],
            
            // Health Facility Access
            [
                'code' => 'health_facility_access',
                'name' => 'Health Facility Access',
                'description' => 'Percentage of population within 5km of functional health facility',
                'category' => 'health',
                'subcategory' => 'access',
                'data_type' => 'percentage',
                'unit' => '%',
                'min_value' => 0,
                'max_value' => 100,
                'polarity' => 'positive',
                'critical_threshold' => 50,
                'warning_threshold' => 70,
                'primary_population_group_id' => null,
            ],
            
            // ====================
            // WASH INDICATORS (4)
            // ====================
            
            [
                'code' => 'improved_water_access',
                'name' => 'Improved Water Access',
                'description' => 'Percentage with access to improved water source',
                'category' => 'wash',
                'subcategory' => 'water',
                'data_type' => 'percentage',
                'unit' => '%',
                'min_value' => 0,
                'max_value' => 100,
                'polarity' => 'positive',
                'critical_threshold' => 40,
                'warning_threshold' => 60,
                'primary_population_group_id' => null,
            ],
            [
                'code' => 'improved_sanitation_access',
                'name' => 'Improved Sanitation Access',
                'description' => 'Percentage with access to improved sanitation facilities',
                'category' => 'wash',
                'subcategory' => 'sanitation',
                'data_type' => 'percentage',
                'unit' => '%',
                'min_value' => 0,
                'max_value' => 100,
                'polarity' => 'positive',
                'critical_threshold' => 30,
                'warning_threshold' => 50,
                'primary_population_group_id' => null,
            ],
            [
                'code' => 'open_defecation_rate',
                'name' => 'Open Defecation Rate',
                'description' => 'Percentage of population practicing open defecation',
                'category' => 'wash',
                'subcategory' => 'sanitation',
                'data_type' => 'percentage',
                'unit' => '%',
                'min_value' => 0,
                'max_value' => 100,
                'polarity' => 'negative',
                'critical_threshold' => 40,
                'warning_threshold' => 20,
                'primary_population_group_id' => null,
            ],
            [
                'code' => 'handwashing_facility_access',
                'name' => 'Handwashing Facility Access',
                'description' => 'Percentage with handwashing facility with soap and water',
                'category' => 'wash',
                'subcategory' => 'hygiene',
                'data_type' => 'percentage',
                'unit' => '%',
                'min_value' => 0,
                'max_value' => 100,
                'polarity' => 'positive',
                'critical_threshold' => 30,
                'warning_threshold' => 50,
                'primary_population_group_id' => null,
            ],
            
            // ====================
            // NUTRITION INDICATORS (5)
            // ====================
            
            [
                'code' => 'stunting_prevalence',
                'name' => 'Stunting Prevalence',
                'description' => 'Percentage of children under 5 with stunting (chronic malnutrition)',
                'category' => 'nutrition',
                'subcategory' => 'malnutrition',
                'data_type' => 'percentage',
                'unit' => '%',
                'min_value' => 0,
                'max_value' => 100,
                'polarity' => 'negative',
                'critical_threshold' => 40,
                'warning_threshold' => 30,
                'primary_population_group_id' => $under5,
            ],
            [
                'code' => 'wasting_prevalence',
                'name' => 'Wasting Prevalence',
                'description' => 'Percentage of children under 5 with wasting (acute malnutrition)',
                'category' => 'nutrition',
                'subcategory' => 'malnutrition',
                'data_type' => 'percentage',
                'unit' => '%',
                'min_value' => 0,
                'max_value' => 100,
                'polarity' => 'negative',
                'critical_threshold' => 15,
                'warning_threshold' => 10,
                'primary_population_group_id' => $under5,
            ],
            [
                'code' => 'sam_prevalence',
                'name' => 'SAM Prevalence',
                'description' => 'Severe Acute Malnutrition rate among children under 5',
                'category' => 'nutrition',
                'subcategory' => 'malnutrition',
                'data_type' => 'percentage',
                'unit' => '%',
                'min_value' => 0,
                'max_value' => 100,
                'polarity' => 'negative',
                'critical_threshold' => 5,
                'warning_threshold' => 2,
                'primary_population_group_id' => $sam,
            ],
            [
                'code' => 'food_insecurity_prevalence',
                'name' => 'Food Insecurity Prevalence',
                'description' => 'Percentage of households experiencing food insecurity',
                'category' => 'nutrition',
                'subcategory' => 'food_security',
                'data_type' => 'percentage',
                'unit' => '%',
                'min_value' => 0,
                'max_value' => 100,
                'polarity' => 'negative',
                'critical_threshold' => 50,
                'warning_threshold' => 30,
                'primary_population_group_id' => null,
            ],
            [
                'code' => 'exclusive_breastfeeding_rate',
                'name' => 'Exclusive Breastfeeding Rate',
                'description' => 'Percentage of infants 0-6 months exclusively breastfed',
                'category' => 'nutrition',
                'subcategory' => 'infant_feeding',
                'data_type' => 'percentage',
                'unit' => '%',
                'min_value' => 0,
                'max_value' => 100,
                'polarity' => 'positive',
                'critical_threshold' => 30,
                'warning_threshold' => 50,
                'primary_population_group_id' => $under5,
            ],
            
            // ====================
            // EDUCATION INDICATORS (3)
            // ====================
            
            [
                'code' => 'primary_enrollment_rate',
                'name' => 'Primary School Enrollment',
                'description' => 'Net enrollment rate in primary education',
                'category' => 'education',
                'subcategory' => 'enrollment',
                'data_type' => 'percentage',
                'unit' => '%',
                'min_value' => 0,
                'max_value' => 100,
                'polarity' => 'positive',
                'critical_threshold' => 50,
                'warning_threshold' => 70,
                'primary_population_group_id' => $schoolAge,
            ],
            [
                'code' => 'school_dropout_rate',
                'name' => 'School Dropout Rate',
                'description' => 'Percentage of enrolled children who drop out',
                'category' => 'education',
                'subcategory' => 'retention',
                'data_type' => 'percentage',
                'unit' => '%',
                'min_value' => 0,
                'max_value' => 100,
                'polarity' => 'negative',
                'critical_threshold' => 30,
                'warning_threshold' => 15,
                'primary_population_group_id' => $schoolAge,
            ],
            [
                'code' => 'functional_schools_percentage',
                'name' => 'Functional Schools',
                'description' => 'Percentage of schools that are functional and accessible',
                'category' => 'education',
                'subcategory' => 'infrastructure',
                'data_type' => 'percentage',
                'unit' => '%',
                'min_value' => 0,
                'max_value' => 100,
                'polarity' => 'positive',
                'critical_threshold' => 50,
                'warning_threshold' => 70,
                'primary_population_group_id' => null,
            ],
            
            // ====================
            // CLIMATE INDICATORS (6)
            // ====================
            
            [
                'code' => 'rainfall_anomaly',
                'name' => 'Rainfall Anomaly',
                'description' => 'Deviation from average rainfall (mm)',
                'category' => 'climate',
                'subcategory' => 'precipitation',
                'data_type' => 'measurement',
                'unit' => 'mm',
                'min_value' => null,
                'max_value' => null,
                'polarity' => 'negative',
                'critical_threshold' => 100,
                'warning_threshold' => 50,
                'primary_population_group_id' => null,
            ],
            [
                'code' => 'temperature_anomaly',
                'name' => 'Temperature Anomaly',
                'description' => 'Deviation from average temperature (°C)',
                'category' => 'climate',
                'subcategory' => 'temperature',
                'data_type' => 'measurement',
                'unit' => 'celsius',
                'min_value' => null,
                'max_value' => null,
                'polarity' => 'negative',
                'critical_threshold' => 3,
                'warning_threshold' => 2,
                'primary_population_group_id' => null,
            ],
            [
                'code' => 'drought_severity_index',
                'name' => 'Drought Severity Index',
                'description' => 'Standardized measure of drought conditions',
                'category' => 'climate',
                'subcategory' => 'drought',
                'data_type' => 'index',
                'unit' => 'index',
                'min_value' => -4,
                'max_value' => 4,
                'polarity' => 'negative',
                'critical_threshold' => -2,
                'warning_threshold' => -1,
                'primary_population_group_id' => null,
            ],
            [
                'code' => 'flood_risk_score',
                'name' => 'Flood Risk Score',
                'description' => 'Composite flood vulnerability score',
                'category' => 'climate',
                'subcategory' => 'flood',
                'data_type' => 'index',
                'unit' => 'score',
                'min_value' => 0,
                'max_value' => 10,
                'polarity' => 'negative',
                'critical_threshold' => 7,
                'warning_threshold' => 5,
                'primary_population_group_id' => null,
            ],
            [
                'code' => 'extreme_heat_days',
                'name' => 'Extreme Heat Days',
                'description' => 'Number of days with temperature >40°C',
                'category' => 'climate',
                'subcategory' => 'temperature',
                'data_type' => 'count',
                'unit' => 'days',
                'min_value' => 0,
                'max_value' => null,
                'polarity' => 'negative',
                'critical_threshold' => 30,
                'warning_threshold' => 15,
                'primary_population_group_id' => null,
            ],
            [
                'code' => 'vegetation_health_index',
                'name' => 'Vegetation Health Index',
                'description' => 'Satellite-based vegetation condition index',
                'category' => 'climate',
                'subcategory' => 'vegetation',
                'data_type' => 'index',
                'unit' => 'index',
                'min_value' => 0,
                'max_value' => 1,
                'polarity' => 'positive',
                'critical_threshold' => 0.3,
                'warning_threshold' => 0.5,
                'primary_population_group_id' => null,
            ],
            
            // ====================
            // CONFLICT INDICATORS (4)
            // ====================
            
            [
                'code' => 'conflict_events_count',
                'name' => 'Conflict Events Count',
                'description' => 'Number of armed conflict events in district',
                'category' => 'conflict',
                'subcategory' => 'violence',
                'data_type' => 'count',
                'unit' => 'events',
                'min_value' => 0,
                'max_value' => null,
                'polarity' => 'negative',
                'critical_threshold' => 10,
                'warning_threshold' => 3,
                'primary_population_group_id' => null,
            ],
            [
                'code' => 'displacement_rate',
                'name' => 'Displacement Rate',
                'description' => 'Percentage of population displaced',
                'category' => 'conflict',
                'subcategory' => 'displacement',
                'data_type' => 'percentage',
                'unit' => '%',
                'min_value' => 0,
                'max_value' => 100,
                'polarity' => 'negative',
                'critical_threshold' => 20,
                'warning_threshold' => 10,
                'primary_population_group_id' => null,
            ],
            [
                'code' => 'idp_population_count',
                'name' => 'IDP Population Count',
                'description' => 'Number of internally displaced persons',
                'category' => 'conflict',
                'subcategory' => 'displacement',
                'data_type' => 'count',
                'unit' => 'persons',
                'min_value' => 0,
                'max_value' => null,
                'polarity' => 'negative',
                'critical_threshold' => 5000,
                'warning_threshold' => 1000,
                'primary_population_group_id' => null,
            ],
            [
                'code' => 'security_incidents_rate',
                'name' => 'Security Incidents Rate',
                'description' => 'Security incidents per 10,000 population',
                'category' => 'conflict',
                'subcategory' => 'security',
                'data_type' => 'rate',
                'unit' => 'per_10000',
                'min_value' => 0,
                'max_value' => null,
                'polarity' => 'negative',
                'critical_threshold' => 50,
                'warning_threshold' => 20,
                'primary_population_group_id' => null,
            ],
            
            // ====================
            // INFRASTRUCTURE INDICATORS (3)
            // ====================
            
            [
                'code' => 'road_accessibility_index',
                'name' => 'Road Accessibility Index',
                'description' => 'Percentage of population with year-round road access',
                'category' => 'infrastructure',
                'subcategory' => 'transport',
                'data_type' => 'percentage',
                'unit' => '%',
                'min_value' => 0,
                'max_value' => 100,
                'polarity' => 'positive',
                'critical_threshold' => 40,
                'warning_threshold' => 60,
                'primary_population_group_id' => null,
            ],
            [
                'code' => 'electricity_access',
                'name' => 'Electricity Access',
                'description' => 'Percentage of households with electricity',
                'category' => 'infrastructure',
                'subcategory' => 'energy',
                'data_type' => 'percentage',
                'unit' => '%',
                'min_value' => 0,
                'max_value' => 100,
                'polarity' => 'positive',
                'critical_threshold' => 20,
                'warning_threshold' => 40,
                'primary_population_group_id' => null,
            ],
            [
                'code' => 'mobile_network_coverage',
                'name' => 'Mobile Network Coverage',
                'description' => 'Percentage of area with mobile signal',
                'category' => 'infrastructure',
                'subcategory' => 'communication',
                'data_type' => 'percentage',
                'unit' => '%',
                'min_value' => 0,
                'max_value' => 100,
                'polarity' => 'positive',
                'critical_threshold' => 50,
                'warning_threshold' => 70,
                'primary_population_group_id' => null,
            ],
            
            // ====================
            // DEMOGRAPHIC INDICATORS (2)
            // ====================
            
            [
                'code' => 'population_density',
                'name' => 'Population Density',
                'description' => 'Persons per square kilometer',
                'category' => 'demographic',
                'subcategory' => 'density',
                'data_type' => 'measurement',
                'unit' => 'per_km2',
                'min_value' => 0,
                'max_value' => null,
                'polarity' => 'negative',
                'critical_threshold' => 500,
                'warning_threshold' => 300,
                'primary_population_group_id' => null,
            ],
            [
                'code' => 'dependency_ratio',
                'name' => 'Child Dependency Ratio',
                'description' => 'Ratio of children (0-14) to working-age population (15-64)',
                'category' => 'demographic',
                'subcategory' => 'structure',
                'data_type' => 'ratio',
                'unit' => 'ratio',
                'min_value' => 0,
                'max_value' => null,
                'polarity' => 'negative',
                'critical_threshold' => 1.0,
                'warning_threshold' => 0.7,
                'primary_population_group_id' => null,
            ],
            
            // ====================
            // ECONOMIC INDICATORS (2)
            // ====================
            
            [
                'code' => 'poverty_rate',
                'name' => 'Poverty Rate',
                'description' => 'Percentage living below national poverty line',
                'category' => 'economic',
                'subcategory' => 'poverty',
                'data_type' => 'percentage',
                'unit' => '%',
                'min_value' => 0,
                'max_value' => 100,
                'polarity' => 'negative',
                'critical_threshold' => 60,
                'warning_threshold' => 40,
                'primary_population_group_id' => null,
            ],
            [
                'code' => 'livelihood_disruption_index',
                'name' => 'Livelihood Disruption Index',
                'description' => 'Composite measure of economic shock impact',
                'category' => 'economic',
                'subcategory' => 'livelihoods',
                'data_type' => 'index',
                'unit' => 'score',
                'min_value' => 0,
                'max_value' => 10,
                'polarity' => 'negative',
                'critical_threshold' => 7,
                'warning_threshold' => 5,
                'primary_population_group_id' => null,
            ],
        ];

        // Add common fields to all indicators
        foreach ($indicators as &$indicator) {
            $indicator['id'] = Str::uuid()->toString();
            $indicator['active'] = true;
            $indicator['display_order'] = 0;
            $indicator['metadata'] = null;
            $indicator['calculation_method'] = null;
            $indicator['data_collection_guidance'] = null;
            $indicator['created_at'] = now();
            $indicator['updated_at'] = now();
        }

        DB::table('indicators')->insert($indicators);

        $this->command->info('✔ Seeded ' . count($indicators) . ' indicators across 9 categories');
        $this->command->info('   - Health: 7');
        $this->command->info('   - WASH: 4');
        $this->command->info('   - Nutrition: 5');
        $this->command->info('   - Education: 3');
        $this->command->info('   - Climate: 6');
        $this->command->info('   - Conflict: 4');
        $this->command->info('   - Infrastructure: 3');
        $this->command->info('   - Demographic: 2');
        $this->command->info('   - Economic: 2');
    }
}