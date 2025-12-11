<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Kwitansi Pembayaran</title>
    <style>
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; color: #222; }
        .header { display:flex; justify-content:space-between; align-items:center; }
        .company { font-size:16px; font-weight:700; }
        .meta { text-align:right; }
        table { width:100%; border-collapse:collapse; margin-top:16px; }
        th, td { padding:8px 6px; border:1px solid #ddd; }
        th { background:#f4f4f4; text-align:left; }
        .totals { margin-top:12px; width:100%; }
        .totals td { padding:6px; }
        .right { text-align:right; }
        .small { font-size:12px; color:#666; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <div class="company">Walini Hot Spring</div>
            <div class="small">Jalan Contoh No.1, Kota, Indonesia</div>
            <div class="small">Phone: 0812-3456-7890</div>
        </div>
        <div class="meta">
            <div><strong>Kwitansi Pembayaran</strong></div>
            <div>No: KW-{{ $payment->id }}</div>
            <div>{{ $payment->paid_at?->format('d M Y H:i') ?? $payment->created_at->format('d M Y H:i') }}</div>
        </div>
    </div>

    <hr style="margin-top:12px" />

    <div style="margin-top:8px">
        <strong>Data Pembayaran</strong>
        <table>
            <tr>
                <th>Pelanggan</th>
                <td>{{ $payment->booking->customer_name ?? '-' }}</td>
                <th>Telepon</th>
                <td>{{ $payment->booking->customer_phone ?? '-' }}</td>
            </tr>
            <tr>
                <th>Kode Booking</th>
                <td>{{ $payment->booking->booking_code ?? '-' }}</td>
                <th>Metode Pembayaran</th>
                <td>{{ $payment->payment_method ?? '-' }}</td>
            </tr>
            <tr>
                <th>Jumlah Dibayar</th>
                <td>Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                <th>Kasir</th>
                <td>{{ $payment->cashier_id ? (App\Models\User::find($payment->cashier_id)?->name ?? $payment->cashier_id) : '-' }}</td>
            </tr>
            <tr>
                <th>Keterangan</th>
                <td colspan="3">{{ $payment->notes ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <div style="margin-top:12px">
        <strong>Rincian Booking</strong>
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Harga Satuan</th>
                    <th>Diskon</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payment->booking->bookingUnits as $unit)
                <tr>
                    <td>{{ $unit->product?->name ?? 'Item' }}</td>
                    <td>{{ $unit->quantity }}</td>
                    <td>Rp {{ number_format($unit->unit_price, 0, ',', '.') }}</td>
                    <td>{{ $unit->discount_percentage }}%</td>
                    <td class="right">Rp {{ number_format(($unit->unit_price * $unit->quantity) - ($unit->discount_amount ?? 0), 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <table class="totals">
                <tr>
                <td class="right">Subtotal:</td>
                <td class="right">Rp {{ number_format(($payment->booking->total_amount + ($payment->booking->discount_amount ?? 0)), 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="right">Total Diskon:</td>
                <td class="right">Rp {{ number_format($payment->booking->discount_amount ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="right"><strong>Total Booking:</strong></td>
                <td class="right"><strong>Rp {{ number_format($payment->booking->total_amount, 0, ',', '.') }}</strong></td>
            </tr>
            <tr>
                <td class="right">Dibayar (Kwitansi ini):</td>
                <td class="right">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="right"><strong>Sisa:</strong></td>
                <td class="right"><strong>Rp {{ number_format(max(0, ($payment->booking->total_amount - ($payment->booking->bookingPayments->sum('amount') ?? 0))), 0, ',', '.') }}</strong></td>
            </tr>
        </table>
    </div>

    <div style="margin-top:18px; text-align:center; font-size:12px; color:#666">
        Terima kasih atas pembayaran Anda.
    </div>

</body>
</html>
