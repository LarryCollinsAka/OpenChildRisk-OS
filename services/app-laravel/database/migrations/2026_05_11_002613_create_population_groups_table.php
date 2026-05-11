<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create population_groups table.
     * 
     * Defines vulnerable population segments for differentiated targeting.
     * 
     * UNICEF REALITY: Children are not homogeneous. A flood affects:
     * - Under-5s differently than adolescents
     * - Disabled children differently than able-bodied
     * - Displaced children differently than residents
     * - Zero-dose children differently than vaccinated
     * 
     * This table enables child-specific vulnerability modeling.
     */
    public function up(): void
    {
        Schema::create('population_groups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->string('code', 100)->unique();
            $table->string('name', 200);
            $table->text('description')->nullable();
            
            // Categorization
            $table->enum('group_type', [
                'age',           // under-5, adolescent, school-age
                'disability',    // mobility, communication, cognitive
                'displacement',  // IDPs, refugees, returnees
                'nutrition',     // SAM, MAM, stunted
                'vaccination',   // zero-dose, under-immunized
                'gender',        // girls, boys
                'other'          // custom groups
            ]);
            
            // Age range (for age-based groups)
            $table->integer('min_age_months')->nullable(); // null = no minimum
            $table->integer('max_age_months')->nullable(); // null = no maximum
            
            // Priority weighting (for risk calculations)
            $table->decimal('vulnerability_weight', 3, 2)->default(1.00); // 0.50 to 2.00
            
            // Status
            $table->boolean('active')->default(true);
            $table->integer('display_order')->default(0); // For UI sorting
            
            // Metadata
            $table->jsonb('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('code');
            $table->index('group_type');
            $table->index('active');
            $table->index('display_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('population_groups');
    }
};