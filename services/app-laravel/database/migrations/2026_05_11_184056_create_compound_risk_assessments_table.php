<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compound_risk_assessments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Assessment identification
            $table->string('assessment_code')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            
            // Location
            $table->foreignUuid('district_id')->constrained('districts')->cascadeOnDelete();
            
            // Assessment period
            $table->date('assessment_date');
            $table->date('valid_from');
            $table->date('valid_until')->nullable();
            
            // Risk scores
            $table->decimal('overall_risk_score', 4, 2); // 0-10
            $table->string('risk_level'); // low, medium, high, critical
            $table->decimal('climate_risk_score', 4, 2)->nullable();
            $table->decimal('disease_risk_score', 4, 2)->nullable();
            $table->decimal('conflict_risk_score', 4, 2)->nullable();
            $table->decimal('infrastructure_risk_score', 4, 2)->nullable();
            $table->decimal('nutrition_risk_score', 4, 2)->nullable();
            
            // Population impact
            $table->integer('vulnerable_population')->nullable();
            $table->integer('vulnerable_children_under5')->nullable();
            $table->integer('displaced_population')->nullable();
            $table->integer('malnourished_children')->nullable();
            
            // Contributing factors
            $table->json('hazard_events')->nullable();
            $table->json('contributing_indicators')->nullable();
            $table->json('vulnerability_factors')->nullable();
            
            // Risk cascades
            $table->json('cascade_pathways')->nullable();
            $table->text('primary_drivers')->nullable();
            
            // Confidence & methodology
            $table->decimal('confidence_level', 3, 2)->default(0.80);
            $table->string('methodology')->nullable();
            $table->string('model_version')->nullable();
            
            // Recommendations
            $table->json('recommended_interventions')->nullable();
            $table->text('priority_actions')->nullable();
            $table->integer('estimated_response_cost')->nullable();
            
            // Assessment metadata
            $table->foreignUuid('assessed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('organization_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->boolean('published')->default(false);
            $table->timestamp('published_at')->nullable();
            
            // Tracking
            $table->string('status')->default('draft');
            $table->uuid('superseded_by_id')->nullable(); // ← JUST A UUID COLUMN, NO FOREIGN KEY YET
            
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('assessment_code');
            $table->index('district_id');
            $table->index('assessment_date');
            $table->index('risk_level');
            $table->index('overall_risk_score');
            $table->index('status');
            $table->index(['valid_from', 'valid_until']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compound_risk_assessments');
    }
};