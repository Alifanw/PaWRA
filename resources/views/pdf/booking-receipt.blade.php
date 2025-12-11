<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #{{ $booking->booking_code }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
            background: #f5f5f5;
        }

        .container {
            max-width: 800px;
            margin: 20px auto;
            background: white;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #007bff;
        }

        .company-info h1 {
            font-size: 24px;
            color: #007bff;
            margin-bottom: 5px;
        }

        .company-info p {
            font-size: 10px;
            color: #666;
        }

        .receipt-number {
            text-align: right;
        }

        .receipt-number p {
            font-size: 10px;
            color: #666;
            margin-bottom: 3px;
        }

        .receipt-number .number {
            font-size: 18px;
            font-weight: bold;
            color: #007bff;
        }

        .section-title {
            font-weight: bold;
            font-size: 12px;
            margin-top: 15px;
            margin-bottom: 8px;
            color: #333;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .info-item {
            font-size: 11px;
        }

        .info-item label {
            font-weight: bold;
            color: #666;
            display: block;
            margin-bottom: 2px;
        }

        .info-item value {
            color: #333;
            display: block;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 10px;
        }

        table thead {
            background: #f8f9fa;
        }

        table th {
            padding: 8px;
            text-align: left;
            font-weight: bold;
            border-bottom: 2px solid #ddd;
            color: #333;
        }

        table td {
            padding: 8px;
            border-bottom: 1px solid #eee;
        }

        table tr:hover {
            background: #f9f9f9;
        }

        .text-right {
            text-align: right;
        }

        .total-section {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 2px solid #ddd;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 11px;
        }

        .total-row.grand-total {
            font-weight: bold;
            font-size: 14px;
            color: #007bff;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #eee;
            color: #999;
            font-size: 9px;
        }

        .dates {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
            font-size: 10px;
        }

        .dates-item {
            text-align: center;
        }

        .dates-item label {
            display: block;
            font-weight: bold;
            color: #666;
            margin-bottom: 3px;
        }

        .dates-item value {
            display: block;
            color: #333;
            font-size: 12px;
        }
    </style>
</head>
<body>

<div class="container">

    <!-- Header -->
    <div class="header">
        <div class="company-info">
            <h1>🏨 Walini Hot Spring</h1>
            <p>Sistem Manajemen Penginapan</p>
        </div>
        <div class="receipt-number">
            <p>Receipt #</p>
            <div class="number">{{ $booking->booking_code }}</div>
            <p style="margin-top: 8px; font-size: 9px;">{{ $booking->created_at->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    <!-- Customer Information -->
    <div class="section-title">INFORMASI TAMU</div>
    <div class="info-grid">
        <div class="info-item">
            <label>Nama Tamu:</label>
            <value>{{ $booking->customer_name }}</value>
        </div>
        <div class="info-item">
            <label>Telepon:</label>
            <value>{{ $booking->customer_phone }}</value>
        </div>
    </div>

    <!-- Booking Dates -->
    <div class="section-title">TANGGAL MENGINAP</div>
    <div class="dates">
        <div class="dates-item">
            <label>Check-In</label>
            <value>{{ \Carbon\Carbon::parse($booking->checkin)->format('d/m/Y') }}</value>
        </div>
        <div class="dates-item">
            <label>Check-Out</label>
            <value>{{ \Carbon\Carbon::parse($booking->checkout)->format('d/m/Y') }}</value>
        </div>
        <div class="dates-item">
            <label>Malam</label>
            <value>{{ $booking->night_count }} malam</value>
        </div>
    </div>

    <!-- Items Table -->
    <div class="section-title">DETAIL PEMESANAN</div>
    <table>
        <thead>
            <tr>
                <th>Produk</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Harga</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($booking->bookingUnits as $unit)
                <tr>
                    <td>{{ $unit->product->name }}</td>
                    <td class="text-right">{{ $unit->quantity }}</td>
                    <td class="text-right">Rp {{ number_format($unit->unit_price, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($unit->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals -->
    <div class="total-section">
        <div class="total-row">
            <span>Subtotal:</span>
            <span>Rp {{ number_format($booking->total_amount, 0, ',', '.') }}</span>
        </div>
        <div class="total-row grand-total">
            <span>TOTAL PEMBAYARAN:</span>
            <span>Rp {{ number_format($booking->total_amount, 0, ',', '.') }}</span>
        </div>
    </div>

    @if($booking->notes)
        <div style="margin-top: 15px; padding: 10px; background: #f0f0f0; border-left: 3px solid #007bff;">
            <strong style="font-size: 10px;">Catatan:</strong>
            <p style="font-size: 10px; margin-top: 3px;">{{ $booking->notes }}</p>
        </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>Terima kasih telah memilih Walini Hot Spring</p>
        <p>Printed at: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

</div>

</body>
</html>
