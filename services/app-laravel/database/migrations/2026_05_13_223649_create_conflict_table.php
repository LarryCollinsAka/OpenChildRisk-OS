<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Conflict data architecture
     * 
     * Pattern mirrors hazard_types → hazard_events
     * Now: conflict_categories → conflict_events
     * 
     * Enables:
     * - Taxonomy standardization (ACLED event types)
     * - Multi-source conflict data
     * - Conflict type filtering
     * - Category-specific risk weights
     */
    public function up(): void
    {
        // =====================================================================
        // CONFLICT CATEGORIES TABLE (Taxonomy)
        // =====================================================================
        Schema::create('conflict_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Category identification
            $table->string('code', 50)->unique(); // 'BATTLES', 'VIOLENCE_CIVILIANS', etc.
            $table->string('name', 100); // 'Armed Battles'
            $table->text('description')->nullable();
            
            // Visual styling (for UI)
            $table->string('color', 20)->default('red'); // UI color coding
            $table->string('icon', 50)->nullable(); // Icon identifier
            
            // Risk weighting
            $table->float('base_severity_weight')->default(1.0); // Multiplier for scoring
            
            // ACLED mapping
            $table->jsonb('acled_event_types')->nullable(); // Maps to ACLED taxonomy
            
            // Status
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            
            // Indexes
            $table->index('code');
            $table->index('is_active');
        });

        // =====================================================================
        // CONFLICT EVENTS TABLE
        // =====================================================================
        Schema::create('conflict_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Classification
            $table->uuid('conflict_category_id');
            
            // Geographic context
            $table->uuid('district_id')->nullable();
            $table->uuid('country_id');
            
            // Data provenance
            $table->uuid('data_source_id')->nullable();
            $table->string('external_id')->nullable(); // ACLED event ID
            
            // Event details
            $table->date('event_date');
            $table->string('sub_event_type', 100)->nullable(); // ACLED sub-type
            $table->text('notes')->nullable();
            
            // Actors involved
            $table->jsonb('actors')->nullable(); // {actor1, actor2, inter1, inter2}
            
            // Impact metrics
            $table->integer('fatalities')->default(0);
            $table->integer('estimated_displaced')->nullable();
            
            // Location (exact coordinates from ACLED)
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('location_name', 255)->nullable();
            
            // Severity scoring
            $table->float('severity_score')->nullable(); // 0-10 calculated score
            
            // Status
            $table->enum('status', ['active', 'historical', 'disputed'])->default('active');
            
            // Additional metadata
            $table->jsonb('metadata')->nullable();
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('conflict_category_id')
                  ->references('id')
                  ->on('conflict_categories')
                  ->onDelete('restrict');
                  
            $table->foreign('district_id')
                  ->references('id')
                  ->on('districts')
                  ->onDelete('set null');
                  
            $table->foreign('country_id')
                  ->references('id')
                  ->on('countries')
                  ->onDelete('cascade');
                  
            $table->foreign('data_source_id')
                  ->references('id')
                  ->on('data_sources')
                  ->onDelete('set null');
            
            // Indexes for performance
            $table->index('conflict_category_id');
            $table->index('district_id');
            $table->index('country_id');
            $table->index('event_date');
            $table->index(['latitude', 'longitude']);
            $table->index('external_id');
            $table->index('status');
        });

        // =====================================================================
        // DISTRICT ACCESS CONSTRAINTS TABLE
        // =====================================================================
        Schema::create('district_access_constraints', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->uuid('district_id');
            
            // Access status
            $table->enum('access_level', ['full', 'partial', 'limited', 'no_access'])
                  ->default('full');
            
            // Infrastructure accessibility
            $table->boolean('road_access')->default(true);
            $table->boolean('humanitarian_access')->default(true);
            $table->boolean('health_facility_accessible')->default(true);
            
            // Security assessment
            $table->enum('security_risk_level', ['low', 'medium', 'high', 'critical'])
                  ->default('low');
            
            // Operational notes
            $table->text('access_notes')->nullable();
            $table->text('security_notes')->nullable();
            
            // Assessment metadata
            $table->timestamp('assessed_at');
            $table->string('assessed_by')->nullable(); // Organization/person
            $table->date('valid_until')->nullable(); // When reassessment needed
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('district_id')
                  ->references('id')
                  ->on('districts')
                  ->onDelete('cascade');
            
            // Indexes
            $table->index('district_id');
            $table->index('access_level');
            $table->index('security_risk_level');
            $table->index('assessed_at');
        });

        // =====================================================================
        // DISTRICT CONFLICT SCORES TABLE (Calculated)
        // =====================================================================
        Schema::create('district_conflict_scores', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->uuid('district_id');
            
            // Time period
            $table->date('score_date');
            $table->integer('days_analyzed')->default(30); // Rolling window
            
            // Conflict metrics
            $table->integer('event_count')->default(0);
            $table->integer('total_fatalities')->default(0);
            $table->integer('estimated_displaced')->default(0);
            
            // Calculated scores (0-10)
            $table->float('intensity_score')->default(0); // Event frequency
            $table->float('severity_score')->default(0); // Fatalities + displacement
            $table->float('recency_score')->default(0); // How recent
            $table->float('overall_score')->default(0); // Composite
            
            // Risk classification
            $table->enum('risk_level', ['low', 'medium', 'high', 'critical'])
                  ->default('low');
            
            // Calculation metadata
            $table->jsonb('calculation_details')->nullable();
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('district_id')
                  ->references('id')
                  ->on('districts')
                  ->onDelete('cascade');
            
            // Indexes
            $table->index('district_id');
            $table->index('score_date');
            $table->index('overall_score');
            $table->index('risk_level');
            
            // One score per district per date
            $table->unique(['district_id', 'score_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('district_conflict_scores');
        Schema::dropIfExists('district_access_constraints');
        Schema::dropIfExists('conflict_events');
        Schema::dropIfExists('conflict_categories');
    }
};