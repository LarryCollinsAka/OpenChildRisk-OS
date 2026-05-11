<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interventions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Intervention identification
            $table->string('intervention_code')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            
            // Classification
            $table->string('intervention_type'); // vaccination, nutrition, WASH, health, emergency_response
            $table->string('intervention_category')->nullable(); // preventive, curative, emergency
            
            // Responding to
            $table->foreignUuid('hazard_event_id')->nullable()->constrained('hazard_events')->nullOnDelete();
            $table->foreignUuid('risk_assessment_id')->nullable()->constrained('compound_risk_assessments')->nullOnDelete();
            $table->foreignUuid('priority_target_id')->nullable()->constrained('priority_targets')->nullOnDelete();
            $table->json('triggered_by_alerts')->nullable(); // Array of alert IDs
            
            // Location
            $table->foreignUuid('district_id')->constrained('districts')->cascadeOnDelete();
            $table->json('target_areas')->nullable(); // Specific villages/zones
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            // Organization & program
            $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignUuid('program_id')->nullable()->constrained('programs')->nullOnDelete();
            $table->foreignUuid('facility_id')->nullable()->constrained('facilities')->nullOnDelete();
            
            // Target beneficiaries
            $table->foreignUuid('population_group_id')->nullable()->constrained('population_groups')->nullOnDelete();
            $table->integer('target_beneficiaries')->nullable();
            $table->integer('target_children_under5')->nullable();
            $table->integer('target_women')->nullable();
            $table->json('eligibility_criteria')->nullable();
            
            // Timing
            $table->date('planned_start_date');
            $table->date('planned_end_date')->nullable();
            $table->date('actual_start_date')->nullable();
            $table->date('actual_end_date')->nullable();
            $table->integer('planned_duration_days')->nullable();
            
            // Resources
            $table->decimal('budget_allocated', 12, 2)->nullable();
            $table->decimal('budget_spent', 12, 2)->nullable();
            $table->json('resources_deployed')->nullable(); // Supplies, equipment
            $table->integer('field_workers_assigned')->nullable();
            
            // Implementation
            $table->string('implementation_approach')->nullable();
            $table->json('activities')->nullable(); // List of intervention activities
            $table->json('implementing_partners')->nullable();
            
            // Status & progress
            $table->string('status')->default('planned'); // planned, approved, in_progress, completed, suspended, cancelled
            $table->integer('completion_percentage')->default(0);
            $table->text('status_notes')->nullable();
            
            // Actual reach
            $table->integer('actual_beneficiaries')->nullable();
            $table->integer('actual_children_reached')->nullable();
            $table->integer('actual_women_reached')->nullable();
            
            // Approval workflow
            $table->foreignUuid('requested_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            
            // Coordination
            $table->foreignUuid('lead_field_worker_id')->nullable()->constrained('field_workers')->nullOnDelete();
            $table->json('team_members')->nullable(); // Array of field_worker IDs
            
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('intervention_code');
            $table->index('intervention_type');
            $table->index('district_id');
            $table->index('organization_id');
            $table->index('status');
            $table->index('planned_start_date');
            $table->index(['actual_start_date', 'actual_end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interventions');
    }
};