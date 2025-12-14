<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SampleDataSeeder extends Seeder
{
    public function run()
    {
        // Add product categories
        $categories = [
            ['code' => 'TIKET', 'name' => 'Tiket', 'category_type' => 'ticket'],
            ['code' => 'PARKIR', 'name' => 'Parkir', 'category_type' => 'parking'],
            ['code' => 'VILLA', 'name' => 'Villa', 'category_type' => 'villa'],
        ];

        foreach ($categories as $category) {
            DB::table('product_categories')->updateOrInsert(
                ['code' => $category['code']],
                ['name' => $category['name'], 'category_type' => $category['category_type']]
            );
        }

        // Get category IDs
        $ticketCat = DB::table('product_categories')->where('code', 'TIKET')->first();
        $parkingCat = DB::table('product_categories')->where('code', 'PARKIR')->first();
        $villaCat = DB::table('product_categories')->where('code', 'VILLA')->first();

        // Add products
        $products = [
            // Ticket products
            ['id' => 1, 'category_id' => $ticketCat->id, 'code' => 'GOKAR-50', 'name' => 'GOKAR 50 CC', 'base_price' => 25000],
            ['id' => 2, 'category_id' => $ticketCat->id, 'code' => 'ATV-90', 'name' => 'ATV 90 CC', 'base_price' => 50000],
            ['id' => 3, 'category_id' => $ticketCat->id, 'code' => 'ATV-TEA', 'name' => 'ATV TEA TOURS', 'base_price' => 100000],
            ['id' => 4, 'category_id' => $ticketCat->id, 'code' => 'FFOX-MINI', 'name' => 'FLYING FOX MINI', 'base_price' => 15000],
            ['id' => 5, 'category_id' => $ticketCat->id, 'code' => 'FFOX-300', 'name' => 'FLYING FOX EXTREME 300M', 'base_price' => 50000],
            ['id' => 6, 'category_id' => $ticketCat->id, 'code' => 'BAJAY', 'name' => 'BAJAY TOUR', 'base_price' => 50000],
            ['id' => 7, 'category_id' => $ticketCat->id, 'code' => 'SEPEDA', 'name' => 'SEPEDA TOUR', 'base_price' => 25000],
            ['id' => 8, 'category_id' => $ticketCat->id, 'code' => 'BOOGIE', 'name' => 'BOOGIE', 'base_price' => 100000],
            ['id' => 9, 'category_id' => $ticketCat->id, 'code' => 'MAINAN', 'name' => 'TIKET MAINAN', 'base_price' => 3000],
            ['id' => 10, 'category_id' => $ticketCat->id, 'code' => 'KERETA', 'name' => 'KERETA API MINI', 'base_price' => 15000],
            ['id' => 11, 'category_id' => $ticketCat->id, 'code' => 'KOLAM', 'name' => 'Kolam Renang', 'base_price' => 40000],
            ['id' => 12, 'category_id' => $ticketCat->id, 'code' => 'KOLAM-FAM', 'name' => 'Kolam Renang Keluarga', 'base_price' => 60000],
            ['id' => 13, 'category_id' => $ticketCat->id, 'code' => 'KAMAR-RENDAM', 'name' => 'Kamar Rendam', 'base_price' => 50000],
            ['id' => 14, 'category_id' => $ticketCat->id, 'code' => 'TERAPI-IKAN', 'name' => 'Terapi Ikan', 'base_price' => 30000],
            ['id' => 15, 'category_id' => $ticketCat->id, 'code' => 'WALINI', 'name' => 'tiket walini', 'base_price' => 40000],
            
            // Parking products
            ['id' => 16, 'category_id' => $parkingCat->id, 'code' => 'PARKIR-2', 'name' => 'PARKIR RODA 2', 'base_price' => 2000],
            ['id' => 17, 'category_id' => $parkingCat->id, 'code' => 'PARKIR-4', 'name' => 'PARKIR RODA 4', 'base_price' => 5000],
            ['id' => 18, 'category_id' => $parkingCat->id, 'code' => 'PARKIR-6', 'name' => 'PARKIR RODA 6', 'base_price' => 5000],
            
            // Villa products
            ['id' => 23, 'category_id' => $villaCat->id, 'code' => 'VILLA-BUNG-WD', 'name' => 'villa bungalow - weekday', 'base_price' => 700000],
            ['id' => 24, 'category_id' => $villaCat->id, 'code' => 'VILLA-BUNG-WE', 'name' => 'villa bungalow - weekend', 'base_price' => 400000],
            ['id' => 25, 'category_id' => $villaCat->id, 'code' => 'VILLA-KER-WD', 'name' => 'villa kerucut - weekday', 'base_price' => 400000],
            ['id' => 26, 'category_id' => $villaCat->id, 'code' => 'VILLA-KER-WE', 'name' => 'villa kerucut - weekend', 'base_price' => 400000],
            ['id' => 27, 'category_id' => $villaCat->id, 'code' => 'VILLA-LUM-WD', 'name' => 'villa lumbung - weekday', 'base_price' => 700000],
            ['id' => 28, 'category_id' => $villaCat->id, 'code' => 'VILLA-LUM-WE', 'name' => 'villa lumbung - weekend', 'base_price' => 400000],
            ['id' => 29, 'category_id' => $villaCat->id, 'code' => 'VILLA-PANG-WD', 'name' => 'villa panggung - weekday', 'base_price' => 700000],
            ['id' => 30, 'category_id' => $villaCat->id, 'code' => 'VILLA-PANG-WE', 'name' => 'villa panggung - weekend', 'base_price' => 400000],
        ];

        foreach ($products as $product) {
            DB::table('products')->updateOrInsert(
                ['id' => $product['id']],
                [
                    'category_id' => $product['category_id'],
                    'code' => $product['code'],
                    'name' => $product['name'],
                    'base_price' => $product['base_price'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // Add sample bookings
        $user = DB::table('users')->where('email', 'admin@airpanas.local')->first();
        
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
