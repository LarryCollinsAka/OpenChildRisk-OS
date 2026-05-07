<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add OpenChildRisk-specific fields to users table.
     * 
     * ARCHITECTURE:
     * - Platform users: organization_id = NULL, user_type = 'platform'
     * - Organization users: organization_id = UUID, user_type = 'organization'
     * 
     * Spatie permissions handle roles for both types.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // User type classification
            $table->enum('user_type', ['platform', 'organization'])->default('organization')->after('email');
            
            // Organization membership (NULLABLE for platform users)
            $table->uuid('organization_id')->nullable()->after('user_type');
            
            // Language preference
            $table->uuid('language_id')->nullable()->after('organization_id');
            
            // Contact info for alerts
            $table->string('phone', 50)->nullable()->after('language_id');
            $table->string('phone_country_code', 5)->nullable()->after('phone');
            $table->boolean('phone_verified')->default(false)->after('phone_country_code');
            
            // User profile
            $table->string('first_name', 100)->nullable()->after('name');
            $table->string('last_name', 100)->nullable()->after('first_name');
            $table->string('title', 50)->nullable()->after('last_name'); // Dr., Mr., Ms., Prof.
            $table->string('job_title', 100)->nullable()->after('title');
            $table->string('occupation', 100)->nullable()->after('job_title'); // Medical Doctor, Epidemiologist, etc.
            
            // Geographic info (for organization users)
            $table->uuid('country_of_origin_id')->nullable()->after('occupation');
            $table->uuid('country_of_current_residence_id')->nullable()->after('country_of_origin_id');
            $table->uuid('country_of_permanent_residence_id')->nullable()->after('country_of_current_residence_id');
            
            // Primary operating area (optional - user's main location)
            $table->uuid('primary_district_id')->nullable()->after('country_of_permanent_residence_id');
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
            $table->foreign('organization_id')
                  ->references('id')
                  ->on('organizations')
                  ->onDelete('cascade');
            
            $table->foreign('language_id')
                  ->references('id')
                  ->on('languages')
                  ->onDelete('set null');
            
            $table->foreign('country_of_origin_id')
                  ->references('id')
                  ->on('countries')
                  ->onDelete('set null');
            
            $table->foreign('country_of_current_residence_id')
                  ->references('id')
                  ->on('countries')
                  ->onDelete('set null');
            
            $table->foreign('country_of_permanent_residence_id')
                  ->references('id')
                  ->on('countries')
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
            $table->index('user_type');
            $table->index('organization_id');
            $table->index('language_id');
            $table->index('country_of_origin_id');
            $table->index('country_of_current_residence_id');
            $table->index('country_of_permanent_residence_id');
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
            $table->dropForeign(['organization_id']);
            $table->dropForeign(['language_id']);
            $table->dropForeign(['country_of_origin_id']);
            $table->dropForeign(['country_of_current_residence_id']);
            $table->dropForeign(['country_of_permanent_residence_id']);
            $table->dropForeign(['primary_district_id']);
            $table->dropForeign(['primary_facility_id']);
            
            // Drop columns
            $table->dropColumn([
                'user_type',
                'organization_id',
                'language_id',
                'phone',
                'phone_country_code',
                'phone_verified',
                'first_name',
                'last_name',
                'title',
                'job_title',
                'occupation',
                'country_of_origin_id',
                'country_of_current_residence_id',
                'country_of_permanent_residence_id',
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