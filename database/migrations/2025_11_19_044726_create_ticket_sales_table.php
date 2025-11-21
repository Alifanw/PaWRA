<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_sales', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('invoice_no', 30)->unique();
            $table->dateTime('sale_date')->useCurrent();
            $table->unsignedBigInteger('cashier_id');
            $table->unsignedInteger('total_qty')->default(0);
            $table->decimal('gross_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('net_amount', 12, 2)->default(0);
            $table->enum('status', ['open','paid','void'])->default('paid');
            $table->timestamps();

            $table->index('sale_date');
            $table->index('cashier_id');

            $table->foreign('cashier_id')
                ->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_sales');
    }
};
