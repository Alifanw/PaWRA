<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Menambah kolom product_availability_id ke booking_units untuk tracking
     * ketersediaan mana yang dipesan
     */
    public function up(): void
    {
        Schema::table('booking_units', function (Blueprint $table) {
            $table->unsignedBigInteger('product_availability_id')->nullable()->after('product_id');
            
            $table->foreign('product_availability_id')
                ->references('id')
                ->on('product_availability')
                ->onDelete('set null');
            
            $table->index('product_availability_id', 'idx_booking_units_availability');
        });
    }

    public function down(): void
    {
        Schema::table('booking_units', function (Blueprint $table) {
            $table->dropForeign(['product_availability_id']);
            $table->dropColumn('product_availability_id');
        });
    }
};
