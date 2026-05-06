<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Facility translations - multilingual facility names.
     * 
     * Example:
     * Facility: Maroua Regional Hospital
     * - en: "Maroua Regional Hospital"
     * - fr: "Hôpital Régional de Maroua"
     * - ar: "مستشفى ماروا الإقليمي"
     */
    public function up(): void
    {
        Schema::create('facility_translations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('facility_id');
            $table->uuid('language_id');
            
            // Translated content
            $table->string('name', 100);
            $table->text('description')->nullable();
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('facility_id')
                  ->references('id')
                  ->on('facilities')
                  ->onDelete('cascade');
            
            $table->foreign('language_id')
                  ->references('id')
                  ->on('languages')
                  ->onDelete('cascade');
            
            // Indexes
            $table->index('facility_id');
            $table->index('language_id');
            
            // One translation per language per facility
            $table->unique(['facility_id', 'language_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facility_translations');
    }
};