<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite does not support "CREATE OR REPLACE VIEW". To keep the
        // migration compatible with sqlite (used by in-memory tests), handle
        // view creation differently depending on the driver.
        $driver = \Illuminate\Support\Facades\Schema::getConnection()->getDriverName();

        $viewSql = "SELECT 
                DATE(ts.sale_date) AS sale_date,
                ts.cashier_id,
                COALESCE(u.full_name, u.name) AS cashier_name,
                COUNT(ts.id) AS total_transactions,
                SUM(ts.total_qty) AS total_qty,
                SUM(ts.gross_amount) AS gross_amount,
                SUM(ts.discount_amount) AS discount_amount,
                SUM(ts.net_amount) AS net_amount
            FROM ticket_sales ts
            JOIN users u ON u.id = ts.cashier_id
            WHERE ts.status != 'void'
            GROUP BY DATE(ts.sale_date), ts.cashier_id, COALESCE(u.full_name, u.name)";

        if ($driver === 'sqlite') {
            // Skip creating this view for sqlite during tests. Creating the
            // view before later migrations that alter the `ticket_sales`
            // table causes sqlite to fail (views reference table names
            // and can block ALTER TABLE operations that rebuild the table).
            // The view is optional for tests, so we avoid creating it here.
        } else {
            DB::statement("CREATE OR REPLACE VIEW vw_ticket_sales_daily AS " . $viewSql . " ORDER BY sale_date DESC;");
        }
    }

    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS vw_ticket_sales_daily");
    }
};
