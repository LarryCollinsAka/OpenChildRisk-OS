<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create data_ingestion_jobs table.
     * 
     * Tracks every sync/import operation from data sources.
     * Provides audit trail and enables troubleshooting.
     * 
     * CRITIQUE: This becomes the operational ingestion audit layer.
     */
    public function up(): void
    {
        Schema::create('data_ingestion_jobs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('data_source_id');

            // Job details
            $table->enum('job_type', [
                'sync',         // Regular scheduled sync
                'backfill',     // Historical data import
                'validation',   // Data quality check
                'manual'        // Manual trigger
            ]);

            $table->enum('status', [
                'pending',
                'running',
                'completed',
                'failed',
                'partial',
                'cancelled'
            ])->default('pending');

            // Execution timeline
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('duration_seconds')->nullable(); // CRITIQUE: Performance tracking

            // Results
            $table->integer('records_processed')->default(0);
            $table->integer('records_created')->default(0);
            $table->integer('records_updated')->default(0);
            $table->integer('records_skipped')->default(0);
            $table->integer('records_failed')->default(0);

            // Data range (for time-series data)
            $table->date('data_start_date')->nullable();
            $table->date('data_end_date')->nullable();

            // Errors
            $table->text('error_message')->nullable();
            $table->jsonb('error_details')->nullable(); // Structured error info
            $table->jsonb('failed_records')->nullable(); // Sample of failed records

            // Validation
            $table->jsonb('validation_warnings')->nullable();
            $table->integer('duplicate_count')->default(0);
            $table->integer('invalid_count')->default(0);

            // Metadata (CRITIQUE: Humanitarian data evolves constantly)
            $table->jsonb('job_config')->nullable(); // Filters, parameters used
            $table->jsonb('metadata')->nullable();

            // Triggered by
            $table->uuid('triggered_by_user_id')->nullable();
            $table->string('trigger_source', 50)->nullable(); // cron, api, ui, cli

            $table->timestamps();

            // Foreign keys
            $table->foreign('data_source_id')
                ->references('id')
                ->on('data_sources')
                ->onDelete('cascade');

            $table->foreign('triggered_by_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            // Indexes
            $table->index('data_source_id');
            $table->index('status');
            $table->index('started_at');
            $table->index('job_type');
            $table->index(['data_source_id', 'status']);
            $table->index(['data_source_id', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_ingestion_jobs');
    }
};
