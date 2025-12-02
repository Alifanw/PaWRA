<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingUnit;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $query = Booking::with(['creator', 'bookingUnits.product']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('booking_code', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        $bookings = $query->latest('created_at')
            ->paginate(15)
            ->through(fn ($booking) => [
                'id' => $booking->id,
                'booking_code' => $booking->booking_code,
                'customer_name' => $booking->customer_name,
                'customer_phone' => $booking->customer_phone,
                'checkin_date' => $booking->checkin,
                'checkout_date' => $booking->checkout,
                'total_amount' => $booking->total_amount,
                'status' => $booking->status,
                'created_by_name' => $booking->creator?->name ?? '-',
                'units_count' => $booking->bookingUnits->count(),
            ]);

        return Inertia::render('Admin/Bookings/Index', [
            'bookings' => $bookings,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create()
    {
        $products = Product::where('is_active', true)
            ->with('category')
            ->get(['id', 'code', 'name', 'category_id', 'base_price']);

        return Inertia::render('Admin/Bookings/Create', [
            'products' => $products,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:200',
            'customer_email' => 'nullable|email|max:200',
            'customer_phone' => 'required|string|max:30',
            'checkin_date' => 'required|date|after_or_equal:today',
            'checkout_date' => 'required|date|after:checkin_date',
            'notes' => 'nullable|string',
            'units' => 'required|array|min:1',
            'units.*.product_id' => 'required|exists:products,id',
            'units.*.quantity' => 'required|integer|min:1',
            'units.*.unit_price' => 'required|numeric|min:0',
            'units.*.discount_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();
        try {
            $lastBooking = Booking::whereDate('created_at', today())->latest()->first();
            $sequence = $lastBooking ? (int)substr($lastBooking->booking_code, -4) + 1 : 1;
            $bookingCode = 'BKG-' . date('Ymd') . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);

            // Recalculate totals on backend (server is source of truth)
            $totalAmount = 0;
            $totalDiscount = 0;
            $roomCount = 0;

            foreach ($validated['units'] as $unit) {
                $qty = (int) ($unit['quantity'] ?? 0);
                $unitSubtotal = $qty * $unit['unit_price'];
                $discountPercentage = $unit['discount_percentage'] ?? 0;
                $discountAmount = ($unitSubtotal * $discountPercentage) / 100;

                $totalAmount += $unitSubtotal - $discountAmount;
                $totalDiscount += $discountAmount;
                $roomCount += $qty;
            }

            // Calculate night count
            $checkin = new \DateTime($validated['checkin_date']);
            $checkout = new \DateTime($validated['checkout_date']);
            $nightCount = $checkin->diff($checkout)->days;

            $booking = Booking::create([
                'booking_code' => $bookingCode,
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'checkin' => $validated['checkin_date'],
                'checkout' => $validated['checkout_date'],
                'night_count' => $nightCount,
                'room_count' => $roomCount,
                'total_amount' => $totalAmount,
                'discount_amount' => $totalDiscount,
                'payment_status' => 'unpaid',
                'notes' => $validated['notes'],
                'status' => 'pending',
                'created_by' => auth()->id(),
            ]);

            // Create booking units with discount information
            foreach ($validated['units'] as $unit) {
                $discountPercentage = $unit['discount_percentage'] ?? 0;
                $unitSubtotal = $unit['quantity'] * $unit['unit_price'];
                $discountAmount = ($unitSubtotal * $discountPercentage) / 100;

                BookingUnit::create([
                    'booking_id' => $booking->id,
                    'product_id' => $unit['product_id'],
                    'quantity' => $unit['quantity'],
                    'unit_price' => $unit['unit_price'],
                    'subtotal' => $unitSubtotal,
                    'discount_percentage' => $discountPercentage,
                    'discount_amount' => $discountAmount,
                ]);
            }

            DB::commit();

            return redirect()->route('admin.bookings.index')
                ->with('success', 'Booking created: ' . $bookingCode)
                ->with('booking_id', $booking->id);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create booking: ' . $e->getMessage());
        }
    }

    public function print(Booking $booking)
    {
        $booking->load(['bookingUnits.product']);

        $pdf = \PDF::loadView('pdf.booking-receipt', [
            'booking' => $booking,
        ]);

        return $pdf->stream("receipt-{$booking->booking_code}.pdf");
    }

    public function show(Booking $booking)
    {
        $booking->load(['creator', 'bookingUnits.product', 'bookingPayments']);

        return Inertia::render('Admin/Bookings/Show', [
            'booking' => [
                'id' => $booking->id,
                'booking_code' => $booking->booking_code,
                'customer_name' => $booking->customer_name,
                'customer_phone' => $booking->customer_phone,
                'checkin_date' => $booking->checkin,
                'checkout_date' => $booking->checkout,
                'night_count' => $booking->night_count,
                'room_count' => $booking->room_count,
                'total_amount' => $booking->total_amount,
                'payment_status' => $booking->payment_status,
                'remaining_balance' => max(0, $booking->total_amount - $booking->bookingPayments->sum('amount')),
                'notes' => $booking->notes,
                'status' => $booking->status,
                'created_by_name' => $booking->creator?->name,
                'created_at' => $booking->created_at,
                'units' => $booking->bookingUnits->map(fn($unit) => [
                    'product_name' => $unit->product->name,
                    'quantity' => $unit->quantity,
                    'unit_price' => $unit->unit_price,
                    'discount_percentage' => $unit->discount_percentage,
                    'discount_amount' => $unit->discount_amount,
                    'subtotal' => $unit->subtotal,
                    'subtotal_after_discount' => ($unit->unit_price * $unit->quantity) - ($unit->discount_amount ?? 0),
                ]),
                'payments' => $booking->bookingPayments->map(fn($payment) => [
                    'id' => $payment->id,
                    'payment_date' => $payment->paid_at,
                    'amount' => $payment->amount,
                    'payment_method' => $payment->payment_method,
                    'payment_reference' => $payment->payment_reference ?? null,
                ]),
            ],
        ]);
    }

    public function updateStatus(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,checked_in,checked_out,cancelled',
        ]);

        $booking->update(['status' => $validated['status']]);

        return back()->with('success', 'Booking status updated');
    }

    public function destroy(Booking $booking)
    {
        if ($booking->status !== 'pending') {
            return back()->with('error', 'Only pending bookings can be deleted');
        }

        DB::beginTransaction();
        try {
            $booking->bookingUnits()->delete();
            $booking->bookingPayments()->delete();
            $booking->delete();
            DB::commit();

            return redirect()->route('admin.bookings.index')
                ->with('success', 'Booking deleted');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete booking');
        }
    }
}
