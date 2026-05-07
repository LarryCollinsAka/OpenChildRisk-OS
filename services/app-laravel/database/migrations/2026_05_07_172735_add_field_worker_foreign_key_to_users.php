<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add foreign key constraint from users to field_workers.
     * 
     * This completes the bidirectional relationship.
     * Done in a separate migration to avoid circular dependency issues.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('field_worker_id')
                  ->references('id')
                  ->on('field_workers')
                  ->onDelete('set null'); // If field worker deleted, user remains
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['field_worker_id']);
        });
    }
};