<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Conflict Provider Sources - Source Intelligence
     * 
     * Track metadata about each conflict data provider.
     * Enables source confidence scoring and provider management.
     */
    public function up(): void
    {
        Schema::create('conflict_provider_sources', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Provider identification
            $table->string('code', 50)->unique()
                  ->comment('Provider code: ACLED, ICEWS, GDELT');
            $table->string('name');
            $table->text('description')->nullable();
            
            // Provider characteristics
            $table->enum('provider_type', ['premium', 'open', 'government', 'research'])
                  ->default('open');
            $table->decimal('reliability_score', 3, 2)->default(0.75)
                  ->comment('Base confidence 0-1');
            $table->string('update_frequency', 50)
                  ->comment('daily, weekly, realtime');
            $table->string('license_type', 100)->nullable();
            $table->integer('historical_depth_years')->nullable()
                  ->comment('Years of historical data');
            
            // API configuration
            $table->boolean('api_enabled')->default(false);
            $table->string('api_base_url')->nullable();
            $table->string('api_auth_type', 50)->nullable()
                  ->comment('oauth, apikey, none');
            $table->boolean('requires_institutional_access')->default(false);
            
            // Coverage
            $table->json('geographic_coverage')->nullable()
                  ->comment('Countries/regions covered');
            $table->json('event_types_covered')->nullable()
                  ->comment('Types of events tracked');
            
            // Ingestion tracking
            $table->timestamp('last_successful_ingestion')->nullable();
            $table->timestamp('last_attempted_ingestion')->nullable();
            $table->integer('total_events_ingested')->default(0);
            $table->integer('events_last_30_days')->default(0);
            
            // Status
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('code');
            $table->index('provider_type');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conflict_provider_sources');
    }
};