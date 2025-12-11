<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'id' => 1,
                'name' => 'superadmin',
                'description' => 'Full system access with all permissions',
                'is_active' => true,
            ],
            [
                'id' => 2,
                'name' => 'admin',
                'description' => 'Administrative access',
                'is_active' => true,
            ],
            [
                'id' => 3,
                'name' => 'cashier',
                'description' => 'Cashier access for ticket sales',
                'is_active' => true,
            ],
            [
                'id' => 4,
                'name' => 'ticket-officer',
                'description' => 'Manage ticket sales and customers',
                'is_active' => true,
            ],
            [
                'id' => 5,
                'name' => 'booking-officer',
                'description' => 'Manage bookings and availability',
                'is_active' => true,
            ],
            [
                'id' => 6,
                'name' => 'parking-attendant',
                'description' => 'Parking management',
                'is_active' => true,
            ],
            [
                'id' => 7,
                'name' => 'monitoring',
                'description' => 'System monitoring and reporting',
                'is_active' => true,
            ],
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );
        }

        // Assign permissions to superadmin
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
        }

        // Assign permissions to admin
        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->syncPermissions([
                'view-users',
                'create-users',
                'update-users',
                'view-products',
                'create-products',
                'update-products',
                'view-bookings',
                'update-bookings',
                'view-ticket-sales',
                'view-reports',
                'export-reports',
            ]);
        }

        // Assign permissions to cashier
        $cashier = Role::where('name', 'cashier')->first();
        if ($cashier) {
            $cashier->syncPermissions([
                'view-ticket-sales',
                'create-ticket-sales',
                'view-products',
            ]);
        }
    }
}
