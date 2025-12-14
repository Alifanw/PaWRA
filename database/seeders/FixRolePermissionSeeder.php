<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class FixRolePermissionSeeder extends Seeder
{
    /**
     * Seed the application's database to fix role permissions.
     * This ensures all roles have correct names and permissions.
     */
    public function run(): void
    {
        // Step 1: Create/Update Core Roles
        $roles = [
            ['id' => 1, 'name' => 'superadmin', 'description' => 'Full system access with all permissions', 'is_active' => true],
            ['id' => 2, 'name' => 'admin', 'description' => 'Administrative access to all modules', 'is_active' => true],
            ['id' => 3, 'name' => 'ticketing', 'description' => 'Access to ticketing and ticket sales', 'is_active' => true],
            ['id' => 4, 'name' => 'booking', 'description' => 'Access to booking management', 'is_active' => true],
            ['id' => 5, 'name' => 'parking', 'description' => 'Access to parking management', 'is_active' => true],
            ['id' => 6, 'name' => 'monitoring', 'description' => 'Access to monitoring and reports', 'is_active' => true],
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );
        }

        // Step 2: Sync Permissions for Each Role
        $this->syncRolePermissions();

        // Step 3: Create/Update Test Users
        $this->createTestUsers();

        $this->command->info('✅ Role permissions fixed successfully!');
    }

    /**
     * Sync permissions for each role
     */
    private function syncRolePermissions(): void
    {
        // SUPERADMIN - All permissions
        $superadmin = Role::where('name', 'superadmin')->first();
        if ($superadmin) {
            $superadmin->syncPermissions([
                // Roles
                'view-roles',
                'create-roles',
                'update-roles',
                'delete-roles',
                'manage-role-permissions',
                // Users
                'view-users',
                'create-users',
                'update-users',
                'delete-users',
                // Products
                'view-products',
                'create-products',
                'update-products',
                'delete-products',
                'update-product-availability',
                // Bookings
                'view-bookings',
                'create-bookings',
                'update-bookings',
                'cancel-bookings',
                // Ticket Sales
                'view-ticket-sales',
                'create-ticket-sales',
                'refund-tickets',
                // Parking
                'view-parking',
                'manage-parking',
                // Reports
                'view-reports',
                'export-reports',
            ]);
            $this->command->info('  ✓ superadmin permissions synced');
        }

        // ADMIN - Administrative permissions
        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->syncPermissions([
                'view-users',
                'create-users',
                'update-users',
                'delete-users',
                'view-products',
                'create-products',
                'update-products',
                'delete-products',
                'update-product-availability',
                'view-bookings',
                'create-bookings',
                'update-bookings',
                'cancel-bookings',
                'view-ticket-sales',
                'create-ticket-sales',
                'refund-tickets',
                'view-reports',
                'export-reports',
            ]);
            $this->command->info('  ✓ admin permissions synced');
        }

        // TICKETING - Ticket sales only
        $ticketing = Role::where('name', 'ticketing')->first();
        if ($ticketing) {
            $ticketing->syncPermissions([
                'view-ticket-sales',
                'create-ticket-sales',
                'view-products',
                'view-ticket-reports',
            ]);
            $this->command->info('  ✓ ticketing permissions synced');
        }

        // BOOKING - Booking management only
        $booking = Role::where('name', 'booking')->first();
        if ($booking) {
            $booking->syncPermissions([
                'view-bookings',
                'create-bookings',
                'update-bookings',
                'view-products',
                'view-booking-reports',
            ]);
            $this->command->info('  ✓ booking permissions synced');
        }

        // PARKING - Parking management only
        $parking = Role::where('name', 'parking')->first();
        if ($parking) {
            $parking->syncPermissions([
                'view-parking',
                'manage-parking',
                'view-products',
            ]);
            $this->command->info('  ✓ parking permissions synced');
        }

        // MONITORING - Monitoring and reports only (no Users / Roles management)
        $monitoring = Role::where('name', 'monitoring')->first();
        if ($monitoring) {
            $monitoring->syncPermissions([
                // intentionally exclude 'view-users' and role management perms
                'view-products',
                'view-bookings',
                'view-ticket-sales',
                'view-reports',
                'export-reports',
                'view-parking',
            ]);
            $this->command->info('  ✓ monitoring permissions synced (users/roles excluded)');
        }
    }

    /**
     * Create test users for each role
     */
    private function createTestUsers(): void
    {
        $users = [
            [
                'email' => 'superadmin@airpanas.local',
                'username' => 'superadmin',
                'password' => Hash::make('Admin123!'),
                'name' => 'Super Administrator',
                'role_name' => 'superadmin',
            ],
            [
                'email' => 'admin@airpanas.local',
                'username' => 'admin',
                'password' => Hash::make('123123'),
                'name' => 'Administrator',
                'role_name' => 'admin',
            ],
            [
                'email' => 'ticket@airpanas.local',
                'username' => 'ticketing',
                'password' => Hash::make('123123'),
                'name' => 'Ticketing Staff',
                'role_name' => 'ticketing',
            ],
            [
                'email' => 'booking@airpanas.local',
                'username' => 'booking',
                'password' => Hash::make('123123'),
                'name' => 'Booking Staff',
                'role_name' => 'booking',
            ],
            [
                'email' => 'parking@airpanas.local',
                'username' => 'parking',
                'password' => Hash::make('123123'),
                'name' => 'Parking Staff',
                'role_name' => 'parking',
            ],
            [
                'email' => 'monitor@airpanas.local',
                'username' => 'monitoring',
                'password' => Hash::make('123123'),
                'name' => 'Monitoring Staff',
                'role_name' => 'monitoring',
            ],
        ];

        foreach ($users as $userData) {
            $roleName = $userData['role_name'];
            unset($userData['role_name']);

            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    ...$userData,
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );

            // Attach role if table exists
            if (Schema::hasTable('role_user')) {
                $role = Role::where('name', $roleName)->first();
                if ($role) {
                    $user->roles()->sync([$role->id]);
                    $this->command->info("  ✓ User '{$user->email}' created with role '{$roleName}'");
                }
            }
        }
    }
}
