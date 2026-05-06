<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Program deployments - where programs are active.
     * 
     * Links programs to specific locations.
     * Time-bound (programs move, expand, contract).
     * 
     * Example:
     * "Cholera Vaccination Campaign 2026"
     * deployed to:
     * - Mora district (2026-03-01 to 2026-03-31)
     * - Maroua district (2026-03-15 to 2026-04-15)
     * 
     * Critical for:
     * - Alert routing (which programs are in affected area)
     * - Coverage tracking
     * - Resource allocation
     */
    public function up(): void
    {
        Schema::create('program_deployments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('program_id');
            
            // Where is the program deployed?
            $table->uuid('district_id')->nullable();
            $table->uuid('facility_id')->nullable();
            
            // Time validity (deployments change)
            $table->timestamp('deployed_from')->nullable();
            $table->timestamp('deployed_to')->nullable();
            
            // Deployment details
            $table->integer('staff_count')->nullable();
            $table->integer('target_beneficiaries')->nullable();
            $table->integer('reached_beneficiaries')->nullable();
            
            // Deployment status
            $table->enum('status', [
                'planned',
                'active',
                'paused',
                'completed',
                'cancelled'
            ])->default('planned');
            
            // Metadata for deployment-specific details
            $table->jsonb('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign keys
            $table->foreign('program_id')
                  ->references('id')
                  ->on('programs')
                  ->onDelete('cascade');
            
            $table->foreign('district_id')
                  ->references('id')
                  ->on('districts')
                  ->onDelete('cascade');
            
            $table->foreign('facility_id')
                  ->references('id')
                  ->on('facilities')
                  ->onDelete('set null');
            
            // Indexes
            $table->index('program_id');
            $table->index('district_id');
            $table->index('facility_id');
            $table->index('status');
            $table->index(['deployed_from', 'deployed_to']);
            
            // Unique constraint: one program per district per time period
            $table->unique(['program_id', 'district_id', 'deployed_from']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_deployments');
    }
};