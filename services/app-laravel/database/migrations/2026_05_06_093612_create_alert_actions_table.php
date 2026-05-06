<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Alert actions - audit trail of actions taken in response to alerts.
     * 
     * Critical for:
     * - Accountability (who did what when)
     * - Impact tracking (did the action work?)
     * - Learning (what actions are most effective)
     * - Reporting to donors
     * 
     * Example:
     * Alert: "HIGH cholera risk in Mora"
     * Actions:
     * - 2026-03-15 10:00: Pre-positioned 500 ORS sachets
     * - 2026-03-15 14:30: Deployed 2 CHWs to district
     * - 2026-03-16 09:00: Activated surveillance system
     */
    public function up(): void
    {
        Schema::create('alert_actions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('alert_id');
            
            // What action was taken?
            $table->enum('action_type', [
                'supplies_prepositioned',
                'staff_deployed',
                'surveillance_activated',
                'community_mobilization',
                'facility_notified',
                'escalation',
                'vaccination_campaign',
                'water_treatment',
                'information_dissemination',
                'coordination_meeting',
                'assessment_conducted',
                'other'
            ]);
            
            $table->text('action_description');
            
            // Who took the action?
            $table->uuid('user_id')->nullable();
            $table->uuid('organization_id')->nullable();
            
            // Where was the action taken?
            $table->uuid('district_id')->nullable();
            $table->uuid('facility_id')->nullable();
            
            // When
            $table->timestamp('action_taken_at');
            
            // Impact
            $table->integer('children_reached')->nullable();
            $table->decimal('cost_usd', 12, 2)->nullable();
            
            // Action status
            $table->enum('status', [
                'planned',
                'in_progress',
                'completed',
                'cancelled',
                'failed'
            ])->default('completed');
            
            // Evidence/documentation
            $table->jsonb('evidence')->nullable(); // photo URLs, document IDs
            $table->text('notes')->nullable();
            
            // Metadata for additional details
            $table->jsonb('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign keys
            $table->foreign('alert_id')
                  ->references('id')
                  ->on('alerts')
                  ->onDelete('cascade');
            
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
            
            $table->foreign('organization_id')
                  ->references('id')
                  ->on('organizations')
                  ->onDelete('set null');
            
            $table->foreign('district_id')
                  ->references('id')
                  ->on('districts')
                  ->onDelete('set null');
            
            $table->foreign('facility_id')
                  ->references('id')
                  ->on('facilities')
                  ->onDelete('set null');
            
            // Indexes
            $table->index('alert_id');
            $table->index('action_type');
            $table->index('user_id');
            $table->index('organization_id');
            $table->index('district_id');
            $table->index('action_taken_at');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alert_actions');
    }
};