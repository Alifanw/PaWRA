<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SampleDataSeeder extends Seeder
{
    public function run()
    {
        // Add sample product categories
        $categories = [
            ['code' => 'VILLA', 'name' => 'Villa'],
            ['code' => 'COTTAGE', 'name' => 'Cottage'],
            ['code' => 'TICKET', 'name' => 'Tickets'],
        ];

        foreach ($categories as $category) {
            DB::table('product_categories')->insertOrIgnore([
                'code' => $category['code'],
                'name' => $category['name'],
            ]);
        }

        // Get category IDs
        $villaCat = DB::table('product_categories')->where('code', 'VILLA')->first();
        $cottageCat = DB::table('product_categories')->where('code', 'COTTAGE')->first();
        $ticketCat = DB::table('product_categories')->where('code', 'TICKET')->first();

        // Add sample products
        $products = [
            ['category_id' => $villaCat->id, 'code' => 'VILLA-A', 'name' => 'Villa Premium A', 'base_price' => 1500000],
            ['category_id' => $villaCat->id, 'code' => 'VILLA-B', 'name' => 'Villa Standard B', 'base_price' => 1000000],
            ['category_id' => $cottageCat->id, 'code' => 'COT-DLX', 'name' => 'Cottage Deluxe', 'base_price' => 750000],
            ['category_id' => $ticketCat->id, 'code' => 'TIX-ADT', 'name' => 'Adult Ticket', 'base_price' => 50000],
            ['category_id' => $ticketCat->id, 'code' => 'TIX-CHD', 'name' => 'Child Ticket', 'base_price' => 25000],
        ];

        foreach ($products as $product) {
            DB::table('products')->insertOrIgnore([
                'category_id' => $product['category_id'],
                'code' => $product['code'],
                'name' => $product['name'],
                'base_price' => $product['base_price'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Add sample bookings
        $user = DB::table('users')->where('username', 'superadmin')->first();
        
        if ($user) {
            $bookings = [
                [
                    'booking_code' => 'BKG' . date('Ymd') . '0001',
                    'customer_name' => 'John Doe',
                    'customer_phone' => '081234567890',
                    'checkin' => now()->addDays(2)->format('Y-m-d'),
                    'checkout' => now()->addDays(4)->format('Y-m-d'),
                    'night_count' => 2,
                    'room_count' => 1,
                    'total_amount' => 2500000,
                    'status' => 'confirmed',
                    'created_by' => $user->id,
                ],
                [
                    'booking_code' => 'BKG' . date('Ymd') . '0002',
                    'customer_name' => 'Jane Smith',
                    'customer_phone' => '081234567891',
                    'checkin' => now()->addDays(5)->format('Y-m-d'),
                    'checkout' => now()->addDays(7)->format('Y-m-d'),
                    'night_count' => 2,
                    'room_count' => 1,
                    'total_amount' => 1800000,
                    'status' => 'pending',
                    'created_by' => $user->id,
                ],
            ];

            foreach ($bookings as $booking) {
                DB::table('bookings')->insertOrIgnore([
                    'booking_code' => $booking['booking_code'],
                    'customer_name' => $booking['customer_name'],
                    'customer_phone' => $booking['customer_phone'],
                    'checkin' => $booking['checkin'],
                    'checkout' => $booking['checkout'],
                    'night_count' => $booking['night_count'],
                    'room_count' => $booking['room_count'],
                    'total_amount' => $booking['total_amount'],
                    'discount_amount' => 0,
                    'dp_amount' => 0,
                    'status' => $booking['status'],
                    'created_by' => $booking['created_by'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('Sample data seeded successfully!');
    }
}
