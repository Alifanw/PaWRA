<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestDataSeeder extends Seeder
{
    public function run()
    {
        // Add 50 more products for pagination testing
        $categories = DB::table('product_categories')->pluck('id')->toArray();
        
        for ($i = 1; $i <= 50; $i++) {
            DB::table('products')->insertOrIgnore([
                'category_id' => $categories[array_rand($categories)],
                'code' => 'TEST-PROD-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'name' => 'Test Product ' . $i,
                'base_price' => 100000 + ($i * 10000),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Add 50 more bookings
        $user = DB::table('users')->where('email', 'admin@airpanas.local')->first();
        if ($user) {
            for ($i = 1; $i <= 50; $i++) {
                DB::table('bookings')->insertOrIgnore([
                    'booking_code' => 'TEST-' . date('Ymd') . str_pad($i, 4, '0', STR_PAD_LEFT),
                    'customer_name' => 'Test Customer ' . $i,
                    'customer_phone' => '0812' . str_pad($i, 8, '0', STR_PAD_LEFT),
                    'checkin' => now()->addDays($i)->format('Y-m-d'),
                    'checkout' => now()->addDays($i + 2)->format('Y-m-d'),
                    'night_count' => 2,
                    'room_count' => 1,
                    'total_amount' => 500000 + ($i * 50000),
                    'discount_amount' => 0,
                    'dp_amount' => 0,
                    'status' => ['pending', 'confirmed', 'checked_in', 'checked_out'][rand(0, 3)],
                    'created_by' => $user->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('Test data seeded! Added 50 products and 50 bookings.');
    }
}
