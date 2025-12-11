<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\ParkingTransaction;
use App\Models\ParkingBooking;
use App\Models\ParkingMonitoring;
use App\Events\ParkingTransactionCreated;
use App\Events\ParkingBookingCreated;

class ParkingController extends Controller
{
    public function createTransaction(Request $request)
    {
        // Middleware 'permission:parkir.create' already enforces access
        $data = $request->validate([
            'vehicle_number' => 'nullable|string',
            'vehicle_type' => 'nullable|in:roda2,roda4_6',
            'vehicle_count' => 'nullable|integer|min:1',
            'total_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        $tx = ParkingTransaction::create([
            'transaction_code' => strtoupper(Str::random(8)),
            'user_id' => optional($request->user())->id,
            'vehicle_number' => $data['vehicle_number'] ?? null,
            'vehicle_type' => $data['vehicle_type'] ?? 'roda2',
            'vehicle_count' => $data['vehicle_count'] ?? 1,
            'total_amount' => $data['total_amount'],
            'status' => 'completed',
            'notes' => $data['notes'] ?? null,
        ]);

        // Dispatch event for monitoring/notifications
        event(new ParkingTransactionCreated($tx));

        return response()->json(['status'=>'success','transaction'=>$tx],201);
    }

    public function createBooking(Request $request)
    {
        // Middleware 'permission:parkir.booking' already enforces access
        $data = $request->validate([
            'customer_name' => 'required|string|max:255',
            'parking_lot' => 'required|string|max:255',
            'start_time' => 'required|date_format:Y-m-d H:i:s',
            'end_time' => 'required|date_format:Y-m-d H:i:s|after:start_time',
            'price' => 'required|numeric|min:0'
        ]);

        $bk = ParkingBooking::create([
            'booking_code' => strtoupper(Str::random(8)),
            'user_id' => optional($request->user())->id,
            'customer_name' => $data['customer_name'],
            'parking_lot' => $data['parking_lot'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'status' => 'confirmed',
            'price' => $data['price'],
        ]);

        // Dispatch booking event
        event(new ParkingBookingCreated($bk));

        return response()->json(['status'=>'success','booking'=>$bk],201);
    }

    public function monitor(Request $request)
    {
        // Middleware 'permission:parkir.monitor' already enforces access
        $data = $request->validate([
            'action' => 'required|in:entered,exited,checked',
            'status' => 'required|in:successful,failed',
            'meta' => 'nullable|string'
        ]);

        $m = ParkingMonitoring::create([
            'user_id' => optional($request->user())->id,
            'action' => $data['action'],
            'status' => $data['status'],
            'meta' => $data['meta'] ?? null,
            'created_at' => now(),
        ]);

        return response()->json(['status'=>'ok','monitor'=>$m],201);
    }

    public function transactions(Request $request)
    {
        $query = ParkingTransaction::with('user');

        if ($request->has('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('transaction_code', 'like', "%{$s}%")
                    ->orWhere('vehicle_number', 'like', "%{$s}%");
            });
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $transactions = $query->latest('created_at')
            ->paginate(15);

        return response()->json($transactions);
    }

    public function showTransaction(ParkingTransaction $transaction)
    {
        $transaction->load('user');
        return response()->json($transaction);
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

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $bookings = $query->latest('created_at')
            ->paginate(15);

        return response()->json($bookings);
    }

    public function showBooking(ParkingBooking $booking)
    {
        $booking->load('user');
        return response()->json($booking);
    }

    public function updateBookingStatus(Request $request, ParkingBooking $booking)
    {
        $data = $request->validate([
            'status' => 'required|in:confirmed,cancelled,completed'
        ]);

        $booking->status = $data['status'];
        $booking->save();

        // Fire event to notify monitoring if needed
        event(new ParkingBookingCreated($booking));

        return response()->json(['status' => 'success', 'booking' => $booking]);
    }
}
