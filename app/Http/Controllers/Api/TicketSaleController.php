<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TicketSale;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class TicketSaleController extends Controller
{
    /**
     * Display a listing of ticket sales
     */
    public function index(Request $request): JsonResponse
    {
        $query = TicketSale::with(['cashier', 'items.product']);

        // Filter by date
        if ($request->has('date')) {
            $query->whereDate('sale_date', $request->date);
        }

        // Filter by date range
        if ($request->has('from')) {
            $query->whereDate('sale_date', '>=', $request->from);
        }
        if ($request->has('to')) {
            $query->whereDate('sale_date', '<=', $request->to);
        }

        // Search by sale code
        if ($request->has('search')) {
            $query->where('sale_code', 'like', "%{$request->search}%");
        }

        // Filter by cashier
        if ($request->has('cashier_id')) {
            $query->where('cashier_id', $request->cashier_id);
        }

        $sales = $query->orderBy('sale_date', 'desc')
                       ->orderBy('created_at', 'desc')
                       ->paginate($request->get('per_page', 15));

        return response()->json($sales);
    }

    /**
     * Store a newly created ticket sale
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sale_date' => 'required|date',
            'total_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string|max:50',
            'customer_name' => 'nullable|string|max:100',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.subtotal' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Generate unique sale code
            $saleCode = $this->generateSaleCode();

            // Create ticket sale
            $ticketSale = TicketSale::create([
                'sale_code' => $saleCode,
                'sale_date' => $validated['sale_date'],
                'total_amount' => $validated['total_amount'],
                'payment_method' => $validated['payment_method'],
                'customer_name' => $validated['customer_name'] ?? null,
                'cashier_id' => auth()->id(),
            ]);

            // Create ticket sale items
            foreach ($validated['items'] as $item) {
                DB::table('ticket_sale_items')->insert([
                    'ticket_sale_id' => $ticketSale->id,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'] ?? 0,
                    'subtotal' => $item['subtotal'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Audit log
            $this->logAudit($request, 'ticket_sale_created', 'ticket_sales', $ticketSale->id, null, $ticketSale->toArray());

            DB::commit();

            return response()->json([
                'message' => 'Ticket sale created successfully',
                'data' => $ticketSale->load('items')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to create ticket sale',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified ticket sale
     */
    public function show(TicketSale $ticketSale): JsonResponse
    {
        $ticketSale->load(['cashier', 'items.product']);
        
        return response()->json(['data' => $ticketSale]);
    }

    /**
     * Get daily sales report
     */
    public function dailyReport(Request $request): JsonResponse
    {
        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
        ]);

        $report = DB::table('vw_ticket_sales_daily')
            ->whereBetween('sale_date', [$request->from, $request->to])
            ->orderBy('sale_date', 'desc')
            ->get();

        $summary = [
            'total_sales' => $report->sum('total_sales'),
            'total_tickets' => $report->sum('total_tickets'),
            'total_transactions' => $report->sum('total_transactions'),
            'days_count' => $report->count(),
        ];

        return response()->json([
            'summary' => $summary,
            'daily_data' => $report
        ]);
    }

    /**
     * Get sales by product report
     */
    public function productReport(Request $request): JsonResponse
    {
        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
        ]);

        $report = DB::table('ticket_sale_items as tsi')
            ->join('ticket_sales as ts', 'tsi.ticket_sale_id', '=', 'ts.id')
            ->join('products as p', 'tsi.product_id', '=', 'p.id')
            ->select(
                'p.id as product_id',
                'p.name as product_name',
                DB::raw('SUM(tsi.qty) as total_qty'),
                DB::raw('SUM(tsi.subtotal) as total_amount'),
                DB::raw('COUNT(DISTINCT ts.id) as transaction_count')
            )
            ->whereBetween('ts.sale_date', [$request->from, $request->to])
            ->groupBy('p.id', 'p.name')
            ->orderBy('total_amount', 'desc')
            ->get();

        return response()->json(['data' => $report]);
    }

    /**
     * Generate unique sale code
     */
    protected function generateSaleCode(): string
    {
        do {
            $code = 'TKT' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (TicketSale::where('sale_code', $code)->exists());

        return $code;
    }

    /**
     * Log audit trail
     */
    protected function logAudit(Request $request, string $action, string $resource, $resourceId, $before, $after): void
    {
        DB::table('audit_logs')->insert([
            'user_id' => auth()->id(),
            'action' => $action,
            'resource' => $resource,
            'resource_id' => $resourceId,
            'before_json' => $before ? json_encode($before) : null,
            'after_json' => $after ? json_encode($after) : null,
            'ip_addr' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);
    }
}
