<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TicketSale;
use App\Models\TicketSaleItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TicketSaleController extends Controller
{
    public function index(Request $request)
    {
        $query = TicketSale::with(['cashier', 'items.product']);

        if ($request->has('date')) {
            $query->whereDate('sale_date', $request->date);
        }

        if ($request->has('search')) {
            $query->where('invoice_no', 'like', "%{$request->search}%");
        }

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

        return Inertia::render('Admin/TicketSales/Index', [
            'ticketSales' => $sales,
            'filters' => $request->only(['search', 'date']),
        ]);
    }

    public function create()
    {
        $products = Product::where('is_active', true)
            ->with('category')
            ->get(['id', 'code', 'name', 'category_id', 'base_price']);

        return Inertia::render('Admin/TicketSales/Create', [
            'products' => $products,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $lastSale = TicketSale::whereDate('created_at', today())->latest()->first();
            $sequence = $lastSale ? (int)substr($lastSale->invoice_no, -4) + 1 : 1;
            $invoiceNo = 'INV-' . date('Ymd') . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);

            $totalQty = collect($validated['items'])->sum('quantity');
            $grossAmount = collect($validated['items'])->sum(function($item) {
                return $item['quantity'] * $item['unit_price'];
            });
            $discountAmount = $validated['discount_amount'] ?? 0;
            $netAmount = $grossAmount - $discountAmount;

            $sale = TicketSale::create([
                'invoice_no' => $invoiceNo,
                'sale_date' => now(),
                'cashier_id' => auth()->id(),
                'total_qty' => $totalQty,
                'gross_amount' => $grossAmount,
                'discount_amount' => $discountAmount,
                'net_amount' => $netAmount,
                'status' => 'paid',
            ]);

            foreach ($validated['items'] as $item) {
                TicketSaleItem::create([
                    'ticket_sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'qty' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_amount' => 0,
                    'line_total' => $item['quantity'] * $item['unit_price'],
                ]);
            }

            DB::commit();

            return redirect()->route('admin.ticket-sales.index')
                ->with('success', 'Ticket sale created: ' . $invoiceNo);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create ticket sale: ' . $e->getMessage());
        }
    }

    public function show(TicketSale $ticketSale)
    {
        $ticketSale->load(['cashier', 'items.product']);

        return Inertia::render('Admin/TicketSales/Show', [
            'sale' => [
                'id' => $ticketSale->id,
                'invoice_no' => $ticketSale->invoice_no,
                'sale_date' => $ticketSale->sale_date,
                'cashier_name' => $ticketSale->cashier?->name,
                'total_qty' => $ticketSale->total_qty,
                'gross_amount' => $ticketSale->gross_amount,
                'discount_amount' => $ticketSale->discount_amount,
                'net_amount' => $ticketSale->net_amount,
                'status' => $ticketSale->status,
                'items' => $ticketSale->items->map(fn($item) => [
                    'product_name' => $item->product->name,
                    'quantity' => $item->qty,
                    'unit_price' => $item->unit_price,
                    'subtotal' => $item->line_total,
                ]),
            ],
        ]);
    }
}
