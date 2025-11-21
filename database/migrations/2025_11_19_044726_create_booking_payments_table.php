<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('booking_id');
            $table->decimal('amount', 12, 2);
            $table->enum('payment_method', ['cash','transfer','qris','other'])->default('cash');
            $table->string('payment_reference', 50)->nullable();
            $table->dateTime('paid_at');
            $table->unsignedBigInteger('cashier_id');
            $table->string('notes', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('paid_at');
            $table->index('cashier_id');

            $table->foreign('booking_id')
                ->references('id')->on('bookings')
                ->onDelete('cascade');

            $table->foreign('cashier_id')
                ->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_payments');
    }
};
