<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * District geo mappings - links operational districts to canonical geography.
     * 
     * CRITICAL LAYER for multi-organization support.
     * 
     * Enables:
     * - Multiple organizations with different boundaries
     * - Flexible district definitions
     * - No duplication of geo data
     * 
     * Example:
     * District "Mora Health Zone" maps to:
     * - city: Mora (geo_type='city', geo_id=uuid)
     * - state: Far North (geo_type='state', geo_id=uuid)
     */
    public function up(): void
    {
        Schema::create('district_geo_mappings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('district_id');
            
            // What type of geo entity does this map to?
            $table->enum('geo_type', ['country', 'state', 'city']);
            
            // UUID of the geo entity (polymorphic)
            $table->uuid('geo_id');
            
            // Who defined this mapping?
            $table->uuid('organization_id')->nullable();
            $table->string('source', 50)->nullable(); // unicef, who, manual
            
            // Time validity (mappings can change)
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_to')->nullable();
            
            // Metadata for additional context
            $table->jsonb('metadata')->nullable();
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('district_id')
                  ->references('id')
                  ->on('districts')
                  ->onDelete('cascade');
            
            // Note: geo_id is polymorphic - references countries/states/cities
            // Cannot add FK constraint
            
            // Indexes
            $table->index('district_id');
            $table->index(['geo_type', 'geo_id']);
            $table->index('organization_id');
            $table->index(['valid_from', 'valid_to']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('district_geo_mappings');
    }
};