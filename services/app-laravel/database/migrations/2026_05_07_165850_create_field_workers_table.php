<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create field_workers table.
     * 
     * Field workers are CHWs, nurses, and other frontline responders.
     * 
     * RELATIONSHIP WITH USERS:
     * - user_id (nullable): Link to users table if field worker has login
     * - Field worker can exist WITHOUT user (imported from external system)
     * - User can exist WITHOUT field worker (admins, managers)
     */
    public function up(): void
    {
        Schema::create('field_workers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Relationships
            $table->uuid('organization_id')->nullable();
            $table->uuid('user_id')->nullable(); // ← Links to users if they have login
            $table->uuid('worker_type_id')->nullable();
            
            // Identity
            $table->string('external_id', 100)->nullable(); // ID from external system
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('title', 50)->nullable(); // Mr., Ms., Dr.
            
            // Specializations (e.g., ["malaria", "vaccination", "wash"])
            $table->jsonb('specializations')->default('[]');
            
            // Contact
            $table->string('phone', 50);
            $table->string('phone_country_code', 5)->nullable();
            $table->boolean('phone_verified')->default(false);
            $table->string('email', 255)->nullable();
            $table->string('whatsapp_number', 50)->nullable();
            
            // Assignment
            $table->uuid('primary_district_id')->nullable();
            $table->uuid('primary_facility_id')->nullable();
            $table->geometry('coverage_area_geom', 'polygon', 4326)->nullable();
            
            // Status
            $table->string('status', 50)->default('active'); // active, inactive, training, suspended
            $table->date('hire_date')->nullable();
            $table->date('training_completed_at')->nullable();
            
            // Alert preferences
            $table->boolean('receive_sms_alerts')->default(true);
            $table->boolean('receive_whatsapp_alerts')->default(true);
            $table->string('alert_priority_threshold', 50)->default('medium');
            
            // Metadata
            $table->text('notes')->nullable();
            $table->jsonb('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign keys
            $table->foreign('organization_id')
                  ->references('id')
                  ->on('organizations')
                  ->onDelete('cascade');
            
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null'); // If user deleted, field worker remains (orphaned)
            
            $table->foreign('worker_type_id')
                  ->references('id')
                  ->on('worker_types')
                  ->onDelete('restrict');
            
            $table->foreign('primary_district_id')
                  ->references('id')
                  ->on('districts')
                  ->onDelete('set null');
            
            $table->foreign('primary_facility_id')
                  ->references('id')
                  ->on('facilities')
                  ->onDelete('set null');
            
            // Indexes
            $table->index('organization_id');
            $table->index('user_id');
            $table->index('worker_type_id');
            $table->index('primary_district_id');
            $table->index('phone');
            $table->index('status');
            $table->index('external_id');
        });

        // GIN index for specializations array
        DB::statement('CREATE INDEX idx_field_workers_specializations ON field_workers USING gin(specializations)');
        
        // Spatial index for coverage area
        DB::statement('CREATE INDEX idx_field_workers_coverage_geom ON field_workers USING gist(coverage_area_geom)');
    }

    public function down(): void
    {
        Schema::dropIfExists('field_workers');
    }
};