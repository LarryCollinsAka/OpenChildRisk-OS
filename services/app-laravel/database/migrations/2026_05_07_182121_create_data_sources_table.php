<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create data_sources table.
     * 
     * Tracks external data providers (DHIS2, CHIRPS, WHO, CSV uploads, etc.)
     * Each source can be synced via API, file upload, or manual entry.
     * 
     * PROVENANCE: Every indicator value will link back to its data source.
     */
    public function up(): void
    {
        Schema::create('data_sources', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('code', 100)->unique();
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->uuid('organization_id')->nullable(); // Which org manages this source

            // Source type
            $table->enum('source_type', [
                'api',
                'file_upload',
                'manual_entry',
                'web_scraping',
                'ftp',
                'email',
                'satellite'
            ]);

            // Provider info
            $table->string('provider', 255)->nullable(); // UNICEF, WHO, CHIRPS, etc.

            // API configuration (for source_type = 'api')
            $table->text('api_url')->nullable();
            $table->enum('api_auth_type', [
                'none',
                'bearer',
                'basic',
                'oauth2',
                'api_key'
            ])->nullable();
            $table->text('api_credentials_encrypted')->nullable(); // Encrypted JSON

            // Data specifications
            $table->string('data_format', 50)->nullable(); // json, csv, xml, geojson
            $table->enum('update_frequency', [
                'realtime',
                'hourly',
                'daily',
                'weekly',
                'monthly',
                'quarterly',
                'on_demand'
            ])->nullable();

            // Geographic coverage
            $table->jsonb('coverage_countries')->nullable(); // ["CM", "NG", "TD"]
            $table->jsonb('coverage_regions')->nullable(); // Which admin levels

            // Sync status
            $table->boolean('active')->default(true);
            $table->timestamp('last_sync_at')->nullable();
            $table->string('last_sync_status', 50)->nullable(); // success, failed, partial
            $table->text('last_sync_error')->nullable();
            $table->integer('consecutive_failures')->default(0);

            // Quality & reliability metrics (CRITIQUE: Track data trustworthiness)
            $table->decimal('reliability_score', 3, 2)->default(1.00); // 0.00 to 1.00
            $table->decimal('data_quality_score', 3, 2)->nullable(); // 0.00 to 1.00
            $table->integer('total_records_synced')->default(0);

            // Configuration
            $table->jsonb('sync_config')->nullable(); // Cron schedule, filters, mappings
            $table->boolean('auto_sync')->default(false); // Auto-sync on schedule

            // Metadata
            $table->text('documentation_url')->nullable();
            $table->text('contact_info')->nullable();
            $table->jsonb('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->onDelete('set null');

            // Indexes
            $table->index('code');
            $table->index('source_type');
            $table->index('active');
            $table->index('last_sync_at');
            $table->index('reliability_score');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_sources');
    }
};
