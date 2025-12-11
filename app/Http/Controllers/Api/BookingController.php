<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    /**
     * Display a listing of bookings
     */
    public function index(Request $request): JsonResponse
    {
        $query = Booking::with(['creator', 'bookingUnits.product', 'payments']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('from')) {
            $query->where('checkin', '>=', $request->from);
        }
        if ($request->has('to')) {
            $query->where('checkin', '<=', $request->to);
        }

        // Search by customer
        if ($request->has('customer')) {
            $query->where(function($q) use ($request) {
                $q->where('customer_name', 'like', "%{$request->customer}%")
                  ->orWhere('customer_phone', 'like', "%{$request->customer}%")
                  ->orWhere('booking_code', 'like', "%{$request->customer}%");
            });
        }

        // Sort by latest
        $query->orderBy('created_at', 'desc');

        // Paginate
        $bookings = $query->paginate($request->get('per_page', 15));

        return response()->json($bookings);
    }

    /**
     * Store a newly created booking
     */
    public function store(StoreBookingRequest $request): JsonResponse
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            // Generate unique booking code
            $bookingCode = $this->generateBookingCode();

            // Create booking
            $booking = Booking::create([
                'booking_code' => $bookingCode,
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'checkin' => $validated['checkin'],
                'checkout' => $validated['checkout'],
                'night_count' => $validated['night_count'],
                'room_count' => $validated['room_count'] ?? 1,
                'total_amount' => $validated['total_amount'],
                'discount_amount' => $validated['discount_amount'] ?? 0,
                'dp_amount' => $validated['dp_amount'] ?? 0,
                'notes' => $validated['notes'] ?? null,
                'status' => 'pending',
                'created_by' => auth()->id(),
            ]);

            // Create booking units
            if (isset($validated['booking_units'])) {
                foreach ($validated['booking_units'] as $unit) {
                    DB::table('booking_units')->insert([
                        'booking_id' => $booking->id,
                        'product_id' => $unit['product_id'],
                        'unit_code' => $unit['unit_code'] ?? null,
                        'qty' => $unit['qty'],
                        'rate' => $unit['rate'],
                        'discount' => $unit['discount'] ?? 0,
                        'subtotal' => $unit['subtotal'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Audit log
            $this->logAudit($request, 'booking_created', 'bookings', $booking->id, null, $booking->toArray());

            DB::commit();

            return response()->json([
                'message' => 'Booking created successfully',
                'data' => $booking->load('bookingUnits')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to create booking',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified booking
     */
    public function show(Booking $booking): JsonResponse
    {
        $booking->load(['creator', 'bookingUnits.product', 'payments']);
        
        return response()->json(['data' => $booking]);
    }

    /**
     * Update the specified booking
     */
    public function update(Request $request, Booking $booking): JsonResponse
    {
        $request->validate([
            'customer_name' => 'sometimes|string|max:100',
            'customer_phone' => 'sometimes|string|max:20',
            'notes' => 'nullable|string',
        ]);

        $before = $booking->toArray();
        $booking->update($request->only(['customer_name', 'customer_phone', 'notes']));
        $booking->updated_by = auth()->id();
        $booking->save();

        $this->logAudit($request, 'booking_updated', 'bookings', $booking->id, $before, $booking->toArray());

        return response()->json([
            'message' => 'Booking updated successfully',
            'data' => $booking
        ]);
    }

    /**
     * Update booking status
     */
    public function updateStatus(Request $request, Booking $booking): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,checked_in,checked_out,cancelled'
        ]);

        $before = $booking->status;
        $booking->status = $request->status;
        $booking->updated_by = auth()->id();
        $booking->save();

        $this->logAudit($request, 'booking_status_updated', 'bookings', $booking->id, 
            ['status' => $before], 
            ['status' => $booking->status]
        );

        return response()->json([
            'message' => 'Booking status updated successfully',
            'data' => $booking
        ]);
    }

    /**
     * Add payment to booking
     */
    public function addPayment(Request $request, Booking $booking): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string|max:50',
            'payment_reference' => 'nullable|string|max:100',
            'paid_at' => 'required|date',
        ]);

        DB::beginTransaction();
        try {
            // Create payment
            $payment = DB::table('booking_payments')->insertGetId([
                'booking_id' => $booking->id,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'payment_reference' => $request->payment_reference,
                'paid_at' => $request->paid_at,
                'recorded_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Check if fully paid
            $totalPaid = DB::table('booking_payments')
                ->where('booking_id', $booking->id)
                ->sum('amount');

            if ($totalPaid >= $booking->total_amount) {
                $booking->status = 'confirmed';
                $booking->save();
            }

            $this->logAudit($request, 'payment_added', 'booking_payments', $payment, null, [
                'booking_id' => $booking->id,
                'amount' => $request->amount
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Payment added successfully',
                'total_paid' => $totalPaid,
                'remaining' => max(0, $booking->total_amount - $totalPaid)
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to add payment',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate unique booking code
     */
    protected function generateBookingCode(): string
    {
        do {
            $code = 'BKG' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (Booking::where('booking_code', $code)->exists());

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
