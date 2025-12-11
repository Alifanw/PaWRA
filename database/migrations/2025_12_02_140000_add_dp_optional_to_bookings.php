<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Whether DP is required for this booking
            $table->boolean('dp_required')->default(true)->after('dp_amount');
            // DP percentage (e.g., 30% of total_amount). If 0, use dp_amount as fixed value
            $table->decimal('dp_percentage', 5, 2)->default(0)->after('dp_required');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['dp_required', 'dp_percentage']);
        });
    }
};
