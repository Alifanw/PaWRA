<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parking_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_code')->unique();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('vehicle_number')->nullable();
            $table->unsignedInteger('vehicle_count')->default(1);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->enum('status', ['pending','completed','cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('transaction_code');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parking_transactions');
    }
};
