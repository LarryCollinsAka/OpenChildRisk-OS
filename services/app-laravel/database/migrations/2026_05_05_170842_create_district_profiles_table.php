<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * District profiles - UNICEF child vulnerability indicators.
     * 
     * Our intelligence layer on top of geography.
     * Links to cities (canonical geography).
     * 
     * Contains:
     * - Child population demographics
     * - WASH coverage indicators
     * - Conflict/crisis flags
     * - Climate vulnerability scores
     * 
     * Source: UNICEF, WHO, national statistics, field surveys
     */
    public function up(): void
    {
        Schema::create('district_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('city_id'); // FK to canonical geography
            
            // CHILD DEMOGRAPHICS
            $table->integer('children_under5')->nullable();
            $table->integer('children_5_to_14')->nullable();
            $table->integer('children_15_to_17')->nullable();
            $table->integer('total_children')->nullable(); // sum of above
            
            // Total population for context
            $table->integer('total_population')->nullable();
            
            // WASH INDICATORS (0.0 to 1.0)
            $table->decimal('sanitation_coverage', 5, 4)->nullable(); // 0.0000 to 1.0000
            $table->decimal('wash_coverage', 5, 4)->nullable();
            $table->decimal('safe_water_access', 5, 4)->nullable();
            
            // HEALTH INFRASTRUCTURE
            $table->integer('health_facilities')->nullable();
            $table->integer('trained_health_workers')->nullable();
            
            // NUTRITION
            $table->decimal('stunting_rate', 5, 4)->nullable(); // 0.0000 to 1.0000
            $table->decimal('wasting_rate', 5, 4)->nullable();
            $table->decimal('underweight_rate', 5, 4)->nullable();
            
            // CRISIS FLAGS
            $table->boolean('conflict_affected')->default(false);
            $table->boolean('climate_vulnerable')->default(false);
            $table->boolean('disease_endemic')->default(false);
            $table->boolean('flood_prone')->default(false);
            $table->boolean('drought_prone')->default(false);
            
            // VULNERABILITY SCORES (0-10)
            $table->decimal('vulnerability_score', 4, 2)->nullable(); // 0.00 to 10.00
            $table->decimal('resilience_score', 4, 2)->nullable();
            
            // DATA QUALITY
            $table->string('data_source', 100)->nullable(); // unicef, who, census
            $table->date('data_collection_date')->nullable();
            $table->enum('data_quality', ['verified', 'estimated', 'outdated'])->default('estimated');
            
            // Time validity (profiles change over time)
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_to')->nullable();
            
            // Metadata for additional indicators
            $table->jsonb('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign keys
            $table->foreign('city_id')
                  ->references('id')
                  ->on('cities')
                  ->onDelete('cascade');
            
            // Indexes
            $table->index('city_id');
            $table->index('conflict_affected');
            $table->index('climate_vulnerable');
            $table->index('vulnerability_score');
            $table->index(['valid_from', 'valid_to']);
            $table->index('data_quality');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('district_profiles');
    }
};