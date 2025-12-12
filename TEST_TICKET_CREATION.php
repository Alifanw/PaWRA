<?php
/**
 * Test Script for Ticket Creation
 * This script tests the complete ticket creation flow
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

use App\Models\TicketSale;
use App\Models\TicketSaleItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== TICKET CREATION DIAGNOSTIC TEST ===\n\n";

// Test 1: Check database connection
echo "[TEST 1] Database Connection\n";
try {
    $count = DB::table('ticket_sales')->count();
    echo "✓ Database connected. Total tickets: $count\n\n";
} catch (\Exception $e) {
    echo "✗ Database error: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 2: Check products available
echo "[TEST 2] Available Products\n";
$products = Product::where('is_active', true)->limit(3)->get();
echo "✓ Active products: " . $products->count() . "\n";
foreach ($products as $p) {
    echo "  - {$p->id}: {$p->name} (Rp " . number_format($p->base_price, 0, ',', '.') . ")\n";
}
echo "\n";

if ($products->isEmpty()) {
    echo "✗ No active products found. Cannot create ticket.\n";
    exit(1);
}

// Test 3: Create a test ticket
echo "[TEST 3] Creating Test Ticket\n";
try {
    DB::beginTransaction();
    
    $invoiceNo = 'TEST-' . date('YmdHis') . '-' . mt_rand(1000, 9999);
    
    $ticket = TicketSale::create([
        'invoice_no' => $invoiceNo,
        'sale_date' => now(),
        'cashier_id' => 1, // Default admin user
        'total_qty' => 2,
        'gross_amount' => 100000,
        'discount_amount' => 0,
        'net_amount' => 100000,
        'status' => 'paid',
        'transaction_status' => 'paid',
        'payment_method' => 'cash',
        'payment_reference' => null,
    ]);
    
    echo "✓ Ticket created: $invoiceNo (ID: {$ticket->id})\n";
    
    // Test 4: Add items to ticket
    echo "[TEST 4] Adding Items to Ticket\n";
    
    foreach ($products->take(2) as $product) {
        TicketSaleItem::create([
            'ticket_sale_id' => $ticket->id,
            'product_id' => $product->id,
            'qty' => 1,
            'unit_price' => $product->base_price,
            'discount_amount' => 0,
            'line_total' => $product->base_price,
        ]);
        echo "✓ Added item: {$product->name}\n";
    }
    
    DB::commit();
    echo "\nTransaction committed successfully\n\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "✗ Error creating ticket: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

// Test 5: Verify created ticket
echo "[TEST 5] Verifying Created Ticket\n";
try {
    $createdTicket = TicketSale::with('items.product')->find($ticket->id);
    
    echo "✓ Ticket found in database\n";
    echo "  Invoice: {$createdTicket->invoice_no}\n";
    echo "  Total Qty: {$createdTicket->total_qty}\n";
    echo "  Net Amount: Rp " . number_format($createdTicket->net_amount, 0, ',', '.') . "\n";
    echo "  Items: {$createdTicket->items->count()}\n";
    
    foreach ($createdTicket->items as $item) {
        echo "    - {$item->product->name}: {$item->qty} x Rp " . number_format($item->unit_price, 0, ',', '.') . "\n";
    }
    
} catch (\Exception $e) {
    echo "✗ Error verifying ticket: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 6: Query latest tickets
echo "\n[TEST 6] Latest Tickets in Database\n";
$latestTickets = TicketSale::orderBy('created_at', 'desc')->limit(5)->get();
echo "✓ Total tickets in database: " . TicketSale::count() . "\n";
echo "Latest 5 tickets:\n";
foreach ($latestTickets as $t) {
    echo "  - {$t->invoice_no} (ID: {$t->id}) - " . $t->created_at->format('Y-m-d H:i:s') . "\n";
}

echo "\n=== ALL TESTS PASSED ===\n";
echo "✓ Ticket creation flow is working correctly\n";
echo "✓ Data is being persisted to database\n";
echo "✓ Relationships are functioning properly\n";
