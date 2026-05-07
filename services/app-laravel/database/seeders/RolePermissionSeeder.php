<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Clear cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Get UNICEF organization
        $unicef = DB::table('organizations')->where('code', 'UNICEF-CM')->first();

        if (!$unicef) {
            $this->command->error('UNICEF not found');
            return;
        }

        // Create all permissions with UUIDs
        $permissions = [
            // Platform permissions
            'manage_system', 'manage_organizations', 'manage_all_users',
            'view_platform_analytics', 'manage_integrations', 'system_debug',
            
            // Organization permissions
            'create_alerts', 'view_alerts', 'resolve_alerts', 'delete_alerts',
            'view_districts', 'manage_districts', 'update_district_profiles',
            'create_programs', 'view_programs', 'manage_programs', 'deploy_programs',
            'create_facilities', 'view_facilities', 'manage_facilities',
            'create_risk_assessments', 'view_risk_scores',
            'invite_users', 'manage_org_users', 'assign_roles',
            'view_reports', 'export_reports',
        ];

        $permissionIds = [];
        foreach ($permissions as $permissionName) {
            $id = Str::uuid()->toString();
            DB::table('permissions')->insert([
                'id' => $id,
                'name' => $permissionName,
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $permissionIds[$permissionName] = $id;
        }

        $this->command->info('✔ Created ' . count($permissions) . ' permissions');

        // PLATFORM ROLES (organization_id = NULL)
        
        // 1. Platform Super Admin
        $platformAdminId = Str::uuid()->toString();
        DB::table('roles')->insert([
            'id' => $platformAdminId,
            'name' => 'Platform Super Admin',
            'guard_name' => 'web',
            'organization_id' => null, // Platform role
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Assign all permissions to Platform Super Admin
        foreach ($permissionIds as $permId) {
            DB::table('role_has_permissions')->insert([
                'permission_id' => $permId,
                'role_id' => $platformAdminId,
            ]);
        }

        // 2. Platform Developer
        $platformDevId = Str::uuid()->toString();
        DB::table('roles')->insert([
            'id' => $platformDevId,
            'name' => 'Platform Developer',
            'guard_name' => 'web',
            'organization_id' => null, // Platform role
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $devPerms = ['manage_system', 'view_platform_analytics', 'system_debug', 'view_alerts', 'view_districts', 'view_reports'];
        foreach ($devPerms as $permName) {
            DB::table('role_has_permissions')->insert([
                'permission_id' => $permissionIds[$permName],
                'role_id' => $platformDevId,
            ]);
        }

        $this->command->info('✔ Created 2 platform roles');

        // ORGANIZATION ROLES (organization_id = UNICEF UUID)
        
        // 1. Organization Executive
        $orgExecId = Str::uuid()->toString();
        DB::table('roles')->insert([
            'id' => $orgExecId,
            'name' => 'Organization Executive',
            'guard_name' => 'web',
            'organization_id' => $unicef->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $execPerms = [
            'create_alerts', 'view_alerts', 'resolve_alerts', 'delete_alerts',
            'view_districts', 'manage_districts', 'update_district_profiles',
            'create_programs', 'view_programs', 'manage_programs', 'deploy_programs',
            'create_facilities', 'view_facilities', 'manage_facilities',
            'create_risk_assessments', 'view_risk_scores',
            'invite_users', 'manage_org_users', 'assign_roles',
            'view_reports', 'export_reports',
        ];
        foreach ($execPerms as $permName) {
            DB::table('role_has_permissions')->insert([
                'permission_id' => $permissionIds[$permName],
                'role_id' => $orgExecId,
            ]);
        }

        // 2. Field Officer
        $fieldOfficerId = Str::uuid()->toString();
        DB::table('roles')->insert([
            'id' => $fieldOfficerId,
            'name' => 'Field Officer',
            'guard_name' => 'web',
            'organization_id' => $unicef->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $fieldPerms = ['create_alerts', 'view_alerts', 'view_districts', 'view_programs', 'view_facilities', 'create_risk_assessments', 'view_risk_scores', 'view_reports'];
        foreach ($fieldPerms as $permName) {
            DB::table('role_has_permissions')->insert([
                'permission_id' => $permissionIds[$permName],
                'role_id' => $fieldOfficerId,
            ]);
        }

        $this->command->info('✔ Created 2 organization roles');
        $this->command->info('✔ Total: 4 roles (2 platform, 2 organization)');
    }
}