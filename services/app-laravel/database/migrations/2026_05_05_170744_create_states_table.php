<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * States/Regions/Provinces - admin level 1.
     * Source: countries-states-cities-database
     * 
     * 5,299 first-level administrative divisions worldwide.
     * Examples: Far North (Cameroon), California (USA), Bavaria (Germany)
     */
    public function up(): void
    {
        Schema::create('states', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('country_id');
            
            // External dataset reference
            $table->integer('external_id')->unique();
            $table->string('external_source', 50)->default('csc_dataset');
            
            // Basic info
            $table->string('name', 100);
            $table->string('native', 100)->nullable();
            
            // ISO codes
            $table->string('iso2', 10)->nullable();
            $table->string('iso3166_2', 20)->nullable(); // Full code like "CM-EN"
            
            // Type varies by country (region, province, state, etc.)
            $table->string('type', 50)->nullable();
            
            // Coordinates (state center)
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            // Timezone
            $table->string('timezone', 100)->nullable();
            
            // Status
            $table->boolean('active')->default(true);
            
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign keys
            $table->foreign('country_id')
                  ->references('id')
                  ->on('countries')
                  ->onDelete('cascade');
            
            // Indexes
            $table->index('country_id');
            $table->index('name');
            $table->index('iso2');
            $table->index('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('states');
    }
};