<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Get references
        $unicef = DB::table('organizations')->where('code', 'UNICEF-CM')->first();
        $english = DB::table('languages')->where('code', 'en')->first();
        
        $platformAdminRole = DB::table('roles')->where('name', 'Platform Super Admin')->first();
        $orgExecRole = DB::table('roles')->where('name', 'Organization Executive')->first();
        $fieldOfficerRole = DB::table('roles')->where('name', 'Field Officer')->first();

        if (!$unicef || !$english || !$platformAdminRole || !$orgExecRole || !$fieldOfficerRole) {
            $this->command->error('Required data not found. Run previous seeders first.');
            return;
        }

        // 1. PLATFORM SUPER ADMIN (user_type = platform, organization_id = NULL)
        $platformAdminId = Str::uuid()->toString();
        DB::table('users')->insert([
            'id' => $platformAdminId,
            'user_type' => 'platform',
            'organization_id' => null, // Platform user
            'name' => 'Platform Administrator',
            'first_name' => 'Platform',
            'last_name' => 'Administrator',
            'email' => 'admin@openchildrisk.org',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'language_id' => $english->id,
            'job_title' => 'Platform Administrator',
            'active' => true,
            'receive_sms_alerts' => false,
            'receive_email_alerts' => true,
            'receive_whatsapp_alerts' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Assign Platform Super Admin role
        DB::table('model_has_roles')->insert([
            'role_id' => $platformAdminRole->id,
            'model_type' => 'App\Models\User',
            'model_id' => $platformAdminId,
            'organization_id' => null,
        ]);

        // 2. UNICEF ORGANIZATION EXECUTIVE (user_type = organization, organization_id = UNICEF)
        $orgExecId = Str::uuid()->toString();
        DB::table('users')->insert([
            'id' => $orgExecId,
            'user_type' => 'organization',
            'organization_id' => $unicef->id,
            'name' => 'Dr. Marie Dubois',
            'first_name' => 'Marie',
            'last_name' => 'Dubois',
            'title' => 'Dr.',
            'email' => 'marie.dubois@unicef.org',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'language_id' => $english->id,
            'phone' => '+237222231300',
            'phone_country_code' => '+237',
            'phone_verified' => true,
            'job_title' => 'Country Director',
            'occupation' => 'Public Health Specialist',
            'active' => true,
            'receive_sms_alerts' => true,
            'receive_email_alerts' => true,
            'receive_whatsapp_alerts' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Assign Organization Executive role
        DB::table('model_has_roles')->insert([
            'role_id' => $orgExecRole->id,
            'model_type' => 'App\Models\User',
            'model_id' => $orgExecId,
            'organization_id' => $unicef->id,
        ]);

        // 3. UNICEF FIELD OFFICER (user_type = organization, organization_id = UNICEF)
        $fieldOfficerId = Str::uuid()->toString();
        DB::table('users')->insert([
            'id' => $fieldOfficerId,
            'user_type' => 'organization',
            'organization_id' => $unicef->id,
            'name' => 'Ibrahim Mahamat',
            'first_name' => 'Ibrahim',
            'last_name' => 'Mahamat',
            'email' => 'ibrahim.mahamat@unicef.org',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'language_id' => $english->id,
            'phone' => '+237690123456',
            'phone_country_code' => '+237',
            'phone_verified' => true,
            'job_title' => 'Field Officer - Far North',
            'occupation' => 'Community Health Worker',
            'active' => true,
            'receive_sms_alerts' => true,
            'receive_email_alerts' => true,
            'receive_whatsapp_alerts' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Assign Field Officer role
        DB::table('model_has_roles')->insert([
            'role_id' => $fieldOfficerRole->id,
            'model_type' => 'App\Models\User',
            'model_id' => $fieldOfficerId,
            'organization_id' => $unicef->id,
        ]);

        $this->command->info('✔ Created 3 users (1 platform, 2 organization)');
        $this->command->info('');
        $this->command->info('LOGIN CREDENTIALS:');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('Platform Admin:');
        $this->command->info('  Email: admin@openchildrisk.org');
        $this->command->info('  Password: password');
        $this->command->info('  Type: platform');
        $this->command->info('');
        $this->command->info('Organization Executive (UNICEF):');
        $this->command->info('  Email: marie.dubois@unicef.org');
        $this->command->info('  Password: password');
        $this->command->info('  Type: organization');
        $this->command->info('');
        $this->command->info('Field Officer (UNICEF):');
        $this->command->info('  Email: ibrahim.mahamat@unicef.org');
        $this->command->info('  Password: password');
        $this->command->info('  Type: organization');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    }
}