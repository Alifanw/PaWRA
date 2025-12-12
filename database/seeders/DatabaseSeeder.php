<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
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
        // Seed roles first (idempotent using Model)
        $baseRoles = [
            ['name' => 'superadmin', 'description' => 'Super Administrator - Full Access', 'is_active' => true],
            ['name' => 'admin', 'description' => 'Administrator', 'is_active' => true],
            ['name' => 'cashier', 'description' => 'Kasir', 'is_active' => true],
            ['name' => 'frontdesk', 'description' => 'Front Desk', 'is_active' => true],
            ['name' => 'auditor', 'description' => 'Auditor - Read Only', 'is_active' => true],
        ];

        foreach ($baseRoles as $r) {
            Role::updateOrCreate(
                ['name' => $r['name']],
                ['description' => $r['description'], 'is_active' => $r['is_active']]
            );
        }

        // Seed role permissions (idempotent)
        $permissionsByRole = [
            'superadmin' => ['*'],
            'admin' => ['users.manage','products.manage','products.view','bookings.manage','bookings.view','payments.create','sales.create','sales.view','reports.view','audit.view'],
            'cashier' => ['products.view','sales.create','sales.view','payments.create','bookings.view'],
            'frontdesk' => ['products.view','bookings.create','bookings.manage','bookings.view'],
            'auditor' => ['products.view','bookings.view','sales.view','reports.view','audit.view'],
        ];

        foreach ($permissionsByRole as $roleName => $perms) {
            $role = \DB::table('roles')->where('name', $roleName)->first();
            if (!$role) continue;
            foreach ($perms as $perm) {
                \DB::table('role_permissions')->updateOrInsert(
                    ['role_id' => $role->id, 'permission' => $perm],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
        }

        // Create or update default superadmin user and ensure pivot role_user exists
        $superEmail = 'admin@airpanas.local';
        $superRoleId = \DB::table('roles')->where('name', 'superadmin')->value('id') ?: 1;

        \DB::table('users')->updateOrInsert(
            ['email' => $superEmail],
            [
                'username' => 'admin',
                'name' => 'Super Administrator',
                'full_name' => 'Super Administrator',
                'email' => $superEmail,
                'password' => bcrypt('Admin123!'), // DEFAULT PASSWORD FOR DEMO
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'email_verified_at' => now(),
            ]
        );

        // Ensure the role_user pivot row exists for superadmin (if pivot table exists)
        if (\Illuminate\Support\Facades\Schema::hasTable('role_user')) {
            $superId = \DB::table('users')->where('email', $superEmail)->value('id');
            if ($superId && $superRoleId) {
                \DB::table('role_user')->updateOrInsert(
                    ['role_id' => $superRoleId, 'user_id' => $superId],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
        }

        // Also create a superadmin alias user with username 'superadmin' (many people try this)
        \DB::table('users')->updateOrInsert(
            ['email' => 'superadmin@airpanas.local'],
            [
                'username' => 'superadmin',
                'name' => 'Super Administrator',
                'full_name' => 'Super Administrator',
                'email' => 'superadmin@airpanas.local',
                'password' => bcrypt('Admin123!'),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'email_verified_at' => now(),
            ]
        );

        if (\Illuminate\Support\Facades\Schema::hasTable('role_user')) {
            $aliasId = \DB::table('users')->where('email', 'superadmin@airpanas.local')->value('id');
            if ($aliasId && $superRoleId) {
                \DB::table('role_user')->updateOrInsert(
                    ['role_id' => $superRoleId, 'user_id' => $aliasId],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
        }

        // Create or update sample admin user
        \DB::table('users')->updateOrInsert(
            ['email' => 'admin2@airpanas.local'],
            [
                'username' => 'admin2',
                'name' => 'Admin User',
                'full_name' => 'Admin User',
                'password' => bcrypt('123123'),
                'is_active' => true,
                'role_id' => 2, // admin
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
        // Ensure pivot for admin2 (if pivot table exists)
        if (\Illuminate\Support\Facades\Schema::hasTable('role_user')) {
            $admin2Id = \DB::table('users')->where('email', 'admin2@airpanas.local')->value('id');
            $adminRoleId = \DB::table('roles')->where('name', 'admin')->value('id');
            if ($admin2Id && $adminRoleId) {
                \DB::table('role_user')->updateOrInsert(
                    ['role_id' => $adminRoleId, 'user_id' => $admin2Id],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
        }

        // Create or update sample cashier user
        \DB::table('users')->updateOrInsert(
            ['email' => 'cashier@airpanas.local'],
            [
                'username' => 'cashier',
                'name' => 'Cashier User',
                'full_name' => 'Cashier User',
                'password' => bcrypt('123123'),
                'is_active' => true,
                'role_id' => 3, // cashier
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
        // Ensure pivot for cashier (if pivot table exists)
        if (\Illuminate\Support\Facades\Schema::hasTable('role_user')) {
            $cashierId = \DB::table('users')->where('email', 'cashier@airpanas.local')->value('id');
            $cashierRoleId = \DB::table('roles')->where('name', 'cashier')->value('id');
            if ($cashierId && $cashierRoleId) {
                \DB::table('role_user')->updateOrInsert(
                    ['role_id' => $cashierRoleId, 'user_id' => $cashierId],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
        }

        // Additional roles for domain-specific access (monitoring, booking, ticketing, parking)
        $extraRoles = [
            ['name' => 'monitoring', 'description' => 'Monitoring - Parking/Visitor Monitor', 'is_active' => true],
            ['name' => 'booking', 'description' => 'Booking - Manage Bookings', 'is_active' => true],
            ['name' => 'ticketing', 'description' => 'Ticketing - POS and Sales', 'is_active' => true],
            ['name' => 'parking', 'description' => 'Parking - Manage Parking Transactions', 'is_active' => true],
        ];

        foreach ($extraRoles as $r) {
            Role::updateOrCreate(
                ['name' => $r['name']],
                ['description' => $r['description'], 'is_active' => $r['is_active']]
            );
        }

        // Resolve role ids for newly created roles (best-effort: select by name)
        $monitoringRole = \DB::table('roles')->where('name', 'monitoring')->first();
        $bookingRole = \DB::table('roles')->where('name', 'booking')->first();
        $ticketingRole = \DB::table('roles')->where('name', 'ticketing')->first();
        $parkingRole = \DB::table('roles')->where('name', 'parking')->first();

        // Add basic permissions for the new roles (idempotent)
        $newPerms = [];
        if ($monitoringRole) {
            $newPerms[] = ['role_id' => $monitoringRole->id, 'permission' => 'parking.monitor'];
            $newPerms[] = ['role_id' => $monitoringRole->id, 'permission' => 'monitoring.view'];
        }
        if ($bookingRole) {
            $newPerms[] = ['role_id' => $bookingRole->id, 'permission' => 'bookings.manage'];
            $newPerms[] = ['role_id' => $bookingRole->id, 'permission' => 'bookings.view'];
        }
        if ($ticketingRole) {
            $newPerms[] = ['role_id' => $ticketingRole->id, 'permission' => 'sales.create'];
            $newPerms[] = ['role_id' => $ticketingRole->id, 'permission' => 'sales.view'];
        }
        if ($parkingRole) {
            $newPerms[] = ['role_id' => $parkingRole->id, 'permission' => 'parking.manage'];
            $newPerms[] = ['role_id' => $parkingRole->id, 'permission' => 'parking.view'];
        }
        foreach ($newPerms as $p) {
            \DB::table('role_permissions')->updateOrInsert(
                ['role_id' => $p['role_id'], 'permission' => $p['permission']],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        // Create sample users for the new roles (keep superadmin untouched)
        if ($monitoringRole) {
            \DB::table('users')->updateOrInsert(
                ['email' => 'monitor@airpanas.local'],
                [
                    'username' => 'monitor',
                    'password' => bcrypt('123123'),
                    'name' => 'Monitoring User',
                    'full_name' => 'Monitoring User',
                    'email' => 'monitor@airpanas.local',
                    'is_active' => true,
                    'role_id' => $monitoringRole->id,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
            $mid = \DB::table('users')->where('email', 'monitor@airpanas.local')->value('id');
            if ($mid && \Illuminate\Support\Facades\Schema::hasTable('role_user')) {
                \DB::table('role_user')->updateOrInsert(['role_id' => $monitoringRole->id, 'user_id' => $mid], ['created_at' => now(), 'updated_at' => now()]);
            }
        }

        if ($bookingRole) {
            \DB::table('users')->updateOrInsert(
                ['email' => 'booking@airpanas.local'],
                [
                    'username' => 'booking',
                    'password' => bcrypt('123123'),
                    'name' => 'Booking User',
                    'full_name' => 'Booking User',
                    'email' => 'booking@airpanas.local',
                    'is_active' => true,
                    'role_id' => $bookingRole->id,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
            $bid = \DB::table('users')->where('email', 'booking@airpanas.local')->value('id');
            if ($bid && \Illuminate\Support\Facades\Schema::hasTable('role_user')) {
                \DB::table('role_user')->updateOrInsert(['role_id' => $bookingRole->id, 'user_id' => $bid], ['created_at' => now(), 'updated_at' => now()]);
            }
        }

        if ($ticketingRole) {
            \DB::table('users')->updateOrInsert(
                ['email' => 'ticket@airpanas.local'],
                [
                    'username' => 'ticketing',
                    'password' => bcrypt('123123'),
                    'name' => 'Ticketing User',
                    'full_name' => 'Ticketing User',
                    'email' => 'ticket@airpanas.local',
                    'is_active' => true,
                    'role_id' => $ticketingRole->id,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
            $tid = \DB::table('users')->where('email', 'ticket@airpanas.local')->value('id');
            if ($tid && \Illuminate\Support\Facades\Schema::hasTable('role_user')) {
                \DB::table('role_user')->updateOrInsert(['role_id' => $ticketingRole->id, 'user_id' => $tid], ['created_at' => now(), 'updated_at' => now()]);
            }
        }

        if ($parkingRole) {
            \DB::table('users')->updateOrInsert(
                ['email' => 'parking@airpanas.local'],
                [
                    'username' => 'parking',
                    'password' => bcrypt('123123'),
                    'name' => 'Parking User',
                    'full_name' => 'Parking User',
                    'email' => 'parking@airpanas.local',
                    'is_active' => true,
                    'role_id' => $parkingRole->id,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
            $pid = \DB::table('users')->where('email', 'parking@airpanas.local')->value('id');
            if ($pid && \Illuminate\Support\Facades\Schema::hasTable('role_user')) {
                \DB::table('role_user')->updateOrInsert(['role_id' => $parkingRole->id, 'user_id' => $pid], ['created_at' => now(), 'updated_at' => now()]);
            }
        }
    }
}
