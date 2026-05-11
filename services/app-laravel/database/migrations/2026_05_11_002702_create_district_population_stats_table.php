<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create district_population_stats table.
     * 
     * Tracks population counts per group per district over time.
     * 
     * EXAMPLE:
     * Mora District, 2026-01-01:
     * - under_5: 12,000
     * - children_with_disabilities: 3,200
     * - displaced_children: 1,800
     * - zero_dose_children: 2,400
     * 
     * This enables targeted questions like:
     * "How many displaced under-5s are in the flood zone?"
     */
    public function up(): void
    {
        Schema::create('district_population_stats', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // What & where
            $table->uuid('district_id');
            $table->uuid('population_group_id');
            
            // When
            $table->date('measured_date');
            
            // Count
            $table->integer('population_count');
            $table->decimal('percentage_of_total', 5, 2)->nullable(); // % of district total
            
            // Data provenance (CRITIQUE: Track source!)
            $table->uuid('data_source_id')->nullable();
            $table->uuid('ingestion_job_id')->nullable();
            
            // Quality
            $table->enum('estimate_type', [
                'census',        // Official census
                'survey',        // Sample survey (MICS, DHS)
                'projection',    // Mathematical projection
                'estimate',      // Expert estimate
                'model'          // Statistical model
            ]);
            $table->decimal('confidence_level', 3, 2)->nullable(); // 0.00 to 1.00
            
            // Metadata
            $table->text('methodology')->nullable();
            $table->text('notes')->nullable();
            $table->jsonb('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign keys
            $table->foreign('district_id')
                  ->references('id')
                  ->on('districts')
                  ->onDelete('cascade');
            
            $table->foreign('population_group_id')
                  ->references('id')
                  ->on('population_groups')
                  ->onDelete('cascade');
            
            $table->foreign('data_source_id')
                  ->references('id')
                  ->on('data_sources')
                  ->onDelete('set null');
            
            $table->foreign('ingestion_job_id')
                  ->references('id')
                  ->on('data_ingestion_jobs')
                  ->onDelete('set null');
            
            // Indexes
            $table->index('district_id');
            $table->index('population_group_id');
            $table->index('measured_date');
            $table->index(['district_id', 'population_group_id', 'measured_date']);
            $table->index('estimate_type');
            
            // Unique constraint: one stat per district+group+date
            $table->unique(['district_id', 'population_group_id', 'measured_date'], 'unq_district_group_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('district_population_stats');
    }
};