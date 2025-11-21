<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_prices', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('product_id');
            $table->string('label', 30);
            $table->decimal('price', 12, 2);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('day_type', ['weekday', 'weekend', 'all'])->default('all');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['product_id', 'label'], 'uk_product_price');
            $table->index(['start_date', 'end_date'], 'idx_product_prices_period');

            $table->foreign('product_id')
                ->references('id')->on('products');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_prices');
    }
};
