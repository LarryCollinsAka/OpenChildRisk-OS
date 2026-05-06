<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Country timezones - IANA timezone data per country.
     * Source: countries-states-cities-database
     * 
     * Some countries have multiple timezones (e.g., USA, Russia).
     * Essential for scheduling alerts and events.
     */
    public function up(): void
    {
        Schema::create('country_timezones', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('country_id');
            
            // IANA timezone identifier (e.g., "Africa/Douala")
            $table->string('zone_name', 100);
            
            // GMT offset in seconds
            $table->integer('gmt_offset');
            
            // Human-readable offset (e.g., "UTC+01:00")
            $table->string('gmt_offset_name', 20);
            
            // Timezone abbreviation (e.g., "WAT")
            $table->string('abbreviation', 10);
            
            // Full timezone name (e.g., "West Africa Time")
            $table->string('tz_name', 100);
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('country_id')
                  ->references('id')
                  ->on('countries')
                  ->onDelete('cascade');
            
            // Indexes
            $table->index('zone_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('country_timezones');
    }
};