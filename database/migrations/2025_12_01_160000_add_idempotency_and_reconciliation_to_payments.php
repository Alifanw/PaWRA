<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('ticket_sale_payments', function (Blueprint $table) {
            // Add idempotency key to prevent duplicate payments
            if (!Schema::hasColumn('ticket_sale_payments', 'idempotency_key')) {
                $table->string('idempotency_key')->nullable()->unique()->after('id');
            }
            // Add reconciliation timestamp
            if (!Schema::hasColumn('ticket_sale_payments', 'reconciled_at')) {
                $table->timestamp('reconciled_at')->nullable()->after('status');
            }
        });
    }

    public function down()
    {
        Schema::table('ticket_sale_payments', function (Blueprint $table) {
            if (Schema::hasColumn('ticket_sale_payments', 'reconciled_at')) {
                $table->dropColumn('reconciled_at');
            }
            if (Schema::hasColumn('ticket_sale_payments', 'idempotency_key')) {
                $table->dropUnique('ticket_sale_payments_idempotency_key_unique');
                $table->dropColumn('idempotency_key');
            }
        });
    }
};
