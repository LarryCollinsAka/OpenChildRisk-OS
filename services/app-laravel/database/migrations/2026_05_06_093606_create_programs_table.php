<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Programs - humanitarian interventions and initiatives.
     * 
     * Examples:
     * - Cholera Vaccination Campaign
     * - Nutrition Screening Q1
     * - Emergency WASH Response
     * - School Feeding Program
     * 
     * Programs:
     * - Respond to specific hazards
     * - Deploy to specific districts
     * - Have start/end dates
     * - Owned by organizations
     */
    public function up(): void
    {
        Schema::create('programs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Basic info
            $table->string('name', 200);
            $table->string('code', 50)->unique()->nullable();
            $table->text('description')->nullable();
            
            // Program classification
            $table->enum('type', [
                'vaccination',
                'nutrition',
                'wash',
                'health',
                'education',
                'protection',
                'emergency_response',
                'preparedness',
                'other'
            ]);
            
            // Ownership
            $table->uuid('organization_id');
            
            // What hazard(s) does this program address?
            $table->uuid('primary_hazard_id')->nullable();
            
            // Geographic scope
            $table->uuid('country_id')->nullable();
            $table->enum('scope', [
                'national',
                'regional',
                'district',
                'facility',
                'multi_country'
            ])->default('district');
            
            // Timeline
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            
            // Budget and targets
            $table->decimal('budget_usd', 15, 2)->nullable();
            $table->integer('target_children')->nullable();
            $table->integer('reached_children')->nullable();
            
            // Program status
            $table->enum('status', [
                'planned',
                'active',
                'suspended',
                'completed',
                'cancelled'
            ])->default('planned');
            
            // Contact
            $table->string('program_manager', 100)->nullable();
            $table->string('contact_email', 100)->nullable();
            $table->string('contact_phone', 50)->nullable();
            
            // Status
            $table->boolean('active')->default(true);
            
            // Metadata for additional program details
            $table->jsonb('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign keys
            $table->foreign('organization_id')
                  ->references('id')
                  ->on('organizations')
                  ->onDelete('cascade');
            
            $table->foreign('primary_hazard_id')
                  ->references('id')
                  ->on('hazard_types')
                  ->onDelete('set null');
            
            $table->foreign('country_id')
                  ->references('id')
                  ->on('countries')
                  ->onDelete('set null');
            
            // Indexes
            $table->index('name');
            $table->index('code');
            $table->index('type');
            $table->index('organization_id');
            $table->index('primary_hazard_id');
            $table->index('country_id');
            $table->index('status');
            $table->index(['start_date', 'end_date']);
            $table->index('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};