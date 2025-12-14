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
        $user = auth()->user();
        
        // Get query for products
        $query = Product::where('is_active', true)
            ->with(['category', 'productCodes']);

        // Filter by user role
        $userRoles = $user->roles()->pluck('name')->toArray();
        
        if (in_array('ticketing', $userRoles) && !in_array('admin', $userRoles) && !in_array('superadmin', $userRoles)) {
            // Ticketing role - only ticket products
            $query->whereHas('category', fn($q) => $q->where('category_type', 'ticket'));
        } elseif (in_array('booking', $userRoles) && !in_array('admin', $userRoles) && !in_array('superadmin', $userRoles)) {
            // Booking role - only villa products
            $query->whereHas('category', fn($q) => $q->where('category_type', 'villa'));
        } elseif (in_array('parking', $userRoles) && !in_array('admin', $userRoles) && !in_array('superadmin', $userRoles)) {
            // Parking role - only parking products
            $query->whereHas('category', fn($q) => $q->where('category_type', 'parking'));
        }
        // Admin and superadmin can see all products

        $products = $query->get(['id', 'code', 'name', 'category_id', 'base_price']);

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
            'dp_required' => 'boolean',
            'dp_type' => 'in:none,fixed,percentage',
            'dp_amount' => 'nullable|numeric|min:0',
            'dp_percentage' => 'nullable|numeric|min:0|max:100',
            'units' => 'required|array|min:1',
            'units.*.product_id' => 'required|exists:products,id',
            'units.*.product_availability_id' => 'nullable|exists:product_availability,id',
            'units.*.quantity' => 'required|integer|min:1',
            'units.*.unit_price' => 'required|numeric|min:0',
            'units.*.discount_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();
        try {
            $checkin = \Carbon\Carbon::createFromFormat('Y-m-d', $validated['checkin_date'])->startOfDay();
            $checkout = \Carbon\Carbon::createFromFormat('Y-m-d', $validated['checkout_date'])->startOfDay();

            // Validate availability for each unit
            foreach ($validated['units'] as $unit) {
                $product = Product::findOrFail($unit['product_id']);
                $quantity = (int)($unit['quantity'] ?? 1);

                // Check if product category is "tiket masuk" (ticket entrance - no stock check needed)
                $isTicketEntrance = $product->category && $product->category->code === 'ticket_entrance';

                // For villa products with availability_id
                if ($unit['product_availability_id'] ?? null) {
                    try {
                        $availability = \App\Models\ProductAvailability::findOrFail($unit['product_availability_id']);
                        
                        // Check if availability is available for the date range
                        if (!$availability->isAvailableForDates($checkin, $checkout)) {
                            DB::rollBack();
                            return back()->withInput()->with('error', 
                                "Availability '{$availability->unit_name}' is not available for the selected dates"
                            );
                        }

                        // Check if enough units available
                        $requiredQty = (int)($unit['quantity'] ?? 1);
                        $availableCount = $availability->getAvailableCount($checkin, $checkout);
                        if ($availableCount < $requiredQty) {
                            DB::rollBack();
                            return back()->withInput()->with('error', 
                                "Not enough '{$availability->unit_name}' available. Only $availableCount available, but $requiredQty required"
                            );
                        }
                    } catch (\Exception $e) {
                        // If booking/availability tables not ready, skip validation
                    }
                }
                // For products with product codes (ATV, games, etc.) - EXCEPT ticket entrance
                elseif (!$isTicketEntrance && $product->productCodes()->exists()) {
                    $availableCodesCount = $product->productCodes()
                        ->where('status', 'available')
                        ->whereDoesntHave('bookingUnits.booking', function ($q) use ($checkin, $checkout) {
                            $q->whereNotIn('status', ['cancelled', 'rejected'])
                                ->where(function ($subQ) use ($checkin, $checkout) {
                                    $subQ->where('checkin', '<', $checkout)
                                        ->where('checkout', '>', $checkin);
                                });
                        })
                        ->count();

                    if ($availableCodesCount < $quantity) {
                        DB::rollBack();
                        return back()->withInput()->with('error', 
                            "Produk '{$product->name}' tidak tersedia cukup. Tersedia: {$availableCodesCount}, Dibutuhkan: {$quantity}"
                        );
                    }
                }
            }

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

            // Determine DP settings
            $dpRequired = $validated['dp_required'] ?? true;
            $dpType = $validated['dp_type'] ?? 'none';
            $dpAmount = 0;
            $dpPercentage = 0;

            if ($dpRequired && $dpType !== 'none') {
                if ($dpType === 'fixed') {
                    $dpAmount = (float)($validated['dp_amount'] ?? 0);
                } elseif ($dpType === 'percentage') {
                    $dpPercentage = (float)($validated['dp_percentage'] ?? 0);
                    $dpAmount = ($totalAmount * $dpPercentage) / 100;
                }
            }

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
                'dp_required' => $dpRequired,
                'dp_amount' => $dpAmount,
                'dp_percentage' => $dpPercentage,
                'payment_status' => 'unpaid',
                'notes' => $validated['notes'],
                'status' => 'pending',
                'created_by' => auth()->id(),
            ]);

            // Create booking units with discount information and availability tracking
            foreach ($validated['units'] as $unit) {
                $discountPercentage = $unit['discount_percentage'] ?? 0;
                $unitSubtotal = $unit['quantity'] * $unit['unit_price'];
                $discountAmount = ($unitSubtotal * $discountPercentage) / 100;

                BookingUnit::create([
                    'booking_id' => $booking->id,
                    'product_id' => $unit['product_id'],
                    'product_availability_id' => $unit['product_availability_id'] ?? null,
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

        $totalPaid = $booking->bookingPayments->sum('amount');
        $effectiveDpAmount = $booking->dp_percentage > 0 
            ? ($booking->total_amount * $booking->dp_percentage) / 100 
            : $booking->dp_amount;

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
                'dp_required' => $booking->dp_required,
                'dp_amount' => $booking->dp_amount,
                'dp_percentage' => $booking->dp_percentage,
                'effective_dp_amount' => $effectiveDpAmount,
                'payment_status' => $booking->payment_status,
                'remaining_balance' => max(0, $booking->total_amount - $totalPaid),
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

    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:bookings,id',
        ]);

        DB::beginTransaction();
        try {
            // Get all bookings to delete
            $bookings = Booking::whereIn('id', $validated['ids'])->get();

            foreach ($bookings as $booking) {
                $booking->bookingUnits()->delete();
                $booking->bookingPayments()->delete();
                $booking->delete();
            }

            DB::commit();
            return response()->json([
                'message' => count($validated['ids']) . ' booking(s) deleted successfully',
                'deleted_count' => count($validated['ids'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to delete bookings'], 422);
        }
    }
}
