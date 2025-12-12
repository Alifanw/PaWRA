<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tabel untuk menyimpan multiple codes (physical items) dari satu product
     * Contoh: Product "ATV" bisa memiliki product_codes: atv-1, atv-2, ..., atv-10
     * Setiap code memiliki status tersedia/tidak tersedia
     */
    public function up(): void
    {
        Schema::create('product_codes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('product_id');
            $table->string('code', 50)->unique(); // atv-1, atv-2, villa-lumbing-4, dll
            $table->enum('status', ['available', 'unavailable', 'maintenance'])->default('available');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->index(['product_id', 'status'], 'idx_product_codes_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_codes');
    }
};
