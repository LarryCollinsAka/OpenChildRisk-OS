<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hazard_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Event identification
            $table->string('event_code')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            
            // Hazard classification
            $table->foreignUuid('hazard_type_id')->constrained('hazard_types')->cascadeOnDelete();
            $table->foreignUuid('hazard_category_id')->nullable()->constrained('hazard_categories')->nullOnDelete();
            
            // Location
            $table->foreignUuid('district_id')->nullable()->constrained('districts')->nullOnDelete();
            $table->foreignUuid('state_id')->nullable()->constrained('states')->nullOnDelete();
            $table->foreignUuid('country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->json('affected_areas')->nullable(); // Array of district IDs
            
            // Timing
            $table->timestamp('detected_at');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamp('peak_at')->nullable();
            $table->integer('duration_hours')->nullable();
            
            // Severity & Impact
            $table->string('severity')->default('medium'); // low, medium, high, critical
            $table->decimal('severity_score', 4, 2)->nullable(); // 0-10
            $table->integer('affected_population')->nullable();
            $table->integer('affected_children_under5')->nullable();
            $table->integer('displaced_population')->nullable();
            $table->integer('casualties')->nullable();
            
            // Event specifics (depends on hazard type)
            $table->json('event_data')->nullable(); // Rainfall mm, temperature, conflict casualties, etc.
            
            // Data source & validation
            $table->foreignUuid('data_source_id')->nullable()->constrained('data_sources')->nullOnDelete();
            $table->foreignUuid('ingestion_job_id')->nullable()->constrained('data_ingestion_jobs')->nullOnDelete();
            $table->boolean('verified')->default(false);
            $table->foreignUuid('verified_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            
            // Status
            $table->string('status')->default('active'); // active, monitoring, resolved, archived
            $table->text('status_notes')->nullable();
            
            // Alert generation
            $table->boolean('alert_generated')->default(false);
            $table->foreignUuid('alert_id')->nullable()->constrained('alerts')->nullOnDelete();
            
            // Metadata
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('event_code');
            $table->index('hazard_type_id');
            $table->index('district_id');
            $table->index('severity');
            $table->index('status');
            $table->index('detected_at');
            $table->index(['started_at', 'ended_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hazard_events');
    }
};