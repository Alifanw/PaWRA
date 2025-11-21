<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles first
        \DB::table('roles')->insert([
            ['name' => 'superadmin', 'description' => 'Super Administrator - Full Access', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin', 'description' => 'Administrator', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'cashier', 'description' => 'Kasir', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'frontdesk', 'description' => 'Front Desk', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'auditor', 'description' => 'Auditor - Read Only', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Seed role permissions
        $permissions = [
            // Superadmin - wildcard all access
            ['role_id' => 1, 'permission' => '*'],
            
            // Admin permissions
            ['role_id' => 2, 'permission' => 'users.manage'],
            ['role_id' => 2, 'permission' => 'products.manage'],
            ['role_id' => 2, 'permission' => 'products.view'],
            ['role_id' => 2, 'permission' => 'bookings.manage'],
            ['role_id' => 2, 'permission' => 'bookings.view'],
            ['role_id' => 2, 'permission' => 'payments.create'],
            ['role_id' => 2, 'permission' => 'sales.create'],
            ['role_id' => 2, 'permission' => 'sales.view'],
            ['role_id' => 2, 'permission' => 'reports.view'],
            ['role_id' => 2, 'permission' => 'audit.view'],
            
            // Cashier permissions
            ['role_id' => 3, 'permission' => 'products.view'],
            ['role_id' => 3, 'permission' => 'sales.create'],
            ['role_id' => 3, 'permission' => 'sales.view'],
            ['role_id' => 3, 'permission' => 'payments.create'],
            ['role_id' => 3, 'permission' => 'bookings.view'],
            
            // Frontdesk permissions
            ['role_id' => 4, 'permission' => 'products.view'],
            ['role_id' => 4, 'permission' => 'bookings.create'],
            ['role_id' => 4, 'permission' => 'bookings.manage'],
            ['role_id' => 4, 'permission' => 'bookings.view'],
            
            // Auditor permissions
            ['role_id' => 5, 'permission' => 'products.view'],
            ['role_id' => 5, 'permission' => 'bookings.view'],
            ['role_id' => 5, 'permission' => 'sales.view'],
            ['role_id' => 5, 'permission' => 'reports.view'],
            ['role_id' => 5, 'permission' => 'audit.view'],
        ];
        \DB::table('role_permissions')->insert($permissions);

        // Create default superadmin user
        \DB::table('users')->insert([
            'username' => 'superadmin',
            'password' => bcrypt('password'), // CHANGE IN PRODUCTION!
            'full_name' => 'Super Administrator',
            'email' => 'admin@airpanas.local',
            'role_id' => 1,
            'is_block' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create sample admin user
        \DB::table('users')->insert([
            'username' => 'admin',
            'password' => bcrypt('password'),
            'full_name' => 'Admin User',
            'email' => 'admin2@airpanas.local',
            'role_id' => 2,
            'is_block' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create sample cashier user
        \DB::table('users')->insert([
            'username' => 'cashier',
            'password' => bcrypt('password'),
            'full_name' => 'Cashier User',
            'email' => 'cashier@airpanas.local',
            'role_id' => 3,
            'is_block' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
