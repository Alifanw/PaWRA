<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TicketSale;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class ReportTicketController extends Controller
{
    /**
     * Show ticket reports page
     */
    public function index(Request $request)
    {
        $this->authorize('view', TicketSale::class);

        $query = TicketSale::with(['ticketSaleDetails.product', 'user', 'payments']);

        // Filter by date range
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by product
        if ($request->has('product_id') && $request->product_id) {
            $query->whereHas('ticketSaleDetails', function ($q) use ($request) {
                $q->where('product_id', $request->product_id);
            });
        }

        $tickets = $query->orderBy('created_at', 'desc')
            ->paginate(25)
            ->through(fn ($t) => [
                'id' => $t->id,
                'sale_code' => $t->sale_code,
                'user_name' => $t->user?->name,
                'total_qty' => $t->ticketSaleDetails()->sum('qty'),
                'total_amount' => (float) $t->total_amount,
                'status' => $t->status,
                'payment_status' => $t->payment_status,
                'created_at' => $t->created_at,
            ]);

        $stats = [
            'total_sales' => TicketSale::count(),
            'completed' => TicketSale::where('status', 'completed')->count(),
            'pending' => TicketSale::where('status', 'pending')->count(),
            'cancelled' => TicketSale::where('status', 'cancelled')->count(),
            'total_revenue' => (float) TicketSale::where('payment_status', 'paid')->sum('total_amount'),
        ];

        return Inertia::render('Admin/Reports/Tickets', [
            'tickets' => $tickets,
            'stats' => $stats,
            'filters' => $request->only(['start_date', 'end_date', 'status', 'product_id']),
        ]);
    }

    /**
     * Export tickets to CSV
     */
    public function export(Request $request)
    {
        $this->authorize('view', TicketSale::class);

        $query = TicketSale::with(['ticketSaleDetails.product', 'user']);

        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $tickets = $query->orderBy('created_at', 'desc')->get();

        $csv = "Sale Code,User,Items,Amount,Status,Payment Status,Created At\n";
        foreach ($tickets as $t) {
            $qty = $t->ticketSaleDetails()->sum('qty');
            $csv .= "\"{$t->sale_code}\",\"{$t->user?->name}\",$qty," . number_format($t->total_amount, 2) . ",{$t->status},{$t->payment_status},\"{$t->created_at}\"\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="tickets_' . now()->format('Ymd_His') . '.csv"',
        ]);
    }
}
