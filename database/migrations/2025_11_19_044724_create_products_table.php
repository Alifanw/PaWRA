<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->smallInteger('category_id')->unsigned();
            $table->string('code', 30)->unique();
            $table->string('name', 100);
            $table->decimal('base_price', 12, 2)->default(0);
            $table->boolean('is_active')->default(1);
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('product_categories');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
