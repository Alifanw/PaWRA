<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductAvailability;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductAvailabilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Villa Premium A - dengan 3 kamar
        $villaA = Product::where('code', 'VILLA-A')->first();
        if ($villaA) {
            $rooms = ['Kamar A', 'Kamar B', 'Kamar C'];
            foreach ($rooms as $index => $room) {
                ProductAvailability::updateOrCreate(
                    ['unit_code' => 'VILLA_A_ROOM_' . chr(65 + $index)],
                    [
                        'product_id' => $villaA->id,
                        'parent_unit' => 'Villa Premium A',
                        'unit_name' => $room,
                        'max_capacity' => 2,
                        'status' => 'available',
                        'description' => "$room in Villa Premium A",
                    ]
                );
            }
        }

        // Villa Standard B - dengan 3 kamar
        $villaB = Product::where('code', 'VILLA-B')->first();
        if ($villaB) {
            $rooms = ['Kamar A', 'Kamar B', 'Kamar C'];
            foreach ($rooms as $index => $room) {
                ProductAvailability::updateOrCreate(
                    ['unit_code' => 'VILLA_B_ROOM_' . chr(65 + $index)],
                    [
                        'product_id' => $villaB->id,
                        'parent_unit' => 'Villa Standard B',
                        'unit_name' => $room,
                        'max_capacity' => 2,
                        'status' => 'available',
                        'description' => "$room in Villa Standard B",
                    ]
                );
            }
        }

        // Villa Bungalow - dengan 2 kamar
        $villaBungalow = Product::where('code', 'V-BUNGALOW')->first();
        if ($villaBungalow) {
            $rooms = ['Kamar A', 'Kamar B'];
            foreach ($rooms as $index => $room) {
                ProductAvailability::updateOrCreate(
                    ['unit_code' => 'VILLA_BUNGALOW_ROOM_' . chr(65 + $index)],
                    [
                        'product_id' => $villaBungalow->id,
                        'parent_unit' => 'Villa Bungalow',
                        'unit_name' => $room,
                        'max_capacity' => 2,
                        'status' => 'available',
                        'description' => "$room in Villa Bungalow",
                    ]
                );
            }
        }

        // Villa Lumbung - dengan 4 kamar
        $villaLumbung = Product::where('code', 'V-LUMBUNG')->first();
        if ($villaLumbung) {
            $rooms = ['Kamar A', 'Kamar B', 'Kamar C', 'Kamar D'];
            foreach ($rooms as $index => $room) {
                ProductAvailability::updateOrCreate(
                    ['unit_code' => 'VILLA_LUMBUNG_ROOM_' . chr(65 + $index)],
                    [
                        'product_id' => $villaLumbung->id,
                        'parent_unit' => 'Villa Lumbung',
                        'unit_name' => $room,
                        'max_capacity' => 2,
                        'status' => 'available',
                        'description' => "$room in Villa Lumbung",
                    ]
                );
            }
        }

        // Villa Kerucut - dengan 2 kamar
        $villaKerucut = Product::where('code', 'V-KERUCUT')->first();
        if ($villaKerucut) {
            $rooms = ['Kamar A', 'Kamar B'];
            foreach ($rooms as $index => $room) {
                ProductAvailability::updateOrCreate(
                    ['unit_code' => 'VILLA_KERUCUT_ROOM_' . chr(65 + $index)],
                    [
                        'product_id' => $villaKerucut->id,
                        'parent_unit' => 'Villa Kerucut',
                        'unit_name' => $room,
                        'max_capacity' => 2,
                        'status' => 'available',
                        'description' => "$room in Villa Kerucut",
                    ]
                );
            }
        }

        // Villa Panggung - dengan 3 kamar
        $villaPanggung = Product::where('code', 'V-PANGGUNG')->first();
        if ($villaPanggung) {
            $rooms = ['Kamar A', 'Kamar B', 'Kamar C'];
            foreach ($rooms as $index => $room) {
                ProductAvailability::updateOrCreate(
                    ['unit_code' => 'VILLA_PANGGUNG_ROOM_' . chr(65 + $index)],
                    [
                        'product_id' => $villaPanggung->id,
                        'parent_unit' => 'Villa Panggung',
                        'unit_name' => $room,
                        'max_capacity' => 2,
                        'status' => 'available',
                        'description' => "$room in Villa Panggung",
                    ]
                );
            }
        }

        // Cottage Deluxe - dengan 2 kamar
        $cottage = Product::where('code', 'COT-DLX')->first();
        if ($cottage) {
            $rooms = ['Kamar A', 'Kamar B'];
            foreach ($rooms as $index => $room) {
                ProductAvailability::updateOrCreate(
                    ['unit_code' => 'COTTAGE_ROOM_' . chr(65 + $index)],
                    [
                        'product_id' => $cottage->id,
                        'parent_unit' => 'Cottage Deluxe',
                        'unit_name' => $room,
                        'max_capacity' => 2,
                        'status' => 'available',
                        'description' => "$room in Cottage Deluxe",
                    ]
                );
            }
        }
    }
}
