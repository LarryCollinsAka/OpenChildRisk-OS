<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add field_worker_id to users table.
     * 
     * This creates a bidirectional relationship:
     * - User can be a field worker (has field_worker_id)
     * - Field worker can have a user account (has user_id)
     * 
     * Note: FK constraint added in a later migration after field_workers table exists.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('field_worker_id')->nullable()->after('organization_id');
            $table->index('field_worker_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('field_worker_id');
        });
    }
};