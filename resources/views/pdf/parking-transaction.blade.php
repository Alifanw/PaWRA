<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Parking Transaction {{ $transaction->transaction_code }}</title>
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
        <h2>Parking Transaction</h2>
        <div>{{ $transaction->transaction_code }}</div>
        <div>{{ $transaction->created_at }}</div>
    </div>

    <div class="section">
        <div class="row">
            <div>
                <strong>Vehicle</strong>
                <div>{{ $transaction->vehicle_number ?? '-' }}</div>
            </div>
            <div>
                <strong>Count</strong>
                <div>{{ $transaction->vehicle_count }}</div>
            </div>
            <div>
                <strong>Cashier</strong>
                <div>{{ $transaction->user?->name ?? '-' }}</div>
            </div>
        </div>
    </div>

    <table class="table">
        <tr>
            <th>Description</th>
            <th>Amount</th>
        </tr>
        <tr>
            <td>Parking fee</td>
            <td style="text-align:right;">Rp {{ number_format($transaction->total_amount,0,',','.') }}</td>
        </tr>
    </table>

    <div class="section">
        <strong>Notes</strong>
        <div>{{ $transaction->notes ?? '-' }}</div>
    </div>

</body>
</html>
