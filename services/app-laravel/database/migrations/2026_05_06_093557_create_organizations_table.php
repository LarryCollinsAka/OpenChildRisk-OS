<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Organizations - humanitarian agencies and partners.
     * 
     * Examples:
     * - UNICEF
     * - WHO
     * - WFP
     * - National Red Cross
     * - Local NGOs
     * 
     * Organizations:
     * - Create alerts
     * - Deploy programs
     * - Own facilities
     * - Define custom district boundaries
     */
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Basic info
            $table->string('name', 100);
            $table->string('abbreviation', 20)->nullable();
            $table->string('code', 50)->unique()->nullable(); // e.g., "UNICEF-CM"
            $table->text('description')->nullable();
            
            // Organization type
            $table->enum('type', [
                'un_agency',
                'ngo_international',
                'ngo_local',
                'government',
                'red_cross',
                'private_sector',
                'other'
            ]);
            
            // Contact info
            $table->string('email', 100)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('website', 255)->nullable();
            $table->text('address')->nullable();
            
            // Primary country of operation
            $table->uuid('country_id')->nullable();
            
            // Status
            $table->boolean('active')->default(true);
            
            // Metadata for additional fields
            $table->jsonb('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign keys
            $table->foreign('country_id')
                  ->references('id')
                  ->on('countries')
                  ->onDelete('set null');
            
            // Indexes
            $table->index('name');
            $table->index('code');
            $table->index('type');
            $table->index('country_id');
            $table->index('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};