<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Districts - operational geography layer.
     * 
     * Flexible zones for humanitarian operations.
     * NOT bound to admin2 boundaries.
     * - Can span multiple cities
     * - Can be sub-city level
     * - Can have custom boundaries
     * 
     * Linked to canonical geography via district_geo_mappings.
     */
    public function up(): void
    {
        Schema::create('districts', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Basic info
            $table->string('name', 100);
            $table->string('code', 50)->unique()->nullable(); // e.g., "FN-MOR"
            $table->text('description')->nullable();

            // Hierarchy (optional - for nested operational zones)
            $table->uuid('parent_id')->nullable();
            $table->string('admin_level', 50)->nullable(); // district, zone, sector

            // Source tracking
            $table->string('external_source', 50)->nullable(); // manual, unicef, who
            $table->string('external_id', 100)->nullable();

            // Geometry (PostGIS)
            // For precise boundary definition when needed
            $table->geometry('geom', 'MULTIPOLYGON', 4326)->nullable();

            // Time validity
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_to')->nullable();

            // Status
            $table->boolean('active')->default(true);

            $table->timestamps();
            $table->softDeletes();


            // Indexes
            $table->index('code');
            $table->index('parent_id');
            $table->index('admin_level');
            $table->index('active');
            $table->index(['valid_from', 'valid_to']);
        });

        //self-referencing foreign key for parent_id
        Schema::table('districts', function (Blueprint $table) {
            $table->foreign('parent_id')
                ->references('id')
                ->on('districts')
                ->onDelete('set null');
        });

        // Trigram index for fuzzy matching
        DB::statement('CREATE INDEX districts_name_trgm_idx ON districts USING gin (name gin_trgm_ops)');
        // Add spatial index for geometry
        DB::statement('CREATE INDEX districts_geom_idx ON districts USING GIST (geom)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('districts');
    }
};
