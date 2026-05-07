<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LanguageSeeder extends Seeder
{
    /**
     * Seed languages supported by OpenChildRisk OS.
     * 
     * Initial support: 7 languages
     * - English (default)
     * - French (primary in Cameroon)
     * - Arabic (Far North region)
     * - Spanish, Portuguese, Dutch, German (future expansion)
     */
    public function run(): void
    {
        $languages = [
            [
                'id' => Str::uuid()->toString(),
                'code' => 'en',
                'name' => 'English',
                'native_name' => 'English',
                'direction' => 'ltr',
                'active' => true,
                'is_default' => true,
                'translation_coverage' => 1.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'code' => 'fr',
                'name' => 'French',
                'native_name' => 'Français',
                'direction' => 'ltr',
                'active' => true,
                'is_default' => false,
                'translation_coverage' => 1.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'code' => 'ar',
                'name' => 'Arabic',
                'native_name' => 'العربية',
                'direction' => 'rtl',
                'active' => true,
                'is_default' => false,
                'translation_coverage' => 1.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'code' => 'es',
                'name' => 'Spanish',
                'native_name' => 'Español',
                'direction' => 'ltr',
                'active' => true,
                'is_default' => false,
                'translation_coverage' => 0.50,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'code' => 'pt',
                'name' => 'Portuguese',
                'native_name' => 'Português',
                'direction' => 'ltr',
                'active' => true,
                'is_default' => false,
                'translation_coverage' => 0.50,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'code' => 'nl',
                'name' => 'Dutch',
                'native_name' => 'Nederlands',
                'direction' => 'ltr',
                'active' => true,
                'is_default' => false,
                'translation_coverage' => 0.50,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'code' => 'de',
                'name' => 'German',
                'native_name' => 'Deutsch',
                'direction' => 'ltr',
                'active' => true,
                'is_default' => false,
                'translation_coverage' => 0.50,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('languages')->insert($languages);

        $this->command->info('✔ Seeded 7 languages');
    }
}