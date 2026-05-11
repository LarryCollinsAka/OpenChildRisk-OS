<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compound_risk_assessments', function (Blueprint $table) {
            $table->foreign('superseded_by_id')
                  ->references('id')
                  ->on('compound_risk_assessments')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('compound_risk_assessments', function (Blueprint $table) {
            $table->dropForeign(['superseded_by_id']);
        });
    }
};