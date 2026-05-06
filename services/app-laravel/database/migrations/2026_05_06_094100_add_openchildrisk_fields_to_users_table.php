<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add OpenChildRisk-specific fields to users table.
     * 
     * NOTE: organization_id is NOT here because Spatie handles
     * multi-organization membership via model_has_roles pivot table.
     * 
     * A user can have:
     * - Role "Field Officer" in UNICEF (organization_id = uuid1)
     * - Role "Admin" in WHO (organization_id = uuid2)
     * 
     * Use: $user->assignRole('Field Officer', $organizationId)
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Language preference
            $table->uuid('language_id')->nullable()->after('email');

            // Contact info for alerts
            $table->string('phone', 50)->nullable()->after('language_id');
            $table->string('phone_country_code', 5)->nullable()->after('phone');
            $table->boolean('phone_verified')->default(false)->after('phone_country_code');

            // User profile
            $table->string('first_name', 100)->nullable()->after('name');
            $table->string('last_name', 100)->nullable()->after('first_name');
            $table->string('job_title', 100)->nullable()->after('last_name');

            // Primary operating area (optional - user's main location)
            // This is separate from their org membership
            $table->uuid('primary_district_id')->nullable()->after('job_title');
            $table->uuid('primary_facility_id')->nullable()->after('primary_district_id');

            // Alert preferences
            $table->boolean('receive_sms_alerts')->default(true)->after('primary_facility_id');
            $table->boolean('receive_email_alerts')->default(true)->after('receive_sms_alerts');
            $table->boolean('receive_whatsapp_alerts')->default(false)->after('receive_email_alerts');

            // User status
            $table->boolean('active')->default(true)->after('receive_whatsapp_alerts');
            $table->timestamp('last_active_at')->nullable()->after('active');

            // Metadata for additional user attributes
            $table->jsonb('metadata')->nullable()->after('last_active_at');

            // Soft deletes (if not already present)
            if (!Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }

            // Foreign keys
            $table->foreign('language_id')
                ->references('id')
                ->on('languages')
                ->onDelete('set null');

            $table->foreign('primary_district_id')
                ->references('id')
                ->on('districts')
                ->onDelete('set null');

            $table->foreign('primary_facility_id')
                ->references('id')
                ->on('facilities')
                ->onDelete('set null');

            // Indexes
            $table->index('language_id');
            $table->index('primary_district_id');
            $table->index('phone');
            $table->index('active');
            $table->index('last_active_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['language_id']);
            $table->dropForeign(['primary_district_id']);
            $table->dropForeign(['primary_facility_id']);

            // Drop columns
            $table->dropColumn([
                'language_id',
                'phone',
                'phone_country_code',
                'phone_verified',
                'first_name',
                'last_name',
                'job_title',
                'primary_district_id',
                'primary_facility_id',
                'receive_sms_alerts',
                'receive_email_alerts',
                'receive_whatsapp_alerts',
                'active',
                'last_active_at',
                'metadata',
            ]);

            // Drop soft deletes if we added it
            if (Schema::hasColumn('users', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
