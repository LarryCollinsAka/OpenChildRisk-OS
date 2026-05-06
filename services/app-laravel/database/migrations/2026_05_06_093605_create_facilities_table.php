<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Facilities - health posts, clinics, hospitals, warehouses.
     * 
     * Critical for:
     * - Alert routing (which facility to notify)
     * - Supply pre-positioning
     * - Capacity tracking
     * - Response coordination
     */
    public function up(): void
    {
        Schema::create('facilities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Basic info
            $table->string('name', 100);
            $table->string('code', 50)->unique()->nullable();
            $table->text('description')->nullable();
            
            // Facility type
            $table->enum('type', [
                'hospital',
                'health_center',
                'health_post',
                'clinic',
                'warehouse',
                'office',
                'school',
                'water_point',
                'other'
            ]);
            
            // Location
            $table->uuid('city_id')->nullable(); // canonical geography
            $table->uuid('district_id')->nullable(); // operational geography
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->geometry('geom', 'POINT', 4326)->nullable();
            $table->text('address')->nullable();
            
            // Ownership
            $table->uuid('organization_id')->nullable();
            
            // Contact
            $table->string('contact_person', 100)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email', 100)->nullable();
            
            // Capacity
            $table->integer('bed_capacity')->nullable();
            $table->integer('staff_count')->nullable();
            $table->boolean('has_cold_chain')->default(false);
            $table->boolean('has_ambulance')->default(false);
            $table->boolean('has_laboratory')->default(false);
            
            // Operational status
            $table->enum('operational_status', [
                'fully_operational',
                'partially_operational',
                'non_operational',
                'under_construction'
            ])->default('fully_operational');
            
            // Status
            $table->boolean('active')->default(true);
            
            // Metadata
            $table->jsonb('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign keys
            $table->foreign('city_id')
                  ->references('id')
                  ->on('cities')
                  ->onDelete('set null');
            
            $table->foreign('district_id')
                  ->references('id')
                  ->on('districts')
                  ->onDelete('set null');
            
            $table->foreign('organization_id')
                  ->references('id')
                  ->on('organizations')
                  ->onDelete('set null');
            
            // Indexes
            $table->index('name');
            $table->index('code');
            $table->index('type');
            $table->index('city_id');
            $table->index('district_id');
            $table->index('organization_id');
            $table->index(['latitude', 'longitude']);
            $table->index('operational_status');
            $table->index('active');
        });
        
        // Add spatial index for geometry
        DB::statement('CREATE INDEX facilities_geom_idx ON facilities USING GIST (geom)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facilities');
    }
};