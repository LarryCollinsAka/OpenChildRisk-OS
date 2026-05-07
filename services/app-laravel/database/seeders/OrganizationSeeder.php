<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrganizationSeeder extends Seeder
{
    /**
     * Seed UNICEF Cameroon for pilot.
     */
    public function run(): void
    {
        // Get Cameroon country ID
        $cameroon = DB::table('countries')->where('iso3', 'CMR')->first();

        if (!$cameroon) {
            $this->command->error('Cameroon not found. Run GeographySeeder first.');
            return;
        }

        $unicefId = Str::uuid()->toString();

        DB::table('organizations')->insert([
            'id' => $unicefId,
            'name' => 'United Nations Children\'s Fund',
            'abbreviation' => 'UNICEF',
            'code' => 'UNICEF-CM',
            'description' => 'UNICEF works in over 190 countries and territories to save children\'s lives, defend their rights, and help them fulfill their potential.',
            'type' => 'un_agency',
            'email' => 'yaounde@unicef.org',
            'phone' => '+237 222 23 13 00',
            'website' => 'https://www.unicef.org/cameroon/',
            'address' => 'Immeuble KADJI, Route de l\'Aéroport, Yaoundé, Cameroon',
            'country_id' => $cameroon->id,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info('✔ Seeded UNICEF Cameroon');
    }
}