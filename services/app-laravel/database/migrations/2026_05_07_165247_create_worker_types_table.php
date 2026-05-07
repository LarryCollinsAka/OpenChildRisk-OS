<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create worker types table.
     * 
     * Field workers are NOT homogeneous - a CHW specializing in malaria
     * cannot respond to nutrition emergencies. This table defines worker
     * classifications for smart alert routing.
     */
    public function up(): void
    {
        Schema::create('worker_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->string('code', 50)->unique();
            $table->string('name', 200);
            $table->text('description')->nullable();
            
            // Specialization area
            $table->string('specialization_area', 100)->nullable(); // health, wash, nutrition, education
            
            // Status
            $table->boolean('active')->default(true);
            
            // Metadata
            $table->jsonb('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('code');
            $table->index('specialization_area');
            $table->index('active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worker_types');
    }
};