<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * District translations - multilingual district names.
     * 
     * Example:
     * District: Far North region
     * - en: "Far North"
     * - fr: "Extrême-Nord"
     * - ar: "الشمال الأقصى"
     * 
     * Used in:
     * - Alert messages
     * - Dashboard UI
     * - Reports
     */
    public function up(): void
    {
        Schema::create('district_translations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('district_id');
            $table->uuid('language_id');
            
            // Translated content
            $table->string('name', 100);
            $table->text('description')->nullable();
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('district_id')
                  ->references('id')
                  ->on('districts')
                  ->onDelete('cascade');
            
            $table->foreign('language_id')
                  ->references('id')
                  ->on('languages')
                  ->onDelete('cascade');
            
            // Indexes
            $table->index('district_id');
            $table->index('language_id');
            
            // One translation per language per district
            $table->unique(['district_id', 'language_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('district_translations');
    }
};