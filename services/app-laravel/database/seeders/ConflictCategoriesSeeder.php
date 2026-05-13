<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ConflictCategory;

class ConflictCategoriesSeeder extends Seeder
{
    /**
     * Seed ACLED conflict categories
     * 
     * Based on ACLED codebook:
     * https://acleddata.com/acleddatanew/wp-content/uploads/dlm_uploads/2019/04/ACLED_Codebook_2019FINAL.pdf
     */
    public function run(): void
    {
        $categories = [
            [
                'code' => 'BATTLES',
                'name' => 'Armed Battles',
                'description' => 'Violent interactions between armed groups (state forces, rebel groups, militias)',
                'color' => 'red',
                'icon' => 'crossed-swords',
                'base_severity_weight' => 2.0,
                'acled_event_types' => ['Battles', 'Armed clash'],
            ],
            [
                'code' => 'EXPLOSIONS',
                'name' => 'Explosions & Remote Violence',
                'description' => 'Use of explosive devices or remote violence (bombs, IEDs, airstrikes, shelling)',
                'color' => 'orange',
                'icon' => 'explosion',
                'base_severity_weight' => 2.5,
                'acled_event_types' => [
                    'Explosions/Remote violence',
                    'Air/drone strike',
                    'Shelling/artillery/missile attack',
                    'Remote explosive/landmine/IED'
                ],
            ],
            [
                'code' => 'VIOLENCE_CIVILIANS',
                'name' => 'Violence Against Civilians',
                'description' => 'Direct attacks on unarmed civilians causing death, injury, or destruction',
                'color' => 'darkred',
                'icon' => 'alert-triangle',
                'base_severity_weight' => 3.0,
                'acled_event_types' => [
                    'Violence against civilians',
                    'Attack',
                    'Sexual violence',
                    'Abduction/forced disappearance'
                ],
            ],
            [
                'code' => 'PROTESTS',
                'name' => 'Protests',
                'description' => 'Non-violent demonstrations or public gatherings',
                'color' => 'blue',
                'icon' => 'users',
                'base_severity_weight' => 0.5,
                'acled_event_types' => ['Protests', 'Peaceful protest'],
            ],
            [
                'code' => 'RIOTS',
                'name' => 'Riots',
                'description' => 'Violent demonstrations involving crowds clashing with other groups or forces',
                'color' => 'amber',
                'icon' => 'flame',
                'base_severity_weight' => 1.5,
                'acled_event_types' => ['Riots', 'Violent demonstration', 'Mob violence'],
            ],
            [
                'code' => 'STRATEGIC_DEVELOPMENTS',
                'name' => 'Strategic Developments',
                'description' => 'Non-violent strategic actions (agreements, arrests, looting, disruptions)',
                'color' => 'gray',
                'icon' => 'flag',
                'base_severity_weight' => 0.8,
                'acled_event_types' => [
                    'Strategic developments',
                    'Agreement',
                    'Arrests',
                    'Looting/property destruction',
                    'Disrupted weapons use'
                ],
            ],
        ];

        foreach ($categories as $category) {
            ConflictCategory::updateOrCreate(
                ['code' => $category['code']],
                array_merge($category, ['is_active' => true])
            );
        }

        $this->command->info('✅ Seeded ' . count($categories) . ' conflict categories');
    }
}