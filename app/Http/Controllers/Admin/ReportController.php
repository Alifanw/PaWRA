<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\TicketSale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\ParkingTransaction;
use App\Models\ParkingBooking;
use App\Models\ParkingMonitoring;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AllReportsExport;

class ReportController extends Controller
{
    public function allTransactions(Request $request)
    {
        $startDate = $request->start_date ?? now()->startOfMonth()->toDateString();
        $endDate = $request->end_date ?? now()->toDateString();
        
        // Convert to datetime for proper range filtering
        $startDateTime = $startDate . ' 00:00:00';
        $endDateTime = $endDate . ' 23:59:59';

        $transactions = [];

        // Get Bookings
        $bookings = Booking::with(['creator', 'bookingUnits.product'])
            ->whereBetween('checkin', [$startDate, $endDate])
            ->orderBy('checkin', 'desc')
            ->get()
            ->each(function($booking) use (&$transactions) {
                $transactions[] = [
                    'type' => 'booking',
                    'id' => $booking->id,
                    'code_or_invoice' => $booking->booking_code,
                    'transaction_date' => is_string($booking->checkin) ? $booking->checkin : $booking->checkin->toDateString(),
                    'name_customer' => $booking->customer_name,
                    'qty_nights' => $booking->night_count,
                    'gross_amount' => $booking->total_amount,
                    'discount_amount' => 0,
                    'net_amount' => $booking->total_amount,
                    'status' => $booking->status,
                ];
            });

        // Get Ticket Sales
        $sales = TicketSale::with(['cashier', 'items.product'])
            ->whereBetween('sale_date', [$startDateTime, $endDateTime])
            ->orderBy('sale_date', 'desc')
            ->get()
            ->each(function($sale) use (&$transactions) {
                $transactions[] = [
                    'type' => 'ticket_sale',
                    'id' => $sale->id,
                    'code_or_invoice' => $sale->invoice_no,
                    'transaction_date' => is_string($sale->sale_date) ? $sale->sale_date : $sale->sale_date->toDateString(),
                    'name_customer' => $sale->cashier?->name,
                    'qty_nights' => $sale->total_qty,
                    'gross_amount' => $sale->gross_amount,
                    'discount_amount' => $sale->discount_amount,
                    'net_amount' => $sale->net_amount,
                    'status' => 'completed',
                ];
            });

        // Get Parking Transactions
        $parkingTx = ParkingTransaction::with(['user'])
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->orderBy('created_at', 'desc')
            ->get()
            ->each(function($pt) use (&$transactions) {
                $transactions[] = [
                    'type' => 'parking_transaction',
                    'id' => $pt->id,
                    'code_or_invoice' => $pt->transaction_code,
                    'transaction_date' => $pt->created_at->toDateString(),
                    'name_customer' => $pt->user?->name ?? 'N/A',
                    'qty_nights' => $pt->vehicle_count,
                    'gross_amount' => $pt->total_amount,
                    'discount_amount' => 0,
                    'net_amount' => $pt->total_amount,
                    'status' => $pt->status,
                ];
            });

        // Sort by date descending
        usort($transactions, function($a, $b) {
            return strtotime($b['transaction_date']) - strtotime($a['transaction_date']);
        });

        // Calculate summary
        $summary = [
            'total_transactions' => count($transactions),
            'with_discount_count' => collect($transactions)->where('discount_amount', '>', 0)->count(),
            'total_discount' => collect($transactions)->sum('discount_amount'),
            'total_revenue' => collect($transactions)->sum('net_amount'),
        ];

        return Inertia::render('Admin/Reports/AllTransactions', [
            'transactions' => $transactions,
            'summary' => $summary,
            'filters' => compact('startDate', 'endDate'),
        ]);
    }

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

