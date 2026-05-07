<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GeographySeeder extends Seeder
{
    /**
     * Seed all countries, states, and cities from JSON dataset.
     * 
     * Source: countries-states-cities-database (ODbL-1.0)
     * Data: 250 countries, 5,299 states, 153,765 cities
     * 
     * Strategy: Chunked inserts for memory efficiency
     */
    public function run(): void
    {
        $jsonPath = database_path('seeders/data/countries-states-cities.json');

        if (!file_exists($jsonPath)) {
            $this->command->error('JSON file not found at: ' . $jsonPath);
            $this->command->info('Please place the full countries-states-cities.json file in database/seeders/data/');
            return;
        }

        $this->command->info('Starting geography import (chunked processing)...');

        // Increase memory and time limits
        ini_set('memory_limit', '512M');
        set_time_limit(3600); // 1 hour

        $this->command->info('Loading and parsing JSON...');
        $jsonContent = file_get_contents($jsonPath);

        $this->command->info('Decoding JSON...');
        $countries = json_decode($jsonContent, true);

        // Free memory
        unset($jsonContent);

        if (!$countries) {
            $this->command->error('Failed to parse JSON');
            return;
        }

        $this->command->info('Found ' . count($countries) . ' countries. Starting import...');

        $countryCount = 0;
        $stateCount = 0;
        $cityCount = 0;
        $startTime = microtime(true);

        foreach ($countries as $countryData) {
            try {
                // Insert country
                $countryId = $this->insertCountry($countryData);
                $countryCount++;

                // Insert timezones (small batch)
                if (!empty($countryData['timezones'])) {
                    $this->insertTimezones($countryId, $countryData['timezones']);
                }

                // Insert translations (small batch)
                if (!empty($countryData['translations'])) {
                    $this->insertCountryTranslations($countryId, $countryData['translations']);
                }

                // Insert states
                foreach ($countryData['states'] ?? [] as $stateData) {
                    $stateId = $this->insertState($countryId, $stateData);
                    $stateCount++;

                    // Insert cities in smaller chunks (100 at a time)
                    $cities = $stateData['cities'] ?? [];
                    if (!empty($cities)) {
                        $cityChunks = array_chunk($cities, 100);

                        foreach ($cityChunks as $chunk) {
                            $this->insertCities($stateId, $countryId, $chunk);
                            $cityCount += count($chunk);
                        }
                    }

                    // Free memory every 10 states
                    if ($stateCount % 10 === 0) {
                        gc_collect_cycles();
                    }
                }

                // Progress every 5 countries
                if ($countryCount % 5 === 0) {
                    $elapsed = round(microtime(true) - $startTime, 2);
                    $this->command->info("Progress: {$countryCount} countries, {$stateCount} states, {$cityCount} cities ({$elapsed}s)");
                }
            } catch (\Exception $e) {
                $this->command->error("Error processing country: " . ($countryData['name'] ?? 'unknown'));
                $this->command->error($e->getMessage());
                continue;
            }
        }

        $totalTime = round(microtime(true) - $startTime, 2);

        $this->command->info('✔ Seeded ' . $countryCount . ' countries');
        $this->command->info('✔ Seeded ' . $stateCount . ' states');
        $this->command->info('✔ Seeded ' . $cityCount . ' cities');
        $this->command->info('Total time: ' . $totalTime . ' seconds');
    }

    private function insertCountry(array $data): string
    {
        $id = Str::uuid()->toString();

        DB::table('countries')->insert([
            'id' => $id,
            'external_id' => $data['id'],
            'external_source' => 'csc_dataset',
            'iso2' => $data['iso2'],
            'iso3' => $data['iso3'],
            'numeric_code' => $data['numeric_code'] ?? null,
            'name' => $data['name'],
            'native' => $data['native'] ?? null,
            'capital' => $data['capital'] ?? null,
            'nationality' => $data['nationality'] ?? null,
            'phonecode' => $data['phonecode'] ?? null,
            'tld' => $data['tld'] ?? null,
            'currency' => $data['currency'] ?? null,
            'currency_name' => $data['currency_name'] ?? null,
            'currency_symbol' => $data['currency_symbol'] ?? null,
            'region' => $data['region'] ?? null,
            'region_id' => $data['region_id'] ?? null,
            'subregion' => $data['subregion'] ?? null,
            'subregion_id' => $data['subregion_id'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'population' => $data['population'] ?? null,
            'gdp' => $data['gdp'] ?? null,
            'emoji' => $data['emoji'] ?? null,
            'emoji_u' => $data['emojiU'] ?? null,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $id;
    }

    private function insertTimezones(string $countryId, array $timezones): void
    {
        $records = [];

        foreach ($timezones as $tz) {
            $records[] = [
                'id' => Str::uuid()->toString(),
                'country_id' => $countryId,
                'zone_name' => $tz['zoneName'],
                'gmt_offset' => $tz['gmtOffset'],
                'gmt_offset_name' => $tz['gmtOffsetName'],
                'abbreviation' => $tz['abbreviation'],
                'tz_name' => $tz['tzName'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($records)) {
            DB::table('country_timezones')->insert($records);
        }
    }

    private function insertCountryTranslations(string $countryId, array $translations): void
    {
        $records = [];

        foreach ($translations as $langCode => $name) {
            $records[] = [
                'id' => Str::uuid()->toString(),
                'country_id' => $countryId,
                'language_code' => $langCode,
                'name' => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($records)) {
            DB::table('country_translations')->insert($records);
        }
    }

    private function insertState(string $countryId, array $data): string
    {
        $id = Str::uuid()->toString();

        DB::table('states')->insert([
            'id' => $id,
            'country_id' => $countryId,
            'external_id' => $data['id'],
            'external_source' => 'csc_dataset',
            'name' => $data['name'],
            'native' => $data['native'] ?? null,
            'iso2' => $data['iso2'] ?? null,
            'iso3166_2' => $data['iso3166_2'] ?? null,
            'type' => $data['type'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'timezone' => $data['timezone'] ?? null,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $id;
    }

    private function insertCities(string $stateId, string $countryId, array $cities): void
    {
        $records = [];

        foreach ($cities as $city) {
            $records[] = [
                'id' => Str::uuid()->toString(),
                'state_id' => $stateId,
                'country_id' => $countryId,
                'external_id' => $city['id'],
                'external_source' => 'csc_dataset',
                'name' => $city['name'],
                'latitude' => $city['latitude'],
                'longitude' => $city['longitude'],
                'timezone' => $city['timezone'] ?? null,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($records)) {
            DB::table('cities')->insert($records);
        }
    }
}
