<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create indicators table.
     * 
     * Defines quantifiable vulnerability dimensions.
     * 
     * EXAMPLES:
     * - vaccination_coverage_dpt3 (%)
     * - improved_water_access (%)
     * - sam_prevalence (%)
     * - cholera_cases_per_1000
     * - rainfall_anomaly_mm
     * - conflict_events_count
     * 
     * This becomes the semantic backbone of the vulnerability model.
     */
    public function up(): void
    {
        Schema::create('indicators', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->string('code', 100)->unique();
            $table->string('name', 200);
            $table->text('description')->nullable();
            
            // Categorization
            $table->enum('category', [
                'health',
                'wash',
                'nutrition',
                'education',
                'climate',
                'conflict',
                'infrastructure',
                'demographic',
                'economic'
            ]);
            
            $table->string('subcategory', 100)->nullable(); // vaccination, disease, water, sanitation, etc.
            
            // Measurement
            $table->enum('data_type', [
                'percentage',      // 0-100%
                'rate',           // per 1000, per 10000
                'count',          // absolute number
                'index',          // 0-1 or 0-10 scale
                'binary',         // yes/no, true/false
                'ratio',          // x:y ratio
                'measurement'     // mm, kg, etc.
            ]);
            
            $table->string('unit', 50)->nullable(); // %, per_1000, mm, cases, events
            $table->decimal('min_value', 10, 2)->nullable(); // Expected minimum
            $table->decimal('max_value', 10, 2)->nullable(); // Expected maximum
            
            // Interpretation (for risk scoring)
            $table->enum('polarity', [
                'positive',  // Higher = better (vaccination coverage)
                'negative'   // Higher = worse (disease prevalence)
            ])->default('negative');
            
            // Thresholds (for alerting)
            $table->decimal('critical_threshold', 10, 2)->nullable();
            $table->decimal('warning_threshold', 10, 2)->nullable();
            
            // Related population group (optional)
            $table->uuid('primary_population_group_id')->nullable();
            
            // Status
            $table->boolean('active')->default(true);
            $table->integer('display_order')->default(0);
            
            // Metadata
            $table->text('calculation_method')->nullable();
            $table->text('data_collection_guidance')->nullable();
            $table->jsonb('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign keys
            $table->foreign('primary_population_group_id')
                  ->references('id')
                  ->on('population_groups')
                  ->onDelete('set null');
            
            // Indexes
            $table->index('code');
            $table->index('category');
            $table->index('data_type');
            $table->index('active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('indicators');
    }
};