<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * District Risk Assessments - Compound Risk Orchestration Layer
     * 
     * This is the operational decision engine that unifies:
     * - Climate scores (rainfall anomalies)
     * - Conflict scores (ACLED events)
     * - Vulnerability scores (WASH, health, population)
     * - Access scores (operational feasibility)
     * 
     * Purpose: Transform fragmented indicators into actionable intelligence.
     * 
     * Strategic Value:
     * - Explainable compound risk scoring
     * - Human-readable decision support
     * - Audit trail for interventions
     * - ML feature engineering foundation
     */
    public function up(): void
    {
        Schema::create('district_risk_assessments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Geographic context
            $table->uuid('district_id');
            $table->date('assessment_date');
            
            // ================================================================
            // COMPONENT SCORES (0-10 scale, normalized)
            // ================================================================
            
            // Climate risk (rainfall anomalies, drought, flooding)
            $table->float('climate_score')->nullable()->comment('0-10 scale');
            $table->jsonb('climate_factors')->nullable()->comment('Contributing factors');
            
            // Conflict intensity (ACLED events, fatalities, displacement)
            $table->float('conflict_score')->nullable()->comment('0-10 scale');
            $table->jsonb('conflict_factors')->nullable()->comment('Event counts, types');
            
            // Health system vulnerability (facility density, disease burden)
            $table->float('health_score')->nullable()->comment('0-10 scale');
            $table->jsonb('health_factors')->nullable()->comment('Facility access, disease');
            
            // Infrastructure vulnerability (WASH, sanitation, social protection)
            $table->float('vulnerability_score')->nullable()->comment('0-10 scale');
            $table->jsonb('vulnerability_factors')->nullable()->comment('WASH, sanitation');
            
            // Operational access (roads, security, humanitarian access)
            $table->float('access_score')->nullable()->comment('0-10 scale, inverse scoring');
            $table->jsonb('access_factors')->nullable()->comment('Constraints detail');
            
            // ================================================================
            // COMPOSITE RISK
            // ================================================================
            
            // Weighted composite score
            $table->float('composite_score')->comment('0-10 weighted average');
            
            // Categorical risk level
            $table->enum('risk_level', ['low', 'medium', 'high', 'critical'])
                  ->comment('Threshold-based classification');
            
            // ================================================================
            // EXPLAINABILITY & CONFIDENCE
            // ================================================================
            
            // Model confidence in assessment
            $table->float('confidence_score')->default(1.0)
                  ->comment('0-1 scale, data completeness');
            
            // Primary risk drivers (for explainability)
            $table->jsonb('primary_drivers')
                  ->comment('["rainfall_anomaly", "conflict_escalation", "low_wash"]');
            
            // Human-readable explanation
            $table->text('explanation')->nullable()
                  ->comment('Why this district is at this risk level');
            
            // ================================================================
            // OPERATIONAL RECOMMENDATIONS
            // ================================================================
            
            // Recommended action level
            $table->enum('recommendation_level', [
                'monitor',      // Low risk - continue monitoring
                'prepare',      // Medium risk - pre-positioning
                'respond',      // High risk - active intervention
                'emergency'     // Critical - immediate response
            ])->comment('Operational action tier');
            
            // Priority interventions
            $table->jsonb('recommended_interventions')->nullable()
                  ->comment('["WASH", "health_screening", "nutrition"]');
            
            // Estimated population at risk
            $table->integer('population_at_risk')->nullable()
                  ->comment('Children under 5 exposed');
            
            // ================================================================
            // CALCULATION METADATA
            // ================================================================
            
            // Scoring method version
            $table->string('calculation_method', 50)->default('v1.0')
                  ->comment('Algorithm version for audit trail');
            
            // Component weights used
            $table->jsonb('scoring_weights')->nullable()
                  ->comment('{"climate": 0.3, "conflict": 0.25, ...}');
            
            // Data sources contributing to score
            $table->jsonb('data_sources')->nullable()
                  ->comment('["CHIRPS", "ACLED", "District_WASH"]');
            
            // Data completeness indicator
            $table->float('data_completeness')->default(1.0)
                  ->comment('0-1 scale, % of expected data present');
            
            // Temporal context
            $table->integer('days_analyzed')->default(30)
                  ->comment('Rolling window for temporal analysis');
            
            // ================================================================
            // AUDIT TRAIL
            // ================================================================
            
            $table->timestamp('calculated_at');
            $table->string('calculated_by', 100)->default('system')
                  ->comment('User or service that triggered assessment');
            
            $table->uuid('triggered_by_event_id')->nullable()
                  ->comment('Event that triggered recalculation');
            
            $table->text('calculation_notes')->nullable()
                  ->comment('Human notes about this assessment');
            
            $table->timestamps();
            
            // ================================================================
            // FOREIGN KEYS
            // ================================================================
            
            $table->foreign('district_id')
                  ->references('id')
                  ->on('districts')
                  ->onDelete('cascade');
            
            // ================================================================
            // INDEXES
            // ================================================================
            
            $table->index('district_id');
            $table->index('assessment_date');
            $table->index('composite_score');
            $table->index('risk_level');
            $table->index('recommendation_level');
            $table->index(['district_id', 'assessment_date']);
            
            // One assessment per district per date
            $table->unique(['district_id', 'assessment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('district_risk_assessments');
    }
};