<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create location_aliases table.
     * 
     * Handles fuzzy location matching for data imports.
     */
    public function up(): void
    {
        Schema::create('location_aliases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // The alias (non-canonical name)
            $table->string('alias', 200);
            $table->string('normalized_alias', 200);
            $table->string('alias_language', 10)->nullable();
            $table->string('alias_type', 50)->nullable();
            
            // What it maps to
            $table->uuid('canonical_district_id')->nullable();
            $table->uuid('canonical_city_id')->nullable();
            $table->uuid('canonical_state_id')->nullable();
            $table->uuid('canonical_country_id')->nullable();
            
            // Quality & usage
            $table->decimal('confidence_score', 3, 2)->default(1.00);
            $table->boolean('verified')->default(false);
            $table->integer('usage_count')->default(0);
            $table->timestamp('last_used_at')->nullable();
            
            // Source of alias
            $table->uuid('data_source_id')->nullable();
            $table->uuid('created_by_user_id')->nullable();
            
            // Metadata
            $table->text('notes')->nullable();
            $table->jsonb('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign keys
            $table->foreign('canonical_district_id')
                  ->references('id')
                  ->on('districts')
                  ->onDelete('cascade');
            
            $table->foreign('canonical_city_id')
                  ->references('id')
                  ->on('cities')
                  ->onDelete('cascade');
            
            $table->foreign('canonical_state_id')
                  ->references('id')
                  ->on('states')
                  ->onDelete('cascade');
            
            $table->foreign('canonical_country_id')
                  ->references('id')
                  ->on('countries')
                  ->onDelete('cascade');
            
            $table->foreign('data_source_id')
                  ->references('id')
                  ->on('data_sources')
                  ->onDelete('set null');
            
            $table->foreign('created_by_user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
            
            // Indexes
            $table->index('alias');
            $table->index('normalized_alias');
            $table->index('canonical_district_id');
            $table->index('canonical_city_id');
            $table->index('canonical_state_id');
            $table->index('verified');
        });

        // Add constraint AFTER table exists
        DB::statement('
            ALTER TABLE location_aliases 
            ADD CONSTRAINT chk_location_aliases_has_target 
            CHECK (
                canonical_district_id IS NOT NULL OR 
                canonical_city_id IS NOT NULL OR 
                canonical_state_id IS NOT NULL OR
                canonical_country_id IS NOT NULL
            )
        ');

        // Fuzzy search indexes
        DB::statement('CREATE INDEX idx_location_aliases_alias_trgm ON location_aliases USING gin(alias gin_trgm_ops)');
        DB::statement('CREATE INDEX idx_location_aliases_normalized_trgm ON location_aliases USING gin(normalized_alias gin_trgm_ops)');

        // Create function for normalizing aliases
        DB::statement("
            CREATE OR REPLACE FUNCTION normalize_location_alias()
            RETURNS TRIGGER AS \$\$
            BEGIN
                NEW.normalized_alias = LOWER(
                    REGEXP_REPLACE(
                        REGEXP_REPLACE(
                            REGEXP_REPLACE(
                                REGEXP_REPLACE(
                                    REGEXP_REPLACE(
                                        REGEXP_REPLACE(NEW.alias, '[àáâãäåāăą]', 'a', 'gi'),
                                        '[èéêëēėę]', 'e', 'gi'
                                    ),
                                    '[ìíîïīį]', 'i', 'gi'
                                ),
                                '[òóôõöøōő]', 'o', 'gi'
                            ),
                            '[ùúûüūű]', 'u', 'gi'
                        ),
                        '[^a-z0-9]', '', 'gi'
                    )
                );
                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        // Create trigger
        DB::statement("
            CREATE TRIGGER trg_normalize_location_alias
            BEFORE INSERT OR UPDATE ON location_aliases
            FOR EACH ROW
            EXECUTE FUNCTION normalize_location_alias();
        ");
    }

    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS trg_normalize_location_alias ON location_aliases');
        DB::statement('DROP FUNCTION IF EXISTS normalize_location_alias()');
        Schema::dropIfExists('location_aliases');
    }
};