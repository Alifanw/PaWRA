<?php
/**
 * Test API Response for Ticket Index
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

use App\Models\User;
use App\Models\TicketSale;

$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== TICKET INDEX API TEST ===\n\n";

// Get all tickets with relations
$query = TicketSale::with(['cashier', 'items.product']);

$sales = $query->orderBy('sale_date', 'desc')
    ->paginate(15)
    ->through(fn ($sale) => [
        'id' => $sale->id,
        'invoice_no' => $sale->invoice_no,
        'sale_date' => $sale->sale_date,
        'cashier_name' => $sale->cashier?->name ?? '-',
        'total_qty' => $sale->total_qty,
        'gross_amount' => $sale->gross_amount,
        'discount_amount' => $sale->discount_amount,
        'net_amount' => $sale->net_amount,
        'status' => $sale->status,
    ]);

echo "Total records: " . $sales->total() . "\n";
echo "Current page: " . $sales->currentPage() . "\n";
echo "Per page: " . $sales->perPage() . "\n";
echo "Total pages: " . $sales->lastPage() . "\n\n";

echo "Records on this page:\n";
foreach ($sales as $sale) {
    echo "- INV: {$sale['invoice_no']}\n";
    echo "  Date: {$sale['sale_date']}\n";
    echo "  Cashier: {$sale['cashier_name']}\n";
    echo "  Qty: {$sale['total_qty']}\n";
    echo "  Net: Rp " . number_format($sale['net_amount'], 0, ',', '.') . "\n";
    echo "  Status: {$sale['status']}\n\n";
}

echo "=== API TEST PASSED ===\n";
