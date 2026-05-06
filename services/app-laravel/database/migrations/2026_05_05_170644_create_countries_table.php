<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Countries table - canonical geography layer.
     * Source: countries-states-cities-database (ODbL-1.0)
     * 
     * 250 countries with complete metadata.
     * Never modified - only referenced.
     */
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // External dataset reference
            $table->integer('external_id')->unique();
            $table->string('external_source', 50)->default('csc_dataset');
            
            // ISO codes - standard identifiers
            $table->string('iso2', 2)->unique();
            $table->string('iso3', 3)->unique();
            $table->string('numeric_code', 3)->nullable();
            
            // Basic info
            $table->string('name', 100);
            $table->string('native', 100)->nullable();
            $table->string('capital', 100)->nullable();
            $table->string('nationality', 100)->nullable();
            
            // Contact info
            $table->string('phonecode', 10)->nullable();
            $table->string('tld', 10)->nullable();
            
            // Currency
            $table->string('currency', 3)->nullable();
            $table->string('currency_name', 100)->nullable();
            $table->string('currency_symbol', 10)->nullable();
            
            // Geography
            $table->string('region', 50)->nullable();
            $table->integer('region_id')->nullable();
            $table->string('subregion', 50)->nullable();
            $table->integer('subregion_id')->nullable();
            
            // Coordinates (country center)
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            // Demographics
            $table->bigInteger('population')->nullable();
            $table->decimal('gdp', 15, 2)->nullable();
            
            // UI elements
            $table->string('emoji', 10)->nullable();
            $table->string('emoji_u', 50)->nullable();
            
            // Status
            $table->boolean('active')->default(true);
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('iso2');
            $table->index('iso3');
            $table->index('region');
            $table->index('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};