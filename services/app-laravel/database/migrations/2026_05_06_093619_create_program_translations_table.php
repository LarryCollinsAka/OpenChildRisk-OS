<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Program translations - multilingual program names.
     * 
     * Example:
     * Program: Emergency WASH Response
     * - en: "Emergency WASH Response"
     * - fr: "Réponse d'urgence WASH"
     * - ar: "استجابة الطوارئ للمياه والصرف الصحي"
     */
    public function up(): void
    {
        Schema::create('program_translations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('program_id');
            $table->uuid('language_id');
            
            // Translated content
            $table->string('name', 200);
            $table->text('description')->nullable();
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('program_id')
                  ->references('id')
                  ->on('programs')
                  ->onDelete('cascade');
            
            $table->foreign('language_id')
                  ->references('id')
                  ->on('languages')
                  ->onDelete('cascade');
            
            // Indexes
            $table->index('program_id');
            $table->index('language_id');
            
            // One translation per language per program
            $table->unique(['program_id', 'language_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_translations');
    }
};