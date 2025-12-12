<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class CreateStaffRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Petugas Tiket',
                'slug' => 'ticket_staff',
                'description' => 'Mengelola tiket masuk, permainan, dan kolam',
                'is_active' => true,
            ],
            [
                'name' => 'Petugas Villa',
                'slug' => 'villa_staff',
                'description' => 'Mengelola villa dan reservasi',
                'is_active' => true,
            ],
            [
                'name' => 'Petugas Parkir',
                'slug' => 'parking_staff',
                'description' => 'Mengelola parkir',
                'is_active' => true,
            ],
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(
                ['slug' => $roleData['slug']],
                $roleData
            );
        }
    }
}
