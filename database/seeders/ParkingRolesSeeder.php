<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Carbon;

class ParkingRolesSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Roles
        $roles = [
            ['name' => 'Petugas Tiket', 'slug' => 'petugas_tiket', 'description' => 'Handle parking ticket transactions', 'created_at'=>$now,'updated_at'=>$now],
            ['name' => 'Petugas Booking Parkir', 'slug' => 'petugas_booking', 'description' => 'Manage parking bookings', 'created_at'=>$now,'updated_at'=>$now],
            ['name' => 'Petugas Monitoring', 'slug' => 'petugas_monitoring', 'description' => 'Monitor parking and alerts', 'created_at'=>$now,'updated_at'=>$now],
        ];
        DB::table('roles')->insertOrIgnore($roles);

        // Permissions
        $perms = [
            ['name'=>'Parkir Create','slug'=>'parkir.create','description'=>'Create parking transaction','created_at'=>$now,'updated_at'=>$now],
            ['name'=>'Parkir Booking','slug'=>'parkir.booking','description'=>'Create and manage parking bookings','created_at'=>$now,'updated_at'=>$now],
            ['name'=>'Parkir Monitor','slug'=>'parkir.monitor','description'=>'Monitor parking status and logs','created_at'=>$now,'updated_at'=>$now],
            ['name'=>'Parkir View Logs','slug'=>'parkir.view_logs','description'=>'View parking transaction logs','created_at'=>$now,'updated_at'=>$now],
        ];
        DB::table('permissions')->insertOrIgnore($perms);

        // Assign perms to roles
        $roleIds = DB::table('roles')->whereIn('slug',['petugas_tiket','petugas_booking','petugas_monitoring'])->pluck('id','slug');
        $permIds = DB::table('permissions')->whereIn('slug',['parkir.create','parkir.booking','parkir.monitor','parkir.view_logs'])->pluck('id','slug');

        // petugas_tiket -> parkir.create, parkir.view_logs
        DB::table('permission_role')->insertOrIgnore([
            ['role_id'=>$roleIds['petugas_tiket'],'permission_id'=>$permIds['parkir.create'],'created_at'=>$now,'updated_at'=>$now],
            ['role_id'=>$roleIds['petugas_tiket'],'permission_id'=>$permIds['parkir.view_logs'],'created_at'=>$now,'updated_at'=>$now],
        ]);

        // petugas_booking -> parkir.booking, parkir.view_logs
        DB::table('permission_role')->insertOrIgnore([
            ['role_id'=>$roleIds['petugas_booking'],'permission_id'=>$permIds['parkir.booking'],'created_at'=>$now,'updated_at'=>$now],
            ['role_id'=>$roleIds['petugas_booking'],'permission_id'=>$permIds['parkir.view_logs'],'created_at'=>$now,'updated_at'=>$now],
        ]);

        // petugas_monitoring -> parkir.monitor, parkir.view_logs
        DB::table('permission_role')->insertOrIgnore([
            ['role_id'=>$roleIds['petugas_monitoring'],'permission_id'=>$permIds['parkir.monitor'],'created_at'=>$now,'updated_at'=>$now],
            ['role_id'=>$roleIds['petugas_monitoring'],'permission_id'=>$permIds['parkir.view_logs'],'created_at'=>$now,'updated_at'=>$now],
        ]);
    }
}
