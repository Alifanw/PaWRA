<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE OR REPLACE VIEW vw_ticket_sales_daily AS
            SELECT 
                DATE(ts.sale_date) AS sale_date,
                ts.cashier_id,
                u.full_name AS cashier_name,
                COUNT(ts.id) AS total_transactions,
                SUM(ts.total_qty) AS total_qty,
                SUM(ts.gross_amount) AS gross_amount,
                SUM(ts.discount_amount) AS discount_amount,
                SUM(ts.net_amount) AS net_amount
            FROM ticket_sales ts
            JOIN users u ON u.id = ts.cashier_id
            WHERE ts.status != 'void'
            GROUP BY DATE(ts.sale_date), ts.cashier_id, u.full_name
            ORDER BY sale_date DESC;
        ");
    }

    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS vw_ticket_sales_daily");
    }
};
