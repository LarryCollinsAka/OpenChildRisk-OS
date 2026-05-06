<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Enable PostgreSQL extensions required by OpenChildRisk OS.
     * 
     * - uuid-ossp: UUID generation
     * - postgis: Geospatial data types and functions
     * - pg_trgm: Fuzzy string matching for location resolution
     */
    public function up(): void
    {
        // Enable UUID generation
        DB::statement('CREATE EXTENSION IF NOT EXISTS "uuid-ossp"');
        
        // Enable PostGIS for geospatial data
        DB::statement('CREATE EXTENSION IF NOT EXISTS "postgis"');
        
        // Enable trigram matching for fuzzy location search
        // Handles "Marua" → "Maroua", "Kouseri" → "Kousseri"
        DB::statement('CREATE EXTENSION IF NOT EXISTS "pg_trgm"');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP EXTENSION IF EXISTS "pg_trgm"');
        DB::statement('DROP EXTENSION IF EXISTS "postgis"');
        DB::statement('DROP EXTENSION IF EXISTS "uuid-ossp"');
    }
};