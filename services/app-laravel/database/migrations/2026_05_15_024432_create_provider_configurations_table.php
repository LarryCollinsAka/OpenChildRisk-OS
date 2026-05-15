<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Provider Configurations
     * 
     * Store provider-specific configuration that can be updated via UI.
     * Examples: API endpoints, file IDs, credentials, mappings.
     */
    public function up(): void
    {
        Schema::create('provider_configurations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Provider identification
            $table->uuid('provider_source_id');
            $table->string('config_key')
                  ->comment('Configuration key: file_id_2024, api_endpoint, etc');
            $table->text('config_value')
                  ->comment('Configuration value: can be JSON, string, number');
            $table->string('value_type', 50)
                  ->default('string')
                  ->comment('string, json, integer, url');
            
            // Metadata
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_editable_via_ui')->default(true);
            
            // Audit
            $table->uuid('updated_by_user_id')->nullable();
            $table->timestamp('last_verified_at')->nullable();
            
            $table->timestamps();
            
            // Foreign key
            $table->foreign('provider_source_id')
                  ->references('id')
                  ->on('conflict_provider_sources')
                  ->onDelete('cascade');
            
            // Unique constraint
            $table->unique(['provider_source_id', 'config_key']);
            
            // Indexes
            $table->index('config_key');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_configurations');
    }
};