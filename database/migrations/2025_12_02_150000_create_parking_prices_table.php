<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parking_prices', function (Blueprint $table) {
            $table->id();
            $table->string('vehicle_type')->unique(); // roda2, roda4_6
            $table->decimal('price_per_hour', 12, 2)->default(0); // Hourly rate
            $table->decimal('price_per_day', 12, 2)->default(0);   // Daily rate (24 hours)
            $table->decimal('flat_price', 12, 2)->nullable();      // Flat price option
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Insert default pricing: 200 Rp per entry (flat)
        DB::table('parking_prices')->insert([
            [
                'vehicle_type' => 'roda2',
                'price_per_hour' => 200,
                'price_per_day' => 2000,
                'flat_price' => 200,
                'is_active' => true,
                'notes' => 'Motorcycle - 200 Rp flat',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'vehicle_type' => 'roda4_6',
                'price_per_hour' => 500,
                'price_per_day' => 5000,
                'flat_price' => 500,
                'is_active' => true,
                'notes' => 'Car - 500 Rp flat',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('parking_prices');
    }
};
