<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Organization translations - multilingual organization names.
     * 
     * Example:
     * Organization: UNICEF
     * - en: "United Nations Children's Fund"
     * - fr: "Fonds des Nations Unies pour l'Enfance"
     * - ar: "صندوق الأمم المتحدة للطفولة"
     */
    public function up(): void
    {
        Schema::create('organization_translations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('language_id');
            
            // Translated content
            $table->string('name', 100);
            $table->text('description')->nullable();
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('organization_id')
                  ->references('id')
                  ->on('organizations')
                  ->onDelete('cascade');
            
            $table->foreign('language_id')
                  ->references('id')
                  ->on('languages')
                  ->onDelete('cascade');
            
            // Indexes
            $table->index('organization_id');
            $table->index('language_id');
            
            // One translation per language per organization
            $table->unique(['organization_id', 'language_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_translations');
    }
};