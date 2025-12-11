<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table untuk mengelola ketersediaan (availability) dari setiap produk booking
     * Contoh: Villa Lumbing 4, Villa Lumbing 5, Aula Utama, dll
     */
    public function up(): void
    {
        Schema::create('product_availability', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('product_id');
            $table->string('parent_unit', 100)->nullable(); // Villa name (e.g., "Villa Lumbing 4")
            $table->string('unit_name', 100); // Room name (e.g., "Kamar A", "Kamar B")
            $table->string('unit_code', 50)->unique(); // e.g., "VILLA_LUMBING_4_ROOM_A"
            $table->integer('max_capacity')->default(2); // max pax per room
            $table->enum('status', ['available', 'unavailable', 'maintenance'])->default('available');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->index(['product_id', 'status'], 'idx_availability_product_status');
            $table->index(['product_id', 'parent_unit'], 'idx_availability_parent_unit');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_availability');
    }
};
