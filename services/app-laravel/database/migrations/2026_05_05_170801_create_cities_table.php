<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cities - admin level 2 (district level).
     * Source: countries-states-cities-database
     * 
     * 153,765 cities/districts worldwide.
     * In humanitarian context: cities = districts.
     * 
     * Examples:
     * - Maroua city (dataset) = Maroua district (UNICEF)
     * - Mora city (dataset) = Mora district (UNICEF)
     */
    public function up(): void
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('state_id');
            $table->uuid('country_id');

            // External dataset reference
            $table->integer('external_id')->unique();
            $table->string('external_source', 50)->default('csc_dataset');

            // Basic info
            $table->string('name', 100);

            // Coordinates (precise location)
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);

            // Timezone
            $table->string('timezone', 100)->nullable();

            // Status
            $table->boolean('active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('state_id')
                ->references('id')
                ->on('states')
                ->onDelete('cascade');

            $table->foreign('country_id')
                ->references('id')
                ->on('countries')
                ->onDelete('cascade');

            // Indexes
            $table->index('state_id');
            $table->index('country_id');
            $table->index('name');
            $table->index(['latitude', 'longitude']);
            $table->index('active');

            // Trigram index for fuzzy matching   
        });

        // Add trigram index for fuzzy name matching
        DB::statement('CREATE INDEX cities_name_trgm_idx ON cities USING gin (name gin_trgm_ops)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
