<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create district_indicator_values table.
     * 
     * Time-series storage for indicator measurements per district.
     * 
     * CRITIQUE INTEGRATION: Full data provenance tracking.
     * 
     * EXAMPLE:
     * Mora District, 2026-01-15:
     * - vaccination_coverage_dpt3: 62% (source: DHIS2, confidence: 0.90)
     * - improved_sanitation: 38% (source: UNICEF MICS, confidence: 0.95)
     * - cholera_cases_per_1000: 2.3 (source: WHO, confidence: 0.85)
     */
    public function up(): void
    {
        Schema::create('district_indicator_values', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // What & where
            $table->uuid('district_id');
            $table->uuid('indicator_id');
            
            // When
            $table->date('measured_at');
            
            // Value
            $table->decimal('value', 15, 4); // Supports percentages, rates, counts
            $table->text('value_text')->nullable(); // For non-numeric values
            
            // Data provenance (CRITIQUE: Track source + job)
            $table->uuid('data_source_id')->nullable();
            $table->uuid('ingestion_job_id')->nullable();
            
            // Quality (CRITIQUE: Confidence tracking)
            $table->decimal('confidence_level', 3, 2)->nullable(); // 0.00 to 1.00
            $table->enum('quality_flag', [
                'verified',
                'provisional',
                'estimated',
                'questionable'
            ])->default('provisional');
            
            // Methodology (CRITIQUE: Explainability)
            $table->text('methodology')->nullable();
            $table->text('raw_value')->nullable(); // CRITIQUE: Preserve original
            $table->text('notes')->nullable();
            
            // Metadata
            $table->jsonb('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign keys
            $table->foreign('district_id')
                  ->references('id')
                  ->on('districts')
                  ->onDelete('cascade');
            
            $table->foreign('indicator_id')
                  ->references('id')
                  ->on('indicators')
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
            $table->index('indicator_id');
            $table->index('measured_at');
            $table->index('quality_flag');
            $table->index(['district_id', 'indicator_id', 'measured_at']);
            $table->index(['data_source_id', 'measured_at']);
            
            // Unique constraint: one value per district+indicator+date
            $table->unique(['district_id', 'indicator_id', 'measured_at'], 'unq_district_indicator_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('district_indicator_values');
    }
};