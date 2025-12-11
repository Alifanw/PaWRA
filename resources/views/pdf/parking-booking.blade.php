<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Parking Booking {{ $booking->booking_code }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 16px; }
        .section { margin-bottom: 8px; }
        .row { display:flex; justify-content:space-between; }
        .table { width:100%; border-collapse:collapse; margin-top:8px; }
        .table td, .table th { border:1px solid #ddd; padding:8px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Parking Booking</h2>
        <div>{{ $booking->booking_code }}</div>
        <div>{{ $booking->created_at }}</div>
    </div>

    <div class="section">
        <div><strong>Customer</strong> {{ $booking->customer_name }}</div>
        <div><strong>Lot</strong> {{ $booking->parking_lot }}</div>
        <div><strong>Period</strong> {{ $booking->start_time }} - {{ $booking->end_time }}</div>
    </div>

    <table class="table">
        <tr>
            <th>Description</th>
            <th>Price</th>
        </tr>
        <tr>
            <td>Booking fee</td>
            <td style="text-align:right;">Rp {{ number_format($booking->price,0,',','.') }}</td>
        </tr>
    </table>

</body>
</html>
