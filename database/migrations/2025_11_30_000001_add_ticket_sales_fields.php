<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_sales', function (Blueprint $table) {
            if (!Schema::hasColumn('ticket_sales', 'invoice_no')) {
                $table->string('invoice_no')->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('ticket_sales', 'status')) {
                $table->enum('status', ['open', 'completed', 'cancelled'])->default('open')->after('invoice_no');
            }
            if (!Schema::hasColumn('ticket_sales', 'total_amount')) {
                $table->decimal('total_amount', 12, 2)->default(0)->after('status');
            }
            if (!Schema::hasColumn('ticket_sales', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('total_amount');
                $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            }
            $table->index(['created_by', 'status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('ticket_sales', function (Blueprint $table) {
            if (Schema::hasColumn('ticket_sales', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
            if (Schema::hasColumn('ticket_sales', 'total_amount')) {
                $table->dropColumn('total_amount');
            }
            if (Schema::hasColumn('ticket_sales', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('ticket_sales', 'invoice_no')) {
                $table->dropUnique(['invoice_no']);
                $table->dropColumn('invoice_no');
            }
        });
    }
};
