<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hazard type translations - multilingual hazard names.
     * 
     * Example:
     * Hazard: Cholera
     * - en: "Cholera"
     * - fr: "Choléra"
     * - ar: "الكوليرا"
     */
    public function up(): void
    {
        Schema::create('hazard_type_translations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('hazard_type_id');
            $table->uuid('language_id');
            
            // Translated content
            $table->string('name', 100);
            $table->text('description')->nullable();
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('hazard_type_id')
                  ->references('id')
                  ->on('hazard_types')
                  ->onDelete('cascade');
            
            $table->foreign('language_id')
                  ->references('id')
                  ->on('languages')
                  ->onDelete('cascade');
            
            // Indexes
            $table->index('hazard_type_id');
            $table->index('language_id');
            
            // One translation per language per hazard type
            $table->unique(['hazard_type_id', 'language_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hazard_type_translations');
    }
};