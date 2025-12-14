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
        // ========================================
        // VILLA PRODUCTS - 6 units each
        // ========================================
        
        // Villa Bungalow Weekday
        $villaBungawdWD = Product::where('code', 'VILLA-BUNG-WD')->first();
        if ($villaBungawdWD) {
            for ($i = 1; $i <= 6; $i++) {
                ProductAvailability::updateOrCreate(
                    ['unit_code' => 'VILLA_BUNG_WD_' . $i],
                    [
                        'product_id' => $villaBungawdWD->id,
                        'parent_unit' => 'Villa Bungalow Weekday',
                        'unit_name' => "Bungalow $i",
                        'max_capacity' => 4,
                        'status' => 'available',
                        'description' => "Bungalow unit $i",
                    ]
                );
            }
        }

        // Villa Bungalow Weekend
        $villaBungawdWE = Product::where('code', 'VILLA-BUNG-WE')->first();
        if ($villaBungawdWE) {
            for ($i = 1; $i <= 6; $i++) {
                ProductAvailability::updateOrCreate(
                    ['unit_code' => 'VILLA_BUNG_WE_' . $i],
                    [
                        'product_id' => $villaBungawdWE->id,
                        'parent_unit' => 'Villa Bungalow Weekend',
                        'unit_name' => "Bungalow $i",
                        'max_capacity' => 4,
                        'status' => 'available',
                        'description' => "Bungalow unit $i",
                    ]
                );
            }
        }

        // Villa Kerucut Weekday
        $villaKerWD = Product::where('code', 'VILLA-KER-WD')->first();
        if ($villaKerWD) {
            for ($i = 1; $i <= 6; $i++) {
                ProductAvailability::updateOrCreate(
                    ['unit_code' => 'VILLA_KER_WD_' . $i],
                    [
                        'product_id' => $villaKerWD->id,
                        'parent_unit' => 'Villa Kerucut Weekday',
                        'unit_name' => "Kerucut $i",
                        'max_capacity' => 4,
                        'status' => 'available',
                        'description' => "Kerucut unit $i",
                    ]
                );
            }
        }

        // Villa Kerucut Weekend
        $villaKerWE = Product::where('code', 'VILLA-KER-WE')->first();
        if ($villaKerWE) {
            for ($i = 1; $i <= 6; $i++) {
                ProductAvailability::updateOrCreate(
                    ['unit_code' => 'VILLA_KER_WE_' . $i],
                    [
                        'product_id' => $villaKerWE->id,
                        'parent_unit' => 'Villa Kerucut Weekend',
                        'unit_name' => "Kerucut $i",
                        'max_capacity' => 4,
                        'status' => 'available',
                        'description' => "Kerucut unit $i",
                    ]
                );
            }
        }

        // Villa Lumbung Weekday - 4 units
        $villaLumWD = Product::where('code', 'VILLA-LUM-WD')->first();
        if ($villaLumWD) {
            for ($i = 1; $i <= 4; $i++) {
                ProductAvailability::updateOrCreate(
                    ['unit_code' => 'VILLA_LUM_WD_' . $i],
                    [
                        'product_id' => $villaLumWD->id,
                        'parent_unit' => 'Villa Lumbung Weekday',
                        'unit_name' => "Lumbung $i",
                        'max_capacity' => 6,
                        'status' => 'available',
                        'description' => "Lumbung unit $i",
                    ]
                );
            }
        }

        // Villa Lumbung Weekend - 4 units
        $villaLumWE = Product::where('code', 'VILLA-LUM-WE')->first();
        if ($villaLumWE) {
            for ($i = 1; $i <= 4; $i++) {
                ProductAvailability::updateOrCreate(
                    ['unit_code' => 'VILLA_LUM_WE_' . $i],
                    [
                        'product_id' => $villaLumWE->id,
                        'parent_unit' => 'Villa Lumbung Weekend',
                        'unit_name' => "Lumbung $i",
                        'max_capacity' => 6,
                        'status' => 'available',
                        'description' => "Lumbung unit $i",
                    ]
                );
            }
        }

        // Villa Panggung Weekday
        $villaPangWD = Product::where('code', 'VILLA-PANG-WD')->first();
        if ($villaPangWD) {
            for ($i = 1; $i <= 6; $i++) {
                ProductAvailability::updateOrCreate(
                    ['unit_code' => 'VILLA_PANG_WD_' . $i],
                    [
                        'product_id' => $villaPangWD->id,
                        'parent_unit' => 'Villa Panggung Weekday',
                        'unit_name' => "Panggung $i",
                        'max_capacity' => 4,
                        'status' => 'available',
                        'description' => "Panggung unit $i",
                    ]
                );
            }
        }

        // Villa Panggung Weekend
        $villaPangWE = Product::where('code', 'VILLA-PANG-WE')->first();
        if ($villaPangWE) {
            for ($i = 1; $i <= 6; $i++) {
                ProductAvailability::updateOrCreate(
                    ['unit_code' => 'VILLA_PANG_WE_' . $i],
                    [
                        'product_id' => $villaPangWE->id,
                        'parent_unit' => 'Villa Panggung Weekend',
                        'unit_name' => "Panggung $i",
                        'max_capacity' => 4,
                        'status' => 'available',
                        'description' => "Panggung unit $i",
                    ]
                );
            }
        }

        // ========================================
        // PERMAINAN PRODUCTS - 10 units each
        // ========================================
        
        // GOKAR 50 CC - 10 units
        $gokar = Product::where('code', 'GOKAR-50')->first();
        if ($gokar) {
            for ($i = 1; $i <= 10; $i++) {
                ProductAvailability::updateOrCreate(
                    ['unit_code' => 'GOKAR_50_' . $i],
                    [
                        'product_id' => $gokar->id,
                        'parent_unit' => 'GOKAR 50 CC',
                        'unit_name' => "Gokar #$i",
                        'max_capacity' => 1,
                        'status' => 'available',
                        'description' => "GOKAR 50 CC unit $i",
                    ]
                );
            }
        }

        // ATV 90 CC - 10 units
        $atv90 = Product::where('code', 'ATV-90')->first();
        if ($atv90) {
            for ($i = 1; $i <= 10; $i++) {
                ProductAvailability::updateOrCreate(
                    ['unit_code' => 'ATV_90_' . $i],
                    [
                        'product_id' => $atv90->id,
                        'parent_unit' => 'ATV 90 CC',
                        'unit_name' => "ATV #$i",
                        'max_capacity' => 1,
                        'status' => 'available',
                        'description' => "ATV 90 CC unit $i",
                    ]
                );
            }
        }

        // FLYING FOX MINI - 6 units
        $flyingFoxMini = Product::where('code', 'FFOX-MINI')->first();
        if ($flyingFoxMini) {
            for ($i = 1; $i <= 6; $i++) {
                ProductAvailability::updateOrCreate(
                    ['unit_code' => 'FFOX_MINI_' . $i],
                    [
                        'product_id' => $flyingFoxMini->id,
                        'parent_unit' => 'Flying Fox Mini',
                        'unit_name' => "Station $i",
                        'max_capacity' => 1,
                        'status' => 'available',
                        'description' => "Flying Fox Mini station $i",
                    ]
                );
            }
        }

        // FLYING FOX EXTREME 300M - 4 units
        $flyingFoxExtreme = Product::where('code', 'FFOX-300')->first();
        if ($flyingFoxExtreme) {
            for ($i = 1; $i <= 4; $i++) {
                ProductAvailability::updateOrCreate(
                    ['unit_code' => 'FFOX_300_' . $i],
                    [
                        'product_id' => $flyingFoxExtreme->id,
                        'parent_unit' => 'Flying Fox Extreme 300M',
                        'unit_name' => "Station $i",
                        'max_capacity' => 1,
                        'status' => 'available',
                        'description' => "Flying Fox Extreme 300M station $i",
                    ]
                );
            }
        }

        // ATV TEA TOURS - 6 units
        $atvTea = Product::where('code', 'ATV-TEA')->first();
        if ($atvTea) {
            for ($i = 1; $i <= 6; $i++) {
                ProductAvailability::updateOrCreate(
                    ['unit_code' => 'ATV_TEA_' . $i],
                    [
                        'product_id' => $atvTea->id,
                        'parent_unit' => 'ATV TEA TOURS',
                        'unit_name' => "ATV #$i",
                        'max_capacity' => 1,
                        'status' => 'available',
                        'description' => "ATV TEA TOURS unit $i",
                    ]
                );
            }
        }

        // BAJAY TOUR - 4 units
        $bajay = Product::where('code', 'BAJAY')->first();
        if ($bajay) {
            for ($i = 1; $i <= 4; $i++) {
                ProductAvailability::updateOrCreate(
                    ['unit_code' => 'BAJAY_' . $i],
                    [
                        'product_id' => $bajay->id,
                        'parent_unit' => 'BAJAY TOUR',
                        'unit_name' => "Bajay #$i",
                        'max_capacity' => 4,
                        'status' => 'available',
                        'description' => "BAJAY TOUR unit $i",
                    ]
                );
            }
        }

        // SEPEDA TOUR - 8 units
        $sepeda = Product::where('code', 'SEPEDA')->first();
        if ($sepeda) {
            for ($i = 1; $i <= 8; $i++) {
                ProductAvailability::updateOrCreate(
                    ['unit_code' => 'SEPEDA_' . $i],
                    [
                        'product_id' => $sepeda->id,
                        'parent_unit' => 'SEPEDA TOUR',
                        'unit_name' => "Sepeda #$i",
                        'max_capacity' => 1,
                        'status' => 'available',
                        'description' => "SEPEDA TOUR unit $i",
                    ]
                );
            }
        }

        // BOOGIE - 4 units
        $boogie = Product::where('code', 'BOOGIE')->first();
        if ($boogie) {
            for ($i = 1; $i <= 4; $i++) {
                ProductAvailability::updateOrCreate(
                    ['unit_code' => 'BOOGIE_' . $i],
                    [
                        'product_id' => $boogie->id,
                        'parent_unit' => 'Boogie',
                        'unit_name' => "Boogie Seat $i",
                        'max_capacity' => 1,
                        'status' => 'available',
                        'description' => "Boogie seat $i",
                    ]
                );
            }
        }

        // TIKET MAINAN - 6 units
        $mainan = Product::where('code', 'MAINAN')->first();
        if ($mainan) {
            for ($i = 1; $i <= 6; $i++) {
                ProductAvailability::updateOrCreate(
                    ['unit_code' => 'MAINAN_' . $i],
                    [
                        'product_id' => $mainan->id,
                        'parent_unit' => 'TIKET MAINAN',
                        'unit_name' => "Permainan #$i",
                        'max_capacity' => 1,
                        'status' => 'available',
                        'description' => "TIKET MAINAN unit $i",
                    ]
                );
            }
        }

        // KERETA API MINI - 4 units
        $kereta = Product::where('code', 'KERETA')->first();
        if ($kereta) {
            for ($i = 1; $i <= 4; $i++) {
                ProductAvailability::updateOrCreate(
                    ['unit_code' => 'KERETA_' . $i],
                    [
                        'product_id' => $kereta->id,
                        'parent_unit' => 'KERETA API MINI',
                        'unit_name' => "Kursi #$i",
                        'max_capacity' => 2,
                        'status' => 'available',
                        'description' => "KERETA API MINI seat $i",
                    ]
                );
            }
        }

        // ========================================
        // KOLAM/POOL PRODUCTS - 3-4 units each
        // ========================================
        
        // Kolam Renang (Regular Pool) - 3 sections
        $kolam = Product::where('code', 'KOLAM')->first();
        if ($kolam) {
            $pools = ['Area A - Dewasa', 'Area B - Anak-anak', 'Area C - Bayi'];
            foreach ($pools as $index => $poolName) {
                ProductAvailability::updateOrCreate(
                    ['unit_code' => 'POOL_REG_' . chr(65 + $index)],
                    [
                        'product_id' => $kolam->id,
                        'parent_unit' => 'Kolam Renang',
                        'unit_name' => $poolName,
                        'max_capacity' => 50,
                        'status' => 'available',
                        'description' => $poolName,
                    ]
                );
            }
        }

        // Kolam Renang Keluarga (Family Pool) - 4 sections
        $kolamFamily = Product::where('code', 'KOLAM-FAM')->first();
        if ($kolamFamily) {
            $poolSections = ['Kolam 1', 'Kolam 2', 'Kolam 3', 'Kolam 4'];
            foreach ($poolSections as $index => $section) {
                ProductAvailability::updateOrCreate(
                    ['unit_code' => 'POOL_FAM_' . ($index + 1)],
                    [
                        'product_id' => $kolamFamily->id,
                        'parent_unit' => 'Kolam Renang Keluarga',
                        'unit_name' => $section,
                        'max_capacity' => 30,
                        'status' => 'available',
                        'description' => "Family pool section: $section",
                    ]
                );
            }
        }

        // Kamar Rendam (Bathing Room) - 4 units
        $kamarRendam = Product::where('code', 'KAMAR-RENDAM')->first();
        if ($kamarRendam) {
            for ($i = 1; $i <= 4; $i++) {
                ProductAvailability::updateOrCreate(
                    ['unit_code' => 'BATH_ROOM_' . $i],
                    [
                        'product_id' => $kamarRendam->id,
                        'parent_unit' => 'Kamar Rendam',
                        'unit_name' => "Ruang $i",
                        'max_capacity' => 4,
                        'status' => 'available',
                        'description' => "Bathing room $i",
                    ]
                );
            }
        }

        // Terapi Ikan (Fish Therapy) - 3 pools
        $terapiIkan = Product::where('code', 'TERAPI-IKAN')->first();
        if ($terapiIkan) {
            $therapyPools = ['Pool A - Panas', 'Pool B - Sejuk', 'Pool C - Medium'];
            foreach ($therapyPools as $index => $poolName) {
                ProductAvailability::updateOrCreate(
                    ['unit_code' => 'THERAPY_POOL_' . chr(65 + $index)],
                    [
                        'product_id' => $terapiIkan->id,
                        'parent_unit' => 'Terapi Ikan',
                        'unit_name' => $poolName,
                        'max_capacity' => 6,
                        'status' => 'available',
                        'description' => $poolName,
                    ]
                );
            }
        }

        $this->command->info('✅ Product availability seeding completed!');
        $this->command->info('   - Villas: 6/4 units each');
        $this->command->info('   - Games: 4-10 units each');
        $this->command->info('   - Pools: 3-4 sections/rooms each');
    }
}

