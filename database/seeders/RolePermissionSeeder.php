<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Roles
        $roles = [
            ['name' => 'Super Admin', 'slug' => 'superadmin', 'description' => 'All privileges', 'created_at'=>$now,'updated_at'=>$now],
            ['name' => 'Admin', 'slug' => 'admin', 'description' => 'Administration', 'created_at'=>$now,'updated_at'=>$now],
            ['name' => 'Cashier', 'slug' => 'cashier', 'description' => 'Point of sale', 'created_at'=>$now,'updated_at'=>$now],
            ['name' => 'Employee', 'slug' => 'employee', 'description' => 'Employee user', 'created_at'=>$now,'updated_at'=>$now],
        ];

        DB::table('roles')->insertOrIgnore($roles);

        // Permissions (minimal set)
        $perms = [
            ['name'=>'Manage Users','slug'=>'manage_users','description'=>'Full user management','created_at'=>$now,'updated_at'=>$now],
            ['name'=>'Manage Employees','slug'=>'manage_employees','description'=>'Manage employee master data','created_at'=>$now,'updated_at'=>$now],
            ['name'=>'View Attendance','slug'=>'view_attendance','description'=>'View attendance logs','created_at'=>$now,'updated_at'=>$now],
            ['name'=>'Manage Door','slug'=>'manage_door','description'=>'Operate door locks','created_at'=>$now,'updated_at'=>$now],
            ['name'=>'Manage Bookings','slug'=>'manage_bookings','description'=>'Booking operations','created_at'=>$now,'updated_at'=>$now],
        ];

        DB::table('permissions')->insertOrIgnore($perms);

        // Assign all permissions to superadmin role
        $superRole = DB::table('roles')->where('slug','superadmin')->first();
        $allPerms = DB::table('permissions')->pluck('id')->all();

        foreach ($allPerms as $pid) {
            DB::table('permission_role')->insertOrIgnore(['role_id'=>$superRole->id,'permission_id'=>$pid,'created_at'=>$now,'updated_at'=>$now]);
        }

        // Create default admin user if not exists
        $adminEmail = env('DEFAULT_ADMIN_EMAIL','admin@local');
        $adminPassword = env('DEFAULT_ADMIN_PASSWORD','changeme');

        $existing = DB::table('users')->where('email', $adminEmail)->first();
        if (!$existing) {
            $uid = DB::table('users')->insertGetId([
                'name'=>'Default Admin',
                'email'=>$adminEmail,
                'password'=>Hash::make($adminPassword),
                'created_at'=>$now,
                'updated_at'=>$now,
            ]);

            // assign superadmin role
            DB::table('role_user')->insertOrIgnore(['user_id'=>$uid,'role_id'=>$superRole->id,'created_at'=>$now,'updated_at'=>$now]);
        }
    }
}
