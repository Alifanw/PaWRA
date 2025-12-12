<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\ParkingTransaction;
use App\Models\ParkingBooking;

class ParkingController extends Controller
{
    public function index(Request $request)
    {
        $query = ParkingTransaction::with('user');

        if ($request->has('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('transaction_code', 'like', "%{$s}%")
                    ->orWhere('vehicle_number', 'like', "%{$s}%");
            });
        }

        $transactions = $query->latest('created_at')
            ->paginate(15)
            ->through(fn ($t) => [
                'id' => $t->id,
                'transaction_code' => $t->transaction_code,
                'vehicle_number' => $t->vehicle_number,
                'vehicle_count' => $t->vehicle_count,
                'total_amount' => $t->total_amount,
                'status' => $t->status,
                'created_by_name' => $t->user?->name ?? '-',
                'created_at' => $t->created_at,
            ]);

        return Inertia::render('Admin/Parking/Index', [
            'transactions' => $transactions,
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Parking/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'vehicle_number' => 'nullable|string',
            'vehicle_type' => 'nullable|in:roda2,roda4_6',
            'vehicle_count' => 'nullable|integer|min:1',
            'total_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        $vehicleType = $data['vehicle_type'] ?? 'roda2';
        $vehicleCount = $data['vehicle_count'] ?? 1;

        // Calculate total amount from pricing if not provided
        $totalAmount = $data['total_amount'] ?? 0;
        if (!$totalAmount || $totalAmount == 0) {
            $unitPrice = \App\Models\ParkingPrice::calculateFee($vehicleType, 1, true);
            $totalAmount = $unitPrice * $vehicleCount;
        }

        $tx = ParkingTransaction::create([
            'transaction_code' => strtoupper(\Illuminate\Support\Str::random(8)),
            'user_id' => optional($request->user())->id,
            'vehicle_number' => $data['vehicle_number'] ?? null,
            'vehicle_type' => $vehicleType,
            'vehicle_count' => $vehicleCount,
            'total_amount' => $totalAmount,
            'status' => 'completed',
            'notes' => $data['notes'] ?? null,
        ]);

        event(new \App\Events\ParkingTransactionCreated($tx));

        return redirect()->route('admin.parking.transactions.show', ['transaction' => $tx->id]);
    }

    public function bookings(Request $request)
    {
        $query = ParkingBooking::with('user');

        if ($request->has('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('booking_code', 'like', "%{$s}%")
                    ->orWhere('customer_name', 'like', "%{$s}%");
            });
        }

        $bookings = $query->latest('created_at')
            ->paginate(15)
            ->through(fn ($b) => [
                'id' => $b->id,
                'booking_code' => $b->booking_code,
                'customer_name' => $b->customer_name,
                'parking_lot' => $b->parking_lot,
                'start_time' => $b->start_time,
                'end_time' => $b->end_time,
                'price' => $b->price,
                'status' => $b->status,
                'created_by_name' => $b->user?->name ?? '-',
            ]);

        return Inertia::render('Admin/Parking/Bookings', [
            'bookings' => $bookings,
            'filters' => $request->only(['search'])
        ]);
    }

    public function monitor(Request $request)
    {
        $query = \App\Models\ParkingMonitoring::with('user');

        if ($request->has('action')) {
            $query->where('action', $request->action);
        }

        $monitors = $query->latest('created_at')
            ->paginate(25)
            ->through(fn ($m) => [
                'id' => $m->id,
                'action' => $m->action,
                'status' => $m->status,
                'meta' => $m->meta,
                'created_by_name' => $m->user?->name ?? '-',
                'created_at' => $m->created_at,
            ]);

        return Inertia::render('Admin/Parking/Monitor', [
            'monitors' => $monitors,
            'filters' => $request->only(['action'])
        ]);
    }

    public function showTransaction(ParkingTransaction $transaction)
    {
        $transaction->load('user');

        $unitPrice = \App\Models\ParkingPrice::calculateFee($transaction->vehicle_type ?? 'roda2', 1, true);

        return Inertia::render('Admin/Parking/ShowTransaction', [
            'transaction' => [
                'id' => $transaction->id,
                'transaction_code' => $transaction->transaction_code,
                'vehicle_number' => $transaction->vehicle_number,
                'vehicle_count' => $transaction->vehicle_count,
                'vehicle_type' => $transaction->vehicle_type ?? 'roda2',
                'total_amount' => $transaction->total_amount,
                'unit_price' => $unitPrice,
                'notes' => $transaction->notes,
                'status' => $transaction->status,
                'created_by_name' => $transaction->user?->name ?? '-',
                'created_at' => $transaction->created_at,
            ]
        ]);
    }

    public function printTransaction(ParkingTransaction $transaction)
    {
        $transaction->load('user');
        $pdf = \PDF::loadView('pdf.parking-transaction', ['transaction' => $transaction]);
        return $pdf->stream("parking-{$transaction->transaction_code}.pdf");
    }

    public function showBooking(ParkingBooking $booking)
    {
        $booking->load('user');

        return Inertia::render('Admin/Parking/ShowBooking', [
            'booking' => [
                'id' => $booking->id,
                'booking_code' => $booking->booking_code,
                'customer_name' => $booking->customer_name,
                'parking_lot' => $booking->parking_lot,
                'start_time' => $booking->start_time,
                'end_time' => $booking->end_time,
                'price' => $booking->price,
                'status' => $booking->status,
                'created_by_name' => $booking->user?->name ?? '-',
                'created_at' => $booking->created_at,
            ]
        ]);
    }

    public function printBooking(ParkingBooking $booking)
    {
        $booking->load('user');
        $pdf = \PDF::loadView('pdf.parking-booking', ['booking' => $booking]);
        return $pdf->stream("parking-booking-{$booking->booking_code}.pdf");
    }

    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:parking_transactions,id',
        ]);

        $deletedCount = ParkingTransaction::whereIn('id', $validated['ids'])->delete();

        return back()->with('success', "$deletedCount parking transaction(s) deleted successfully");
    }
}
