<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParkingTransaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class ReportParkingController extends Controller
{
    /**
     * Show parking reports page
     */
    public function index(Request $request)
    {
        $this->authorize('view', ParkingTransaction::class);

        $query = ParkingTransaction::query();

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

        // Filter by vehicle type
        if ($request->has('vehicle_type') && $request->vehicle_type) {
            $query->where('vehicle_type', $request->vehicle_type);
        }

        $parkings = $query->orderBy('created_at', 'desc')
            ->paginate(25)
            ->through(fn ($p) => [
                'id' => $p->id,
                'ticket_number' => $p->ticket_number,
                'vehicle_number' => $p->vehicle_number,
                'vehicle_type' => $p->vehicle_type,
                'vehicle_count' => $p->vehicle_count,
                'checkin' => $p->checkin,
                'checkout' => $p->checkout,
                'duration' => $p->duration,
                'total_amount' => (float) $p->total_amount,
                'payment_status' => $p->payment_status,
                'created_at' => $p->created_at,
            ]);

        $stats = [
            'total_transactions' => ParkingTransaction::count(),
            'roda2_count' => ParkingTransaction::where('vehicle_type', 'roda2')->count(),
            'roda4_6_count' => ParkingTransaction::where('vehicle_type', 'roda4_6')->count(),
            'paid' => ParkingTransaction::where('payment_status', 'paid')->count(),
            'unpaid' => ParkingTransaction::where('payment_status', 'unpaid')->count(),
            'total_revenue' => (float) ParkingTransaction::where('payment_status', 'paid')->sum('total_amount'),
        ];

        return Inertia::render('Admin/Reports/Parking', [
            'parkings' => $parkings,
            'stats' => $stats,
            'filters' => $request->only(['start_date', 'end_date', 'status', 'vehicle_type']),
        ]);
    }

    /**
     * Export parkings to CSV
     */
    public function export(Request $request)
    {
        $this->authorize('view', ParkingTransaction::class);

        $query = ParkingTransaction::query();

        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $parkings = $query->orderBy('created_at', 'desc')->get();

        $csv = "Ticket Number,Vehicle Number,Vehicle Type,Count,Check-in,Check-out,Duration,Amount,Payment Status,Created At\n";
        foreach ($parkings as $p) {
            $csv .= "\"{$p->ticket_number}\",\"{$p->vehicle_number}\",{$p->vehicle_type},{$p->vehicle_count},\"{$p->checkin}\",\"{$p->checkout}\",{$p->duration}," . number_format($p->total_amount, 2) . ",{$p->payment_status},\"{$p->created_at}\"\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="parking_' . now()->format('Ymd_His') . '.csv"',
        ]);
    }
}
