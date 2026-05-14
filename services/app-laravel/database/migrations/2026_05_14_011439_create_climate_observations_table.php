<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Climate Observations - Time-series climate data storage
     * 
     * Purpose: Store satellite-derived climate measurements (CHIRPS rainfall)
     * for temporal analysis and anomaly detection.
     * 
     * Data Sources:
     * - CHIRPS (Climate Hazards Group InfraRed Precipitation with Station data)
     * - NASA POWER (future: temperature, humidity)
     * - ERA5 (future: climate reanalysis)
     * 
     * Use Cases:
     * - Rainfall anomaly detection
     * - Drought monitoring
     * - Climate risk scoring
     * - ML training data
     * - Historical trend analysis
     */
    public function up(): void
    {
        Schema::create('climate_observations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Geographic context
            $table->uuid('district_id');
            
            // Data provenance
            $table->uuid('data_source_id')->nullable();
            $table->uuid('ingestion_job_id')->nullable();
            
            // Temporal
            $table->date('observation_date');
            
            // ================================================================
            // CLIMATE MEASUREMENTS
            // ================================================================
            
            // Rainfall (mm)
            $table->decimal('rainfall_mm', 10, 2)->nullable()
                  ->comment('Daily rainfall in millimeters');
            
            // Temperature (°C) - for future use
            $table->decimal('temperature_min_c', 5, 2)->nullable()
                  ->comment('Minimum temperature Celsius');
            $table->decimal('temperature_max_c', 5, 2)->nullable()
                  ->comment('Maximum temperature Celsius');
            $table->decimal('temperature_avg_c', 5, 2)->nullable()
                  ->comment('Average temperature Celsius');
            
            // Humidity (%) - for future use
            $table->decimal('humidity_pct', 5, 2)->nullable()
                  ->comment('Relative humidity percentage');
            
            // ================================================================
            // ANOMALY DETECTION
            // ================================================================
            
            // Rainfall anomaly (percentage deviation from normal)
            $table->decimal('rainfall_anomaly_pct', 10, 2)->nullable()
                  ->comment('% deviation from historical average');
            
            // Historical baseline
            $table->decimal('rainfall_historical_avg', 10, 2)->nullable()
                  ->comment('Historical average for this date/location');
            
            // Z-score (standard deviations from mean)
            $table->decimal('rainfall_zscore', 6, 3)->nullable()
                  ->comment('Statistical z-score for anomaly detection');
            
            // ================================================================
            // QUALITY METRICS
            // ================================================================
            
            // Data quality flag
            $table->enum('quality', ['excellent', 'good', 'fair', 'poor', 'estimated'])
                  ->default('good')
                  ->comment('Data quality assessment');
            
            // Confidence score (0-1)
            $table->decimal('confidence', 3, 2)->default(1.00)
                  ->comment('Measurement confidence 0-1');
            
            // Spatial resolution (km)
            $table->decimal('spatial_resolution_km', 6, 2)->nullable()
                  ->comment('Pixel size in kilometers');
            
            // ================================================================
            // METADATA
            // ================================================================
            
            // Additional context
            $table->jsonb('metadata')->nullable()
                  ->comment('Source-specific metadata');
            
            $table->timestamps();
            
            // ================================================================
            // FOREIGN KEYS
            // ================================================================
            
            $table->foreign('district_id')
                  ->references('id')
                  ->on('districts')
                  ->onDelete('cascade');
            
            $table->foreign('data_source_id')
                  ->references('id')
                  ->on('data_sources')
                  ->onDelete('set null');
            
            $table->foreign('ingestion_job_id')
                  ->references('id')
                  ->on('data_ingestion_jobs')
                  ->onDelete('set null');
            
            // ================================================================
            // INDEXES
            // ================================================================
            
            $table->index('district_id');
            $table->index('observation_date');
            $table->index('data_source_id');
            $table->index(['district_id', 'observation_date']);
            $table->index('rainfall_mm');
            $table->index('rainfall_anomaly_pct');
            
            // One observation per district per date per source
            $table->unique(['district_id', 'observation_date', 'data_source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('climate_observations');
    }
};