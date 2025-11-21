<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_sale_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('ticket_sale_id');
            $table->unsignedInteger('product_id');
            $table->unsignedInteger('qty');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('line_total', 12, 2);
            $table->timestamp('created_at')->useCurrent();

            $table->index('product_id');

            $table->foreign('ticket_sale_id')
                ->references('id')->on('ticket_sales')
                ->onDelete('cascade');

            $table->foreign('product_id')
                ->references('id')->on('products');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_sale_items');
    }
};
