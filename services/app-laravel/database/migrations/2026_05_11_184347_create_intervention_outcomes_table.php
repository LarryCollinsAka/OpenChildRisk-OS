<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('intervention_outcomes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Link to intervention
            $table->foreignUuid('intervention_id')->constrained('interventions')->cascadeOnDelete();
            
            // Outcome identification
            $table->string('outcome_code')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            
            // Measurement
            $table->date('measured_date');
            $table->string('measurement_type'); // baseline, midline, endline, follow_up
            $table->integer('days_since_intervention_start')->nullable();
            
            // Impact metrics
            $table->json('outcome_indicators')->nullable(); // Array of {indicator_id, baseline, endline, change}
            $table->decimal('effectiveness_score', 4, 2)->nullable(); // 0-10
            $table->string('impact_level')->nullable(); // minimal, moderate, significant, transformative
            
            // Specific outcomes (depends on intervention type)
            $table->integer('children_vaccinated')->nullable();
            $table->integer('cases_prevented')->nullable();
            $table->integer('lives_saved_estimate')->nullable();
            $table->decimal('disease_incidence_reduction', 5, 2)->nullable(); // Percentage
            $table->decimal('malnutrition_reduction', 5, 2)->nullable();
            
            // Risk reduction
            $table->decimal('risk_score_before', 4, 2)->nullable();
            $table->decimal('risk_score_after', 4, 2)->nullable();
            $table->decimal('risk_reduction', 4, 2)->nullable();
            
            // Cost effectiveness
            $table->decimal('cost_per_beneficiary', 10, 2)->nullable();
            $table->decimal('cost_per_outcome', 10, 2)->nullable(); // e.g., cost per case prevented
            
            // Beneficiary feedback
            $table->integer('beneficiaries_surveyed')->nullable();
            $table->decimal('satisfaction_score', 3, 2)->nullable(); // 0-5
            $table->json('feedback_summary')->nullable();
            
            // Challenges & lessons
            $table->json('challenges_encountered')->nullable();
            $table->json('lessons_learned')->nullable();
            $table->json('recommendations')->nullable();
            
            // Data quality
            $table->foreignUuid('data_source_id')->nullable()->constrained('data_sources')->nullOnDelete();
            $table->decimal('data_quality_score', 3, 2)->nullable(); // 0-1
            $table->string('verification_method')->nullable();
            $table->boolean('verified')->default(false);
            
            // Assessment
            $table->foreignUuid('assessed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('organization_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->timestamp('assessed_at')->nullable();
            
            // Reporting
            $table->boolean('published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->string('report_url')->nullable();
            
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('intervention_id');
            $table->index('outcome_code');
            $table->index('measured_date');
            $table->index('measurement_type');
            $table->index('impact_level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intervention_outcomes');
    }
};