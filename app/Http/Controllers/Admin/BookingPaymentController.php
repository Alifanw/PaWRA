<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingPayment;
use App\Models\Booking;
use Illuminate\Http\Request;

class BookingPaymentController extends Controller
{
    /**
     * Stream a payment receipt PDF for a given payment.
     */
    public function print(BookingPayment $payment)
    {
        $payment->load(['booking.bookingUnits.product', 'booking.creator']);

        $pdf = \PDF::loadView('pdf.payment-receipt', [
            'payment' => $payment,
        ]);

        return $pdf->stream("kwitansi-{$payment->id}.pdf");
    }

    /**
     * Store a new payment for a booking.
     */
    public function store(Request $request, $booking)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $payment = BookingPayment::create([
            'booking_id' => $booking,
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'payment_reference' => $request->input('payment_reference'),
            'paid_at' => now(),
            'cashier_id' => auth()->id(),
            'notes' => $validated['notes'] ?? null,
        ]);

        // Update booking payment aggregates and status
        $bookingModel = Booking::find($booking);
        if ($bookingModel) {
            // Sum all recorded payments (paid_at indicates completed payments in schema)
            $totalPaid = (float) $bookingModel->bookingPayments()->sum('amount');

            // Determine payment_status
            if ($totalPaid <= 0) {
                $paymentStatus = 'unpaid';
            } elseif ($totalPaid >= (float) $bookingModel->total_amount) {
                $paymentStatus = 'paid';
            } else {
                $paymentStatus = 'partial';
            }

            $bookingModel->update(['payment_status' => $paymentStatus]);

            // Optionally update booking operational status when fully paid
            if ($totalPaid >= (float) $bookingModel->total_amount) {
                // move to confirmed if it was pending/draft
                if (in_array($bookingModel->status, ['draft', 'pending'])) {
                    $bookingModel->update(['status' => 'confirmed']);
                }
            } else {
                // keep it pending if not fully paid
                if (in_array($bookingModel->status, ['draft'])) {
                    $bookingModel->update(['status' => 'pending']);
                }
            }
        }

        if ($request->wantsJson()) {
            // Render receipt HTML so JS popup can display and print it immediately
            $payment->load(['booking.bookingUnits.product', 'booking.creator']);
            $receiptHtml = view('pdf.payment-receipt', ['payment' => $payment])->render();

            return response()->json([
                'payment_id' => $payment->id,
                'receipt_html' => $receiptHtml,
            ], 201);
        }

        return redirect()->route('admin.bookings.show', $booking)
            ->with('success', 'Payment recorded')
            ->with('payment_id', $payment->id);
    }
}
