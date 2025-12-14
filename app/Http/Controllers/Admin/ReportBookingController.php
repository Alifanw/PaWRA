<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class ReportBookingController extends Controller
{
    /**
     * Show booking reports page
     */
    public function index(Request $request)
    {
        $this->authorize('view', Booking::class);

        $query = Booking::with(['bookingUnits.product', 'user']);

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
            $query->whereHas('bookingUnits', function ($q) use ($request) {
                $q->where('product_id', $request->product_id);
            });
        }

        $bookings = $query->orderBy('created_at', 'desc')
            ->paginate(25)
            ->through(fn ($b) => [
                'id' => $b->id,
                'booking_code' => $b->booking_code,
                'user_name' => $b->user?->name,
                'checkin' => $b->checkin,
                'checkout' => $b->checkout,
                'total_night' => $b->total_night,
                'total_amount' => (float) $b->total_amount,
                'status' => $b->status,
                'payment_status' => $b->payment_status,
                'unit_count' => $b->bookingUnits()->count(),
                'created_at' => $b->created_at,
            ]);

        $stats = [
            'total_bookings' => Booking::count(),
            'confirmed' => Booking::where('status', 'confirmed')->count(),
            'pending' => Booking::where('status', 'pending')->count(),
            'cancelled' => Booking::where('status', 'cancelled')->count(),
            'total_revenue' => (float) Booking::where('payment_status', 'paid')->sum('total_amount'),
        ];

        return Inertia::render('Admin/Reports/Bookings', [
            'bookings' => $bookings,
            'stats' => $stats,
            'filters' => $request->only(['start_date', 'end_date', 'status', 'product_id']),
        ]);
    }

    /**
     * Export bookings to CSV
     */
    public function export(Request $request)
    {
        $this->authorize('view', Booking::class);

        $query = Booking::with(['bookingUnits.product', 'user']);

        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $bookings = $query->orderBy('created_at', 'desc')->get();

        $csv = "Booking Code,User,Check-in,Check-out,Total Night,Amount,Status,Payment Status,Created At\n";
        foreach ($bookings as $b) {
            $csv .= "\"{$b->booking_code}\",\"{$b->user?->name}\",\"{$b->checkin}\",\"{$b->checkout}\",{$b->total_night}," . number_format($b->total_amount, 2) . ",{$b->status},{$b->payment_status},\"{$b->created_at}\"\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="bookings_' . now()->format('Ymd_His') . '.csv"',
        ]);
    }
}
