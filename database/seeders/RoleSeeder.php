<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Roles MUST match the names used in routes/web.php middleware
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
                'description' => 'Administrative access to all modules',
                'is_active' => true,
            ],
            [
                'id' => 3,
                'name' => 'ticketing',
                'description' => 'Access to ticketing and ticket sales',
                'is_active' => true,
            ],
            [
                'id' => 4,
                'name' => 'booking',
                'description' => 'Access to booking management',
                'is_active' => true,
            ],
            [
                'id' => 5,
                'name' => 'parking',
                'description' => 'Access to parking management',
                'is_active' => true,
            ],
            [
                'id' => 6,
                'name' => 'monitoring',
                'description' => 'Access to monitoring and reports',
                'is_active' => true,
            ],
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );
        }

        // Assign permissions to superadmin (all permissions)
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
                'delete-users',
                'view-products',
                'create-products',
                'update-products',
                'delete-products',
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
        }

        // Assign permissions to ticketing staff
        $ticketing = Role::where('name', 'ticketing')->first();
        if ($ticketing) {
            $ticketing->syncPermissions([
                'view-ticket-sales',
                'create-ticket-sales',
                'view-products',
            ]);
        }

        // Assign permissions to booking staff
        $booking = Role::where('name', 'booking')->first();
        if ($booking) {
            $booking->syncPermissions([
                'view-bookings',
                'create-bookings',
                'update-bookings',
                'view-products',
            ]);
        }

        // Assign permissions to parking staff
        $parking = Role::where('name', 'parking')->first();
        if ($parking) {
            $parking->syncPermissions([
                'view-parking',
                'manage-parking',
                'view-products',
            ]);
        }

        // Assign permissions to monitoring/reporting staff
        // Monitoring should have access to nearly everything except user/role management
        $monitoring = Role::where('name', 'monitoring')->first();
        if ($monitoring) {
            $monitoring->syncPermissions([
                'view-products',
                'view-bookings',
                'view-ticket-sales',
                'view-reports',
                'export-reports',
                'view-parking',
            ]);
        }
    }
}
