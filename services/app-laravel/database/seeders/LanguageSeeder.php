<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Language;

class LanguageSeeder extends Seeder
{
    public function run(): void
    {
        $languages = [
            ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'direction' => 'ltr', 'is_default' => true],
            ['code' => 'fr', 'name' => 'French', 'native_name' => 'Français', 'direction' => 'ltr', 'is_default' => false],
            ['code' => 'es', 'name' => 'Spanish', 'native_name' => 'Español', 'direction' => 'ltr', 'is_default' => false],
            ['code' => 'pt', 'name' => 'Portuguese', 'native_name' => 'Português', 'direction' => 'ltr', 'is_default' => false],
            ['code' => 'ar', 'name' => 'Arabic', 'native_name' => 'العربية', 'direction' => 'rtl', 'is_default' => false],
            ['code' => 'bn', 'name' => 'Bangla', 'native_name' => 'বাংলা', 'direction' => 'ltr', 'is_default' => false],
            ['code' => 'th', 'name' => 'Thai', 'native_name' => 'ไทย', 'direction' => 'ltr', 'is_default' => false],
            ['code' => 'uk', 'name' => 'Ukrainian', 'native_name' => 'Українська', 'direction' => 'ltr', 'is_default' => false],
            ['code' => 'nl', 'name' => 'Dutch', 'native_name' => 'Nederlands', 'direction' => 'ltr', 'is_default' => false],
            ['code' => 'vi', 'name' => 'Vietnamese', 'native_name' => 'Tiếng Việt', 'direction' => 'ltr', 'is_default' => false],
            ['code' => 'hi', 'name' => 'Hindi', 'native_name' => 'हिन्दी', 'direction' => 'ltr', 'is_default' => false],
        ];

        foreach ($languages as $lang) {
            Language::updateOrCreate(
                ['code' => $lang['code']],
                array_merge($lang, ['active' => true, 'translation_coverage' => 1.00])
            );
        }

        $this->command->info('✅ Seeded ' . count($languages) . ' languages');
    }
}