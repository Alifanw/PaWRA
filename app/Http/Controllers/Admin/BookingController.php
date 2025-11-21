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
        ]);

        DB::beginTransaction();
        try {
            $lastBooking = Booking::whereDate('created_at', today())->latest()->first();
            $sequence = $lastBooking ? (int)substr($lastBooking->booking_code, -4) + 1 : 1;
            $bookingCode = 'BKG-' . date('Ymd') . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);

            $totalAmount = collect($validated['units'])->sum(function($unit) {
                return $unit['quantity'] * $unit['unit_price'];
            });

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
                'total_amount' => $totalAmount,
                'notes' => $validated['notes'],
                'status' => 'pending',
                'created_by' => auth()->id(),
            ]);

            foreach ($validated['units'] as $unit) {
                BookingUnit::create([
                    'booking_id' => $booking->id,
                    'product_id' => $unit['product_id'],
                    'quantity' => $unit['quantity'],
                    'unit_price' => $unit['unit_price'],
                    'subtotal' => $unit['quantity'] * $unit['unit_price'],
                ]);
            }

            DB::commit();

            return redirect()->route('admin.bookings.index')
                ->with('success', 'Booking created: ' . $bookingCode);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create booking: ' . $e->getMessage());
        }
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
                'total_amount' => $booking->total_amount,
                'notes' => $booking->notes,
                'status' => $booking->status,
                'created_by_name' => $booking->creator?->name,
                'created_at' => $booking->created_at,
                'units' => $booking->bookingUnits->map(fn($unit) => [
                    'product_name' => $unit->product->name,
                    'quantity' => $unit->quantity,
                    'unit_price' => $unit->unit_price,
                    'subtotal' => $unit->subtotal,
                ]),
                'payments' => $booking->bookingPayments->map(fn($payment) => [
                    'payment_date' => $payment->payment_date,
                    'amount' => $payment->amount,
                    'payment_method' => $payment->payment_method,
                    'status' => $payment->status,
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
