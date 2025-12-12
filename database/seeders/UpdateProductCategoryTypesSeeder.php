<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductCategory;

class UpdateProductCategoryTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Map of category codes to types
        $categoryMappings = [
            // Ticket-related categories
            'ticket_entrance' => 'ticket',   // Tiket Masuk
            'games' => 'ticket',             // Permainan
            'pool' => 'ticket',              // Kolam
            
            // Villa-related
            'villa' => 'villa',
            'room' => 'villa',
            'accommodation' => 'villa',
            
            // Parking
            'parking' => 'parking',
            'parking_space' => 'parking',
            
            // Other (default)
            'other' => 'other',
        ];

        foreach ($categoryMappings as $code => $type) {
            ProductCategory::where('code', $code)
                ->update(['category_type' => $type]);
        }

        // For any categories without explicit mapping, try to infer from name
        $unmappedCategories = ProductCategory::whereNull('category_type')
            ->orWhere('category_type', '')
            ->get();

        foreach ($unmappedCategories as $category) {
            $type = $this->inferTypeFromName($category->name);
            $category->update(['category_type' => $type]);
        }
    }

    /**
     * Infer category type from name
     */
    private function inferTypeFromName(string $name): string
    {
        $name = strtolower($name);

        if (str_contains($name, 'tiket') || str_contains($name, 'ticket')) {
            return 'ticket';
        }
        if (str_contains($name, 'villa') || str_contains($name, 'kamar') || str_contains($name, 'room')) {
            return 'villa';
        }
        if (str_contains($name, 'parkir') || str_contains($name, 'parking')) {
            return 'parking';
        }
        if (str_contains($name, 'permainan') || str_contains($name, 'game')) {
            return 'ticket';
        }
        if (str_contains($name, 'kolam') || str_contains($name, 'pool')) {
            return 'ticket';
        }

        return 'other';
    }
}
