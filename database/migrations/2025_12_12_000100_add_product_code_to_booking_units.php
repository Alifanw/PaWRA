<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add product_code_id column to booking_units table
     * Untuk menghubungkan booking unit dengan product code spesifik (physical item)
     */
    public function up(): void
    {
        Schema::table('booking_units', function (Blueprint $table) {
            // Add column after product_id if not exists
            if (!Schema::hasColumn('booking_units', 'product_code_id')) {
                $table->unsignedBigInteger('product_code_id')->nullable()->after('product_id');
                $table->foreign('product_code_id')->references('id')->on('product_codes')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('booking_units', function (Blueprint $table) {
            if (Schema::hasColumn('booking_units', 'product_code_id')) {
                $table->dropForeign(['product_code_id']);
                $table->dropColumn('product_code_id');
            }
        });
    }
};
