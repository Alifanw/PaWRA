<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\TicketSale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function bookings(Request $request)
    {
        $startDate = $request->start_date ?? now()->startOfMonth()->toDateString();
        $endDate = $request->end_date ?? now()->toDateString();

        $bookings = Booking::with(['creator', 'bookingUnits.product'])
            ->whereBetween('checkin', [$startDate, $endDate])
            ->get()
            ->map(fn($booking) => [
                'booking_code' => $booking->booking_code,
                'customer_name' => $booking->customer_name,
                'checkin' => $booking->checkin,
                'checkout' => $booking->checkout,
                'night_count' => $booking->night_count,
                'total_amount' => $booking->total_amount,
                'status' => $booking->status,
                'created_by' => $booking->creator?->name,
            ]);

        $summary = [
            'total_bookings' => $bookings->count(),
            'confirmed' => $bookings->where('status', 'confirmed')->count(),
            'checked_in' => $bookings->where('status', 'checked_in')->count(),
            'checked_out' => $bookings->where('status', 'checked_out')->count(),
            'cancelled' => $bookings->where('status', 'cancelled')->count(),
            'total_revenue' => $bookings->whereIn('status', ['confirmed', 'checked_in', 'checked_out'])->sum('total_amount'),
        ];

        return Inertia::render('Admin/Reports/Bookings', [
            'bookings' => $bookings,
            'summary' => $summary,
            'filters' => compact('startDate', 'endDate'),
        ]);
    }

    public function ticketSales(Request $request)
    {
        $startDate = $request->start_date ?? now()->startOfMonth()->toDateString();
        $endDate = $request->end_date ?? now()->toDateString();

        $sales = TicketSale::with(['cashier', 'items.product'])
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->get()
            ->map(fn($sale) => [
                'invoice_no' => $sale->invoice_no,
                'sale_date' => $sale->sale_date,
                'cashier_name' => $sale->cashier?->name,
                'total_qty' => $sale->total_qty,
                'gross_amount' => $sale->gross_amount,
                'discount_amount' => $sale->discount_amount,
                'net_amount' => $sale->net_amount,
            ]);

        // Daily sales summary
        $dailySales = TicketSale::whereBetween('sale_date', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(sale_date) as date'),
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(total_qty) as total_qty'),
                DB::raw('SUM(net_amount) as total_revenue')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $summary = [
            'total_transactions' => $sales->count(),
            'total_qty' => $sales->sum('total_qty'),
            'total_gross' => $sales->sum('gross_amount'),
            'total_discount' => $sales->sum('discount_amount'),
            'total_revenue' => $sales->sum('net_amount'),
        ];

        return Inertia::render('Admin/Reports/TicketSales', [
            'sales' => $sales,
            'dailySales' => $dailySales,
            'summary' => $summary,
            'filters' => compact('startDate', 'endDate'),
        ]);
    }
}
