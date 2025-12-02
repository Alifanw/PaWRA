<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('ticket_sales', function (Blueprint $table) {
            if (!Schema::hasColumn('ticket_sales', 'transaction_status')) {
                $table->enum('transaction_status', ['pending', 'paid', 'cancelled'])->default('pending')->after('status');
            }
            if (!Schema::hasColumn('ticket_sales', 'payment_method')) {
                $table->enum('payment_method', ['cash', 'bank_transfer', 'e_wallet'])->default('cash')->after('transaction_status');
            }
            if (!Schema::hasColumn('ticket_sales', 'payment_reference')) {
                $table->string('payment_reference')->nullable()->after('payment_method');
            }
        });

        // Create payments log table
        if (!Schema::hasTable('ticket_sale_payments')) {
            Schema::create('ticket_sale_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ticket_sale_id')->constrained('ticket_sales')->cascadeOnDelete();
                $table->enum('method', ['cash', 'bank_transfer', 'e_wallet']);
                $table->string('reference')->nullable();
                $table->decimal('amount', 15, 2);
                $table->enum('status', ['successful', 'failed', 'refunded'])->default('successful');
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('ticket_sale_payments')) {
            Schema::dropIfExists('ticket_sale_payments');
        }

        Schema::table('ticket_sales', function (Blueprint $table) {
            if (Schema::hasColumn('ticket_sales', 'payment_reference')) {
                $table->dropColumn('payment_reference');
            }
            if (Schema::hasColumn('ticket_sales', 'payment_method')) {
                $table->dropColumn('payment_method');
            }
            if (Schema::hasColumn('ticket_sales', 'transaction_status')) {
                $table->dropColumn('transaction_status');
            }
        });
    }
};
