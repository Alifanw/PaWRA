<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parking_transactions', function (Blueprint $table) {
            $table->enum('vehicle_type', ['roda2', 'roda4_6'])->default('roda2')->after('vehicle_number');
        });
    }

    public function down(): void
    {
        Schema::table('parking_transactions', function (Blueprint $table) {
            $table->dropColumn('vehicle_type');
        });
    }
};
