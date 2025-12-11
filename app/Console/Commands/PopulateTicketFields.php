<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PopulateTicketFields extends Command
{
    protected $signature = 'tickets:populate-fields';
    protected $description = 'Populate unit_price, subtotal and ticket_sales.total_amount from products or ticket types for existing data.';

    public function handle(): int
    {
        $this->info('Populating ticket_sale_items unit_price and subtotal...');

        $items = DB::table('ticket_sale_items')->select('id','ticket_type_id','product_id','quantity','unit_price')->get();
        $updatedCount = 0;

        foreach ($items as $item) {
            $unitPrice = 0;
            if ($item->product_id) {
                $product = DB::table('products')->where('id', $item->product_id)->first();
                $unitPrice = $product->base_price ?? 0;
            } else if ($item->ticket_type_id) {
                $type = DB::table('ticket_types')->where('id', $item->ticket_type_id)->first();
                $unitPrice = $type->price ?? 0;
            }

            $subtotal = ($unitPrice * ($item->quantity ?? 0));

            DB::table('ticket_sale_items')->where('id', $item->id)->update([
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
            ]);

            $updatedCount++;
        }

        $this->info("Updated {$updatedCount} ticket_sale_items, computing ticket_sales totals...");

        $sales = DB::table('ticket_sales')->select('id')->get();
        foreach ($sales as $sale) {
            $sum = DB::table('ticket_sale_items')->where('ticket_sale_id', $sale->id)->sum('subtotal');
            DB::table('ticket_sales')->where('id', $sale->id)->update(['total_amount' => $sum]);
        }

        $this->info('Done.');
        return 0;
    }
}
