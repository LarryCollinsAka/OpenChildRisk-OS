<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Spatie permissions with teams (organizations) enabled.
     * Modified to use UUIDs instead of auto-incrementing integers.
     * 
     * CRITICAL: organization_id is NULLABLE to support both:
     * - Platform users (organization_id = NULL)
     * - Organization users (organization_id = UUID)
     */
    public function up(): void
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $pivotRole = $columnNames['role_pivot_key'] ?? 'role_id';
        $pivotPermission = $columnNames['permission_pivot_key'] ?? 'permission_id';

        if (empty($tableNames)) {
            throw new \Exception('Error: config/permission.php not loaded. Run [php artisan config:clear] and try again.');
        }

        // PERMISSIONS TABLE
        Schema::create($tableNames['permissions'], function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();

            $table->unique(['name', 'guard_name']);
        });

        // ROLES TABLE
        Schema::create($tableNames['roles'], function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->nullable(); // NULLABLE for platform roles
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();

            $table->index('organization_id', 'roles_team_foreign_key_index');
            $table->unique(['organization_id', 'name', 'guard_name']);
        });

        // MODEL HAS PERMISSIONS TABLE
        Schema::create($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames, $pivotPermission) {
            $table->uuid($pivotPermission);
            $table->string('model_type');
            $table->uuid('model_id');
            $table->uuid('organization_id')->nullable(); // NULLABLE for platform users

            $table->foreign($pivotPermission)
                ->references('id')
                ->on($tableNames['permissions'])
                ->onDelete('cascade');

            $table->index(['model_id', 'model_type'], 'model_has_permissions_model_id_model_type_index');
            $table->index('organization_id', 'model_has_permissions_team_foreign_key_index');

            $table->primary(
                ['organization_id', $pivotPermission, 'model_id', 'model_type'],
                'model_has_permissions_permission_model_type_primary'
            );
        });

        // MODEL HAS ROLES TABLE
        Schema::create($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames, $pivotRole) {
            $table->uuid($pivotRole);
            $table->string('model_type');
            $table->uuid('model_id');
            $table->uuid('organization_id')->nullable(); // NULLABLE FOR PLATFORM USERS

            $table->foreign($pivotRole)
                ->references('id')
                ->on($tableNames['roles'])
                ->onDelete('cascade');

            // Indexes
            $table->index(['model_id', 'model_type'], 'model_has_roles_model_id_model_type_index');
            $table->index('organization_id', 'model_has_roles_team_foreign_key_index');

            // PRIMARY KEY WITHOUT organization_id (allows NULL values)
            $table->primary(
                [$pivotRole, 'model_id', 'model_type'],
                'model_has_roles_role_model_type_primary'
            );

            // UNIQUE CONSTRAINT for org-scoped uniqueness (NULLs are treated as distinct in unique constraints)
            $table->unique(
                ['organization_id', $pivotRole, 'model_id', 'model_type'],
                'model_has_roles_org_unique'
            );
        });

        // ROLE HAS PERMISSIONS TABLE
        Schema::create($tableNames['role_has_permissions'], function (Blueprint $table) use ($tableNames, $pivotRole, $pivotPermission) {
            $table->uuid($pivotPermission);
            $table->uuid($pivotRole);

            $table->foreign($pivotPermission)
                ->references('id')
                ->on($tableNames['permissions'])
                ->onDelete('cascade');

            $table->foreign($pivotRole)
                ->references('id')
                ->on($tableNames['roles'])
                ->onDelete('cascade');

            $table->primary([$pivotPermission, $pivotRole], 'role_has_permissions_permission_id_role_id_primary');
        });

        app('cache')
            ->store(config('permission.cache.store') != 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');

        if (empty($tableNames)) {
            throw new \Exception('Error: config/permission.php not found and defaults could not be merged.');
        }

        Schema::drop($tableNames['role_has_permissions']);
        Schema::drop($tableNames['model_has_roles']);
        Schema::drop($tableNames['model_has_permissions']);
        Schema::drop($tableNames['roles']);
        Schema::drop($tableNames['permissions']);
    }
};
