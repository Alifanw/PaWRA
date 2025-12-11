<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TicketSale;
use App\Models\TicketSaleItem;
use App\Models\Product;

class TicketSaleSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();
        
        if ($products->isEmpty()) {
            return;
        }

        // Get last invoice number
        $lastSale = TicketSale::whereDate('created_at', today())->latest()->first();
        $startSeq = $lastSale ? ((int)substr($lastSale->invoice_no, -4)) + 1 : 1;

        // Create sample ticket sales
        for ($i = 0; $i < 5; $i++) {
            $sale = TicketSale::create([
                'invoice_no' => 'INV-' . date('Ymd') . '-' . str_pad($startSeq + $i, 4, '0', STR_PAD_LEFT),
                'sale_date' => now()->subDays(rand(0, 7)),
                'cashier_id' => 1, // superadmin
                'total_qty' => 0,
                'gross_amount' => 0,
                'discount_amount' => rand(0, 5) * 1000,
                'net_amount' => 0,
                'status' => 'paid',
            ]);

            // Add 2-4 items per sale
            $itemCount = rand(2, 4);
            $totalQty = 0;
            $grossAmount = 0;

            for ($j = 0; $j < $itemCount; $j++) {
                $product = $products->random();
                $qty = rand(1, 5);
                $price = $product->base_price;
                $subtotal = $qty * $price;

                TicketSaleItem::create([
                    'ticket_sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'qty' => $qty,
                    'unit_price' => $price,
                    'discount_amount' => 0,
                    'line_total' => $subtotal,
                ]);

                $totalQty += $qty;
                $grossAmount += $subtotal;
            }

            // Update sale totals
            $sale->update([
                'total_qty' => $totalQty,
                'gross_amount' => $grossAmount,
                'net_amount' => $grossAmount - $sale->discount_amount,
            ]);
        }
    }
}
