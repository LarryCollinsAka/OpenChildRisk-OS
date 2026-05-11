<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create priority_targets table.
     * 
     * Strategic targeting decisions for resource allocation.
     * 
     * EXAMPLE:
     * "Mora district prioritized for cholera response due to:
     * - Low sanitation (38%)
     * - Recent flooding
     * - 12,000 under-5 children at risk
     * Priority valid until 2026-12-31"
     * 
     * This becomes the operational decision engine.
     */
    public function up(): void
    {
        Schema::create('priority_targets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // What & where
            $table->uuid('district_id');
            $table->uuid('hazard_type_id')->nullable(); // Specific hazard (cholera, flood)
            $table->uuid('population_group_id')->nullable(); // Specific group (under-5, SAM)
            $table->uuid('organization_id')->nullable(); // Which org set this priority
            
            // Priority details
            $table->string('name', 200); // "Mora Cholera Response Priority"
            $table->text('description')->nullable();
            $table->text('rationale'); // Why is this a priority?
            
            // Scoring
            $table->decimal('priority_score', 5, 2); // 0.00 to 100.00
            $table->enum('priority_level', [
                'critical',
                'high',
                'medium',
                'low'
            ])->default('medium');
            
            // Timeframe
            $table->date('valid_from');
            $table->date('valid_until')->nullable(); // null = indefinite
            
            // Status
            $table->enum('status', [
                'active',
                'expired',
                'achieved',
                'cancelled'
            ])->default('active');
            
            // Target metrics (what are we trying to achieve?)
            $table->jsonb('target_indicators')->nullable(); // {"vaccination_coverage_dpt3": 80, "sam_prevalence": 3}
            $table->integer('target_beneficiaries')->nullable(); // How many children to reach
            
            // Resources allocated
            $table->uuid('assigned_program_id')->nullable(); // Which program responds
            $table->integer('field_workers_assigned')->nullable();
            $table->decimal('budget_allocated', 15, 2)->nullable();
            
            // Tracking
            $table->date('last_reviewed_at')->nullable();
            $table->uuid('reviewed_by_user_id')->nullable();
            $table->text('review_notes')->nullable();
            
            // Metadata
            $table->jsonb('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign keys
            $table->foreign('district_id')
                  ->references('id')
                  ->on('districts')
                  ->onDelete('cascade');
            
            $table->foreign('hazard_type_id')
                  ->references('id')
                  ->on('hazard_types')
                  ->onDelete('set null');
            
            $table->foreign('population_group_id')
                  ->references('id')
                  ->on('population_groups')
                  ->onDelete('set null');
            
            $table->foreign('organization_id')
                  ->references('id')
                  ->on('organizations')
                  ->onDelete('set null');
            
            $table->foreign('assigned_program_id')
                  ->references('id')
                  ->on('programs')
                  ->onDelete('set null');
            
            $table->foreign('reviewed_by_user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
            
            // Indexes
            $table->index('district_id');
            $table->index('hazard_type_id');
            $table->index('population_group_id');
            $table->index('priority_level');
            $table->index('status');
            $table->index('valid_from');
            $table->index('valid_until');
            $table->index(['district_id', 'status', 'priority_level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('priority_targets');
    }
};