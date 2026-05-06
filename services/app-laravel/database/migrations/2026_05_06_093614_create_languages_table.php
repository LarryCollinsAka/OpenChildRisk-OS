<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Languages - supported languages for system and domain data.
     * 
     * Two-layer translation approach:
     * 1. Static UI strings → JSON files (lang/{code}/alerts.json)
     * 2. Domain data → database tables (district_translations, etc.)
     * 
     * Initial support:
     * - English (en)
     * - French (fr)
     * - Arabic (ar)
     * - Spanish (es)
     * - Portuguese (pt)
     * - Dutch (nl)
     * - German (de)
     * 
     * Adding new language = ONE INSERT + optional JSON upload.
     * Zero code changes required.
     */
    public function up(): void
    {
        Schema::create('languages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // ISO 639-1 language code (2 chars) or locale (5 chars like pt-BR)
            $table->string('code', 10)->unique();
            
            // Language name in English
            $table->string('name', 100);
            
            // Language name in native script
            $table->string('native_name', 100)->nullable();
            
            // Text direction
            $table->enum('direction', ['ltr', 'rtl'])->default('ltr');
            
            // Is this language active in the system?
            $table->boolean('active')->default(true);
            
            // Is this the default/fallback language?
            $table->boolean('is_default')->default(false);
            
            // Completeness of translations (0.0 to 1.0)
            $table->decimal('translation_coverage', 3, 2)->default(1.00);
            
            $table->timestamps();
            
            // Indexes
            $table->index('code');
            $table->index('active');
            $table->index('is_default');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};