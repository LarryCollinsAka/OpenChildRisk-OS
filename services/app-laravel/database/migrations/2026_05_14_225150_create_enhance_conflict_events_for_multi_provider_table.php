<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Enhance conflict_events for multi-provider architecture
     * 
     * Philosophy: Provider-agnostic event normalization
     * - Single normalized table (not per-provider tables)
     * - Source confidence scoring
     * - Event deduplication support
     * - Cross-source validation
     */
    public function up(): void
    {
        Schema::table('conflict_events', function (Blueprint $table) {
            // Source provider identification
            $table->string('source_provider', 50)->after('data_source_id')
                  ->nullable()
                  ->comment('Provider: ACLED, ICEWS, GDELT, etc');
            
            // Source confidence (0.0 - 1.0)
            $table->decimal('source_confidence', 3, 2)->after('source_provider')
                  ->default(0.75)
                  ->comment('Provider reliability score 0-1');
            
            // Event deduplication
            $table->string('canonical_event_hash', 64)->after('source_confidence')
                  ->nullable()
                  ->comment('Hash for cross-source deduplication');
            
            // Cross-source matching
            $table->json('cross_source_matches')->after('canonical_event_hash')
                  ->nullable()
                  ->comment('Related events from other providers');
            
            // Temporal similarity (for deduplication)
            $table->integer('temporal_window_hours')->after('cross_source_matches')
                  ->default(48)
                  ->comment('Time window for event matching');
            
            // Spatial similarity (for deduplication)
            $table->decimal('spatial_radius_km', 6, 2)->after('temporal_window_hours')
                  ->default(50.0)
                  ->comment('Distance threshold for matching');
            
            // Provider-specific raw data
            $table->jsonb('provider_raw_data')->after('metadata')
                  ->nullable()
                  ->comment('Original provider data for replay');
            
            // Add indexes
            $table->index('source_provider');
            $table->index('canonical_event_hash');
            $table->index('source_confidence');
            $table->index(['event_date', 'source_provider']);
        });
    }

    public function down(): void
    {
        Schema::table('conflict_events', function (Blueprint $table) {
            $table->dropIndex(['source_provider']);
            $table->dropIndex(['canonical_event_hash']);
            $table->dropIndex(['source_confidence']);
            $table->dropIndex(['event_date', 'source_provider']);
            
            $table->dropColumn([
                'source_provider',
                'source_confidence',
                'canonical_event_hash',
                'cross_source_matches',
                'temporal_window_hours',
                'spatial_radius_km',
                'provider_raw_data',
            ]);
        });
    }
};