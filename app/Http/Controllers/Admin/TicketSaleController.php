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
            'payment_method' => 'nullable|in:cash,bank_transfer,e_wallet',
            'payment_reference' => 'nullable|string',
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

            $transactionStatus = $validated['payment_method'] ? 'paid' : 'pending';

            $sale = TicketSale::create([
                'invoice_no' => $invoiceNo,
                'sale_date' => now(),
                'cashier_id' => auth()->id(),
                'total_qty' => $totalQty,
                'gross_amount' => $grossAmount,
                'discount_amount' => $discountAmount,
                'net_amount' => $netAmount,
                'status' => $transactionStatus === 'paid' ? 'paid' : 'open',
                'transaction_status' => $transactionStatus,
                'payment_method' => $validated['payment_method'] ?? 'cash',
                'payment_reference' => $validated['payment_reference'] ?? null,
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

            // If payment info provided, record payment log
            if (!empty($validated['payment_method'])) {
                \App\Models\TicketSalePayment::create([
                    'ticket_sale_id' => $sale->id,
                    'method' => $validated['payment_method'],
                    'reference' => $validated['payment_reference'] ?? null,
                    'amount' => $netAmount,
                    'status' => 'successful',
                    'created_by' => auth()->id(),
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

    public function pay(Request $request, TicketSale $ticketSale)
    {
        $validated = $request->validate([
            'payment_method' => 'required|in:cash,bank_transfer,e_wallet',
            'payment_reference' => 'nullable|string',
            'amount' => 'nullable|numeric|min:0',
            'idempotency_key' => 'nullable|string|max:255',
        ]);

        // Check if request is idempotent (duplicate)
        $idempotencyKey = $validated['idempotency_key'] ?? null;
        if ($idempotencyKey) {
            $existingPayment = \App\Models\TicketSalePayment::where('idempotency_key', $idempotencyKey)->first();
            if ($existingPayment) {
                return back()->with('warning', 'Payment already recorded with this reference');
            }
        }

        DB::beginTransaction();
        try {
            $amount = $validated['amount'] ?? $ticketSale->net_amount;

            // Validate balance (prevent overpayment)
            $balance = \App\Models\TicketSalePayment::getBalance($ticketSale->id, $ticketSale->net_amount);
            if ($amount > $balance + 0.01) { // Allow 1 cent rounding tolerance
                DB::rollBack();
                return back()->with('error', "Payment amount exceeds balance. Remaining: Rp " . number_format($balance, 2, ',', '.'));
            }

            // Record payment
            $payment = \App\Models\TicketSalePayment::create([
                'ticket_sale_id' => $ticketSale->id,
                'method' => $validated['payment_method'],
                'reference' => $validated['payment_reference'] ?? null,
                'amount' => $amount,
                'status' => 'successful',
                'created_by' => auth()->id(),
                'idempotency_key' => $idempotencyKey,
                'reconciled_at' => now(),
            ]);

            // Update sale status if fully paid
            $newBalance = \App\Models\TicketSalePayment::getBalance($ticketSale->id, $ticketSale->net_amount);
            if ($newBalance <= 0.01) { // Fully paid
                $ticketSale->update([
                    'transaction_status' => 'paid',
                    'payment_method' => $validated['payment_method'],
                    'payment_reference' => $validated['payment_reference'] ?? null,
                ]);
            }

            DB::commit();

            return back()->with('success', 'Payment recorded. Balance: Rp ' . number_format(max(0, $newBalance), 2, ',', '.'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to record payment: ' . $e->getMessage());
        }
    }

    public function cancel(Request $request, TicketSale $ticketSale)
    {
        $validated = $request->validate([
            'reason' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Prevent cancelling a partially or fully paid transaction without refund
            $totalPaid = \App\Models\TicketSalePayment::getTotalPaid($ticketSale->id);
            if ($totalPaid > 0.01) {
                DB::rollBack();
                return back()->with('error', 'Cannot cancel a paid transaction. Please refund first.');
            }

            $ticketSale->update(['transaction_status' => 'cancelled']);
            DB::commit();
            return back()->with('success', 'Transaction cancelled');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to cancel: ' . $e->getMessage());
        }
    }

    public function refund(Request $request, TicketSale $ticketSale)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'reason' => 'nullable|string',
            'reference' => 'nullable|string',
            'idempotency_key' => 'nullable|string|max:255',
        ]);

        // Check if request is idempotent (duplicate)
        $idempotencyKey = $validated['idempotency_key'] ?? null;
        if ($idempotencyKey) {
            $existingRefund = \App\Models\TicketSalePayment::where('idempotency_key', $idempotencyKey)
                ->where('status', 'refunded')
                ->first();
            if ($existingRefund) {
                return back()->with('warning', 'Refund already recorded with this reference');
            }
        }

        DB::beginTransaction();
        try {
            // Validate that there is something to refund
            $totalPaid = \App\Models\TicketSalePayment::getTotalPaid($ticketSale->id);
            $totalRefunded = \App\Models\TicketSalePayment::getTotalRefunded($ticketSale->id);
            $refundableAmount = $totalPaid - $totalRefunded;

            if ($validated['amount'] > $refundableAmount + 0.01) { // Allow rounding
                DB::rollBack();
                return back()->with('error', "Refund amount exceeds refundable balance. Available: Rp " . number_format($refundableAmount, 2, ',', '.'));
            }

            // Create refund log
            \App\Models\TicketSalePayment::create([
                'ticket_sale_id' => $ticketSale->id,
                'method' => $ticketSale->payment_method ?? 'cash',
                'reference' => $validated['reference'] ?? null,
                'amount' => $validated['amount'],
                'status' => 'refunded',
                'created_by' => auth()->id(),
                'idempotency_key' => $idempotencyKey,
                'reconciled_at' => now(),
            ]);

            // Update transaction status if it's fully refunded
            $finalBalance = \App\Models\TicketSalePayment::getBalance($ticketSale->id, $ticketSale->net_amount);
            if ($finalBalance >= $ticketSale->net_amount - 0.01) { // Fully refunded
                $ticketSale->update(['transaction_status' => 'cancelled']);
            }

            DB::commit();
            return back()->with('success', 'Refund recorded: Rp ' . number_format($validated['amount'], 2, ',', '.'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to record refund: ' . $e->getMessage());
        }
    }

    public function show(TicketSale $ticketSale)
    {
        $ticketSale->load(['cashier', 'items.product', 'payments']);

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
                'transaction_status' => $ticketSale->transaction_status ?? 'pending',
                'payment_method' => $ticketSale->payment_method ?? null,
                'payment_reference' => $ticketSale->payment_reference ?? null,
                'payments' => $ticketSale->payments->map(fn($p) => [
                    'id' => $p->id,
                    'method' => $p->method,
                    'reference' => $p->reference,
                    'amount' => $p->amount,
                    'status' => $p->status,
                    'created_at' => $p->created_at,
                ]),
                'items' => $ticketSale->items->map(fn($item) => [
                    'product_name' => $item->product->name,
                    'quantity' => $item->qty,
                    'unit_price' => $item->unit_price,
                    'subtotal' => $item->line_total,
                ]),
            ],
        ]);
    }

    public function print(TicketSale $ticketSale)
    {
        $ticketSale->load(['items.product', 'cashier']);
        $pdf = \PDF::loadView('pdf.ticket-sale-receipt', [
            'sale' => $ticketSale,
        ]);
        return $pdf->stream("struk-{$ticketSale->invoice_no}.pdf");
    }

    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:ticket_sales,id',
        ]);

        // Check which sales are paid
        $paidSales = TicketSale::whereIn('id', $validated['ids'])
            ->where('transaction_status', '!=', 'unpaid')
            ->pluck('invoice_no')
            ->toArray();

        if (!empty($paidSales)) {
            return back()->with('error', 'Cannot delete paid transactions: ' . implode(', ', $paidSales));
        }

        // Bulk delete
        DB::beginTransaction();
        try {
            foreach ($validated['ids'] as $saleId) {
                TicketSaleItem::where('sale_id', $saleId)->delete();
            }
            $deletedCount = TicketSale::whereIn('id', $validated['ids'])->delete();
            DB::commit();

            return back()->with('success', "$deletedCount ticket sale(s) deleted successfully");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete ticket sales');
        }
    }
}
