<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hazard types - categories of child health risks.
     * 
     * Categories (max 2 levels):
     * - Disease (parent)
     *   - Cholera (child)
     *   - Malaria (child)
     *   - Measles (child)
     * - Climate (parent)
     *   - Flood (child)
     *   - Drought (child)
     *   - Heat (child)
     * - Conflict (parent)
     *   - Armed conflict (child)
     *   - Displacement (child)
     * 
     * Determines which risk engine to use.
     */
    public function up(): void
    {
        Schema::create('hazard_types', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Basic info
            $table->string('name', 100);
            $table->string('code', 50)->unique(); // e.g., "cholera", "flood"
            $table->text('description')->nullable();

            // Hierarchy (max 2 levels)
            $table->uuid('parent_id')->nullable();

            // Category grouping
            $table->enum('category', [
                'disease',
                'climate',
                'conflict',
                'nutrition',
                'wash',
                'other'
            ]);

            // Risk engine mapping
            $table->string('risk_engine', 100)->nullable(); // cholera, heat, flood

            // Severity indicators
            $table->integer('default_severity')->default(5); // 1-10
            $table->integer('typical_time_window_days')->nullable(); // alert window

            // Status
            $table->boolean('active')->default(true);

            // Metadata for risk parameters
            $table->jsonb('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();


            // Indexes
            $table->index('code');
            $table->index('category');
            $table->index('parent_id');
            $table->index('active');
        });

        Schema::table('hazard_types', function (Blueprint $table) {
            $table->foreign('parent_id')
                ->references('id')
                ->on('hazard_types')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hazard_types');
    }
};
