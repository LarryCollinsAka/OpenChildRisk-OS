<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\District;
use App\Models\DistrictTranslation;
use App\Models\Language;

class DistrictTranslationsSeeder extends Seeder
{
    public function run(): void
    {
        // Get language IDs (NOT codes!)
        $languages = Language::whereIn('code', ['en', 'fr', 'ar'])
            ->pluck('id', 'code')
            ->toArray();

        if (empty($languages)) {
            $this->command->error('❌ No languages found! Run LanguagesSeeder first.');
            return;
        }

        $this->command->info('Found languages: ' . implode(', ', array_keys($languages)));

        $translations = [
            'Mora' => [
                'en' => ['name' => 'Mora', 'description' => 'District in Far North Region'],
                'fr' => ['name' => 'Mora', 'description' => 'District de la Région de l\'Extrême-Nord'],
                'ar' => ['name' => 'مورا', 'description' => 'مقاطعة في منطقة أقصى الشمال'],
            ],
            'Makary' => [
                'en' => ['name' => 'Makary', 'description' => 'District in Far North Region'],
                'fr' => ['name' => 'Makary', 'description' => 'District de la Région de l\'Extrême-Nord'],
                'ar' => ['name' => 'ماكاري', 'description' => 'مقاطعة في منطقة أقصى الشمال'],
            ],
            'Kousseri' => [
                'en' => ['name' => 'Kousseri', 'description' => 'District in Far North Region'],
                'fr' => ['name' => 'Kousseri', 'description' => 'District de la Région de l\'Extrême-Nord'],
                'ar' => ['name' => 'كوسيري', 'description' => 'مقاطعة في منطقة أقصى الشمال'],
            ],
            'Yagoua' => [
                'en' => ['name' => 'Yagoua', 'description' => 'District in Far North Region'],
                'fr' => ['name' => 'Yagoua', 'description' => 'District de la Région de l\'Extrême-Nord'],
                'ar' => ['name' => 'ياغوا', 'description' => 'مقاطعة في منطقة أقصى الشمال'],
            ],
            'Maroua' => [
                'en' => ['name' => 'Maroua', 'description' => 'Capital of Far North Region'],
                'fr' => ['name' => 'Maroua', 'description' => 'Capitale de la Région de l\'Extrême-Nord'],
                'ar' => ['name' => 'ماروا', 'description' => 'عاصمة منطقة أقصى الشمال'],
            ],
            'Kolofata' => [
                'en' => ['name' => 'Kolofata', 'description' => 'District in Far North Region'],
                'fr' => ['name' => 'Kolofata', 'description' => 'District de la Région de l\'Extrême-Nord'],
                'ar' => ['name' => 'كولوفاتا', 'description' => 'مقاطعة في منطقة أقصى الشمال'],
            ],
            'Logone-Birni' => [
                'en' => ['name' => 'Logone-Birni', 'description' => 'District in Far North Region'],
                'fr' => ['name' => 'Logone-Birni', 'description' => 'District de la Région de l\'Extrême-Nord'],
                'ar' => ['name' => 'لوغون-بيرني', 'description' => 'مقاطعة في منطقة أقصى الشمال'],
            ],
            'Bertoua' => [
                'en' => ['name' => 'Bertoua', 'description' => 'Capital of East Region'],
                'fr' => ['name' => 'Bertoua', 'description' => 'Capitale de la Région de l\'Est'],
                'ar' => ['name' => 'بيرتوا', 'description' => 'عاصمة منطقة الشرق'],
            ],
            'Batouri' => [
                'en' => ['name' => 'Batouri', 'description' => 'District in East Region'],
                'fr' => ['name' => 'Batouri', 'description' => 'District de la Région de l\'Est'],
                'ar' => ['name' => 'باتوري', 'description' => 'مقاطعة في منطقة الشرق'],
            ],
            'Yokadouma' => [
                'en' => ['name' => 'Yokadouma', 'description' => 'District in East Region'],
                'fr' => ['name' => 'Yokadouma', 'description' => 'District de la Région de l\'Est'],
                'ar' => ['name' => 'يوكادوما', 'description' => 'مقاطعة في منطقة الشرق'],
            ],
            'Abong-Mbang' => [
                'en' => ['name' => 'Abong-Mbang', 'description' => 'District in East Region'],
                'fr' => ['name' => 'Abong-Mbang', 'description' => 'District de la Région de l\'Est'],
                'ar' => ['name' => 'أبونغ-مبانغ', 'description' => 'مقاطعة في منطقة الشرق'],
            ],
        ];

        $created = 0;
        $updated = 0;

        foreach ($translations as $districtName => $langs) {
            $district = District::where('name', $districtName)->first();

            if (!$district) {
                $this->command->warn("⚠️  District not found: {$districtName}");
                continue;
            }

            foreach ($langs as $langCode => $content) {
                if (!isset($languages[$langCode])) {
                    continue;
                }

                $result = DistrictTranslation::updateOrCreate(
                    [
                        'district_id' => $district->id,
                        'language_id' => $languages[$langCode], // ← USING language_id
                    ],
                    [
                        'name' => $content['name'],
                        'description' => $content['description'],
                    ]
                );

                if ($result->wasRecentlyCreated) {
                    $created++;
                } else {
                    $updated++;
                }
            }

            $this->command->info("✅ {$districtName}");
        }

        $this->command->newLine();
        $this->command->info("📊 Created: {$created} | Updated: {$updated}");
    }
}