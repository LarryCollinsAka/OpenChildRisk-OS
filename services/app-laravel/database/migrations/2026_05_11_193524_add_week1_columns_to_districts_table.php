<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('districts', function (Blueprint $table) {
            // Location hierarchy
            $table->foreignUuid('state_id')->nullable()->after('code')->constrained('states')->nullOnDelete();
            $table->foreignUuid('country_id')->nullable()->after('state_id')->constrained('countries')->nullOnDelete();
            
            // District type
            $table->string('district_type')->nullable()->after('description');
            
            // Demographics
            $table->integer('population')->nullable()->after('district_type');
            $table->decimal('area_sq_km', 10, 2)->nullable()->after('population');
            
            // Geospatial
            $table->geometry('geometry', 'multipolygon', 4326)->nullable()->after('area_sq_km');
            $table->decimal('centroid_lat', 10, 8)->nullable()->after('geometry');
            $table->decimal('centroid_lng', 11, 8)->nullable()->after('centroid_lat');
            
            // Metadata
            $table->json('metadata')->nullable()->after('centroid_lng');
        });
    }

    public function down(): void
    {
        Schema::table('districts', function (Blueprint $table) {
            $table->dropForeign(['state_id']);
            $table->dropForeign(['country_id']);
            $table->dropColumn([
                'state_id',
                'country_id',
                'district_type',
                'population',
                'area_sq_km',
                'geometry',
                'centroid_lat',
                'centroid_lng',
                'metadata',
            ]);
        });
    }
};