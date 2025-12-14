<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('product_availability', 'total_units')) {
            Schema::table('product_availability', function (Blueprint $table) {
                $table->integer('total_units')->default(1)->after('max_capacity');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('product_availability', 'total_units')) {
            Schema::table('product_availability', function (Blueprint $table) {
                $table->dropColumn('total_units');
            });
        }
    }
};
