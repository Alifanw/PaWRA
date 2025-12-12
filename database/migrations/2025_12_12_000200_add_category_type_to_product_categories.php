<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add category_type column to distinguish product categories
     * Types: 'ticket' (Tiket Masuk, Permainan, Kolam), 'villa', 'parking'
     * Ini untuk role-based filtering untuk petugas
     */
    public function up(): void
    {
        Schema::table('product_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('product_categories', 'category_type')) {
                $table->enum('category_type', ['ticket', 'villa', 'parking', 'other'])->default('other')->after('name');
                $table->timestamps();
            }
        });
    }

    public function down(): void
    {
        Schema::table('product_categories', function (Blueprint $table) {
            if (Schema::hasColumn('product_categories', 'category_type')) {
                $table->dropColumn(['category_type', 'created_at', 'updated_at']);
            }
        });
    }
};