    public function exportAll(Request $request)
    {
        $startDate = $request->start_date ?? now()->subMonth()->toDateString();
        $endDate = $request->end_date ?? now()->toDateString();

        $bookings = Booking::with(['creator'])->whereBetween('checkin', [$startDate, $endDate])->get();
        $sales = TicketSale::with(['cashier'])->whereBetween('sale_date', [$startDate, $endDate])->get();
        $parkingTx = ParkingTransaction::with(['user'])->whereBetween('created_at', [$startDate, $endDate])->get();
        $parkingBookings = ParkingBooking::with(['user'])->whereBetween('created_at', [$startDate, $endDate])->get();
        $monitorings = ParkingMonitoring::with(['user'])->whereBetween('created_at', [$startDate, $endDate])->get();

        $filename = 'all_reports_'.$startDate.'_to_'.$endDate.'.csv';

        $response = new StreamedResponse(function () use ($bookings, $sales, $parkingTx, $parkingBookings, $monitorings) {
            $handle = fopen('php://output', 'w');

            // Header row
            fputcsv($handle, [
                'source', 'id', 'code_or_invoice', 'date', 'name', 'vehicle_number', 'vehicle_type', 'qty', 'amount', 'status', 'notes', 'extra'
            ]);

            // Bookings
            foreach ($bookings as $b) {
                fputcsv($handle, [
                    'booking',
                    $b->id,
                    $b->booking_code,
                    $b->checkin,
                    $b->customer_name,
                    '',
                    '',
                    $b->night_count ?? '',
                    $b->total_amount ?? '',
                    $b->status,
                    '',
                    json_encode(['created_by' => $b->creator?->name]),
                ]);
            }

            // Ticket sales
            foreach ($sales as $s) {
                fputcsv($handle, [
                    'ticket_sale',
                    $s->id,
                    $s->invoice_no,
                    $s->sale_date,
                    $s->cashier?->name,
                    '',
                    '',
                    $s->total_qty ?? '',
                    $s->net_amount ?? '',
                    '',
                    '',
                    json_encode(['gross_amount' => $s->gross_amount, 'discount' => $s->discount_amount]),
                ]);
            }

            // Parking transactions
            foreach ($parkingTx as $t) {
                fputcsv($handle, [
                    'parking_transaction',
                    $t->id,
                    $t->transaction_code ?? '',
                    $t->created_at ?? '',
                    $t->user?->name ?? $t->created_by_name ?? '',
                    $t->vehicle_number ?? '',
                    $t->vehicle_type ?? '',
                    $t->vehicle_count ?? '',
                    $t->total_amount ?? '',
                    $t->status ?? '',
                    $t->notes ?? '',
                    '',
                ]);
            }

            // Parking bookings
            foreach ($parkingBookings as $b) {
                fputcsv($handle, [
                    'parking_booking',
                    $b->id,
                    $b->booking_code ?? '',
                    $b->created_at ?? '',
                    $b->user?->name ?? '',
                    '',
                    $b->vehicle_type ?? '',
                    $b->vehicle_count ?? '',
                    $b->total_amount ?? '',
                    $b->status ?? '',
                    $b->notes ?? '',
                    '',
                ]);
            }

            // Monitorings
            foreach ($monitorings as $m) {
                fputcsv($handle, [
                    'parking_monitoring',
                    $m->id,
                    '',
                    $m->created_at ?? '',
                    $m->user?->name ?? '',
                    '',
                    $m->vehicle_type ?? '',
                    $m->vehicle_count ?? '',
                    $m->amount ?? '',
                    $m->status ?? '',
                    $m->notes ?? '',
                    '',
                ]);
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'"');

        return $response;
    }

    public function exportAllXlsx(Request $request)
    {
        $startDate = $request->start_date ?? now()->subMonth()->toDateString();
        $endDate = $request->end_date ?? now()->toDateString();

        $filename = 'all_reports_'.$startDate.'_to_'.$endDate.'.xlsx';

        return Excel::download(new AllReportsExport($startDate, $endDate), $filename);
    }

    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
        ]);

        // This is a report view, no direct delete action
        return back()->with('info', 'Reports cannot be deleted directly. Delete transactions instead.');
    }

    public function bulkDeleteTicketSales(Request $request)
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
            $deletedCount = TicketSale::whereIn('id', $validated['ids'])->delete();
            DB::commit();

            return back()->with('success', "$deletedCount ticket sale(s) deleted successfully");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete ticket sales');
        }
    }

    public function bulkDeleteBookings(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:bookings,id',
        ]);

        // Check which bookings are not pending
        $nonPendingBookings = Booking::whereIn('id', $validated['ids'])
            ->where('status', '!=', 'pending')
            ->pluck('booking_code')
            ->toArray();

        if (!empty($nonPendingBookings)) {
            return back()->with('error', 'Only pending bookings can be deleted: ' . implode(', ', $nonPendingBookings));
        }

        // Bulk delete
        DB::beginTransaction();
        try {
            $deletedCount = Booking::whereIn('id', $validated['ids'])->delete();
            DB::commit();

            return back()->with('success', "$deletedCount booking(s) deleted successfully");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete bookings');
        }
    }
}
