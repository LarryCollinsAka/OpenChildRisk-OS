<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create hazard categories table.
     * 
     * Categories provide high-level grouping for hazard types.
     * This enables better querying, filtering, and taxonomy management.
     */
    public function up(): void
    {
        Schema::create('hazard_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->string('code', 50)->unique();
            $table->string('name', 200);
            $table->text('description')->nullable();
            
            // UI metadata
            $table->string('icon', 50)->nullable(); // Font Awesome icon name
            $table->string('color', 20)->nullable(); // Hex color for UI
            
            // Status
            $table->boolean('active')->default(true);
            
            // Metadata for extensibility
            $table->jsonb('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('code');
            $table->index('active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hazard_categories');
    }
};