<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\State;
use App\Models\District;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Bertoua Districts Seeder (East Region, Cameroon)
 * 
 * Seeds districts for the East Region of Cameroon, centered around Bertoua.
 * This region has different risk profile than Far North:
 * - Rainforest climate (heavy rainfall, not drought)
 * - Yellow fever, typhoid risk (not cholera)
 * - Better infrastructure (urban center)
 * - Logging/deforestation challenges
 */
class BertouaDistrictsSeeder extends Seeder
{
    public function run(): void
    {
        // Get Cameroon
        $cameroon = Country::where('iso3', 'CMR')->first();
        
        if (!$cameroon) {
            $this->command->error('Cameroon not found in countries table!');
            return;
        }

        // Get or create East Region
        $eastRegion = State::firstOrCreate(
            ['name' => 'East', 'country_id' => $cameroon->id],
            [
                'id' => Str::uuid(),
                'country_code' => 'CM',
                'iso2' => 'CM-EST',
                'latitude' => 4.5777,
                'longitude' => 14.0904,
            ]
        );

        $this->command->info('East Region: ' . $eastRegion->name);

        // Districts around Bertoua with real coordinates
        $districts = [
            [
                'name' => 'Bertoua',
                'code' => 'CM-EST-BERTOUA',
                'centroid_lat' => 4.5777,
                'centroid_lng' => 14.0904,
                'population' => 218000,
                'area_sq_km' => 4234,
                'description' => 'Regional capital with urban infrastructure and health facilities',
            ],
            [
                'name' => 'Batouri',
                'code' => 'CM-EST-BATOURI',
                'centroid_lat' => 4.4333,
                'centroid_lng' => 14.3667,
                'population' => 42000,
                'area_sq_km' => 3856,
                'description' => 'Mining district with environmental health concerns',
            ],
            [
                'name' => 'Yokadouma',
                'code' => 'CM-EST-YOKADOUMA',
                'centroid_lat' => 3.5167,
                'centroid_lng' => 15.0500,
                'population' => 35000,
                'area_sq_km' => 5120,
                'description' => 'Remote rainforest district with limited healthcare access',
            ],
            [
                'name' => 'Abong-Mbang',
                'code' => 'CM-EST-ABONG-MBANG',
                'centroid_lat' => 3.9833,
                'centroid_lng' => 13.1833,
                'population' => 28000,
                'area_sq_km' => 2890,
                'description' => 'Agricultural district with seasonal flooding from Nyong River',
            ],
        ];

        foreach ($districts as $districtData) {
            $district = District::updateOrCreate(
                ['code' => $districtData['code']],
                [
                    'name' => $districtData['name'],
                    'state_id' => $eastRegion->id,
                    'country_id' => $cameroon->id,
                    'centroid_lat' => $districtData['centroid_lat'],
                    'centroid_lng' => $districtData['centroid_lng'],
                    'population' => $districtData['population'],
                    'area_sq_km' => $districtData['area_sq_km'],
                    'description' => $districtData['description'],
                    'district_type' => 'administrative',
                    'active' => true,
                ]
            );

            $this->command->info("✓ {$district->name} ({$district->population} population)");
        }

        $this->command->info("\n✅ Created " . count($districts) . " districts in East Region (Bertoua)");
    }
}