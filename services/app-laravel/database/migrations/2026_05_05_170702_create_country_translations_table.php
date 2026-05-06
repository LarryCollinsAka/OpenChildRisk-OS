<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Country translations - 19 languages per country.
     * Source: countries-states-cities-database
     * 
     * Languages: br, ko, pt-BR, pt, nl, hr, fa, de, es, fr, ja, it, 
     *            zh-CN, tr, ru, uk, pl, ar, hi
     */
    public function up(): void
    {
        Schema::create('country_translations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('country_id');
            
            // ISO 639-1 language codes (2 chars) or locale (5 chars like pt-BR)
            $table->string('language_code', 10);
            
            // Translated country name
            $table->string('name', 100);
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('country_id')
                  ->references('id')
                  ->on('countries')
                  ->onDelete('cascade');
            
            // Indexes
            $table->index('language_code');
            $table->unique(['country_id', 'language_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('country_translations');
    }
};