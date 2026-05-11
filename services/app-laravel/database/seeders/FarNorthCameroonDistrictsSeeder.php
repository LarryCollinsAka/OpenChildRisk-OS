<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\State;
use App\Models\District;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FarNorthCameroonDistrictsSeeder extends Seeder
{
    public function run(): void
    {
        // Get Cameroon
        $cameroon = Country::where('iso3', 'CMR')->first();
        
        if (!$cameroon) {
            $this->command->error('Cameroon not found in countries table!');
            return;
        }

        // Get or create Far North Region (Extrême-Nord)
        $farNorth = State::firstOrCreate(
            ['name' => 'Far North', 'country_id' => $cameroon->id],
            [
                'id' => Str::uuid(),
                'country_code' => 'CM',
                'iso2' => 'CM-EN',
                'latitude' => 10.5919,
                'longitude' => 14.5963,
            ]
        );

        $this->command->info('Far North Region: ' . $farNorth->name);

        // Districts with real coordinates
        $districts = [
            [
                'name' => 'Mora',
                'code' => 'CM-EN-MORA',
                'centroid_lat' => 11.0455,
                'centroid_lng' => 14.1392,
                'population' => 125000,
                'area_sq_km' => 5248,
                'description' => 'High-risk district with recurring cholera outbreaks and poor WASH infrastructure',
            ],
            [
                'name' => 'Makary',
                'code' => 'CM-EN-MAKARY',
                'centroid_lat' => 12.5739,
                'centroid_lng' => 14.4581,
                'population' => 89000,
                'area_sq_km' => 4156,
                'description' => 'Border district prone to flooding and displacement',
            ],
            [
                'name' => 'Kousseri',
                'code' => 'CM-EN-KOUSSERI',
                'centroid_lat' => 12.0778,
                'centroid_lng' => 15.0308,
                'population' => 435000,
                'area_sq_km' => 484,
                'description' => 'Urban district with low vaccination coverage and malaria prevalence',
            ],
            [
                'name' => 'Yagoua',
                'code' => 'CM-EN-YAGOUA',
                'centroid_lat' => 10.3414,
                'centroid_lng' => 15.2372,
                'population' => 92000,
                'area_sq_km' => 8745,
                'description' => 'Agricultural district with stable health indicators',
            ],
            [
                'name' => 'Maroua',
                'code' => 'CM-EN-MAROUA',
                'centroid_lat' => 10.5913,
                'centroid_lng' => 14.3159,
                'population' => 320000,
                'area_sq_km' => 297,
                'description' => 'Regional capital with better health infrastructure',
            ],
            [
                'name' => 'Kolofata',
                'code' => 'CM-EN-KOLOFATA',
                'centroid_lat' => 11.0575,
                'centroid_lng' => 13.9364,
                'population' => 68000,
                'area_sq_km' => 3642,
                'description' => 'Remote district affected by conflict and displacement',
            ],
            [
                'name' => 'Logone-Birni',
                'code' => 'CM-EN-LOGONE-BIRNI',
                'centroid_lat' => 12.3667,
                'centroid_lng' => 14.9667,
                'population' => 52000,
                'area_sq_km' => 2450,
                'description' => 'Riverine district with seasonal flooding challenges',
            ],
        ];

        foreach ($districts as $districtData) {
            $district = District::updateOrCreate(
                ['code' => $districtData['code']],
                [
                    'name' => $districtData['name'],
                    'state_id' => $farNorth->id,
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

        $this->command->info("\n✅ Created " . count($districts) . " districts in Far North Region");
    }
}