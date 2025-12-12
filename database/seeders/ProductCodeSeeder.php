<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductCode;

class ProductCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all products
        $products = Product::all();

        foreach ($products as $product) {
            // Generate product codes based on product type
            $codes = $this->generateCodesForProduct($product);
            
            foreach ($codes as $code) {
                ProductCode::firstOrCreate(
                    ['code' => $code['code']],
                    [
                        'product_id' => $product->id,
                        'status' => $code['status'] ?? 'available',
                        'notes' => $code['notes'] ?? null,
                    ]
                );
            }
        }
    }

    /**
     * Generate codes for a product based on its category and name
     */
    private function generateCodesForProduct(Product $product): array
    {
        $codes = [];
        
        // Get product category to determine quantity
        $category = $product->category;
        $productCode = strtolower(str_replace(' ', '-', $product->code));
        
        // Determine default quantity based on product type
        $defaultQty = match ($category?->code) {
            'villa' => 5,           // Each villa product has 5 units
            'atv', 'game' => 10,    // ATV and games have 10 units each
            'pool' => 3,            // Pools have 3 units each
            default => 1,           // Others default to 1
        };

        // Generate codes
        for ($i = 1; $i <= $defaultQty; $i++) {
            $codes[] = [
                'code' => "{$productCode}-{$i}",
                'status' => 'available',
                'notes' => "Unit {$i} - {$product->name}",
            ];
        }

        return $codes;
    }
}
