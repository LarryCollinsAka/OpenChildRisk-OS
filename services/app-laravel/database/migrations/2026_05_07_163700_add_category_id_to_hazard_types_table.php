<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add category relationship to hazard_types.
     * 
     * This creates proper taxonomy: Category → Type → Specific Hazard
     * Example: Climate → Flood → Cameroon Sept 2026 Flood
     */
    public function up(): void
    {
        Schema::table('hazard_types', function (Blueprint $table) {
            // Add category foreign key
            $table->uuid('category_id')->nullable()->after('id');
            
            $table->foreign('category_id')
                  ->references('id')
                  ->on('hazard_categories')
                  ->onDelete('restrict'); // Don't delete category if types exist
            
            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::table('hazard_types', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
    }
};