<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Struk Penjualan Tiket</title>
    <style>
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; color: #222; margin: 0; padding: 10px; }
        .header { display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; }
        .company { font-size:16px; font-weight:700; }
        .meta { text-align:right; font-size:12px; }
        .meta-item { margin-bottom:4px; }
        table { width:100%; border-collapse:collapse; margin:12px 0; }
        th, td { padding:8px 6px; border:1px solid #ddd; font-size:12px; }
        th { background:#f4f4f4; text-align:left; font-weight:bold; }
        .totals-table { margin-top:16px; }
        .totals-table td { border:none; padding:6px 0; }
        .totals-table .label { text-align:right; font-weight:bold; padding-right:12px; width:70%; }
        .totals-table .value { text-align:right; font-weight:bold; padding-left:12px; }
        .grand-total { border-top:2px solid #222; border-bottom:2px solid #222; font-size:14px; }
        .footer { text-align:center; margin-top:20px; font-size:11px; color:#666; }
        .divider { border-top:1px solid #ddd; margin:12px 0; }
        .center { text-align:center; }
        .right { text-align:right; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <div class="company">AirPanas Walini</div>
            <div style="font-size:12px; color:#666;">Hot Spring Resort</div>
        </div>
        <div class="meta">
            <div class="meta-item"><strong>STRUK PENJUALAN</strong></div>
            <div class="meta-item">Invoice: {{ $sale->invoice_no }}</div>
            <div class="meta-item">Tanggal: {{ $sale->sale_date->format('d-m-Y H:i:s') }}</div>
            <div class="meta-item">Kasir: {{ $sale->cashier->name ?? '-' }}</div>
        </div>
    </div>

    <div class="divider"></div>

    <table>
        <thead>
            <tr>
                <th>Produk</th>
                <th style="text-align:center;">Qty</th>
                <th style="text-align:right;">Harga</th>
                <th style="text-align:right;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sale->items as $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td style="text-align:center;">{{ $item->qty }}</td>
                    <td style="text-align:right;">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td style="text-align:right;">Rp {{ number_format($item->line_total, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="center">Tidak ada item</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="divider"></div>

    <table class="totals-table">
        <tr>
            <td class="label">Total Item:</td>
            <td class="value">{{ $sale->total_qty }}</td>
        </tr>
        <tr>
            <td class="label">Subtotal:</td>
            <td class="value">Rp {{ number_format($sale->gross_amount, 0, ',', '.') }}</td>
        </tr>
        @if($sale->discount_amount > 0)
            <tr>
                <td class="label">Diskon:</td>
                <td class="value">-Rp {{ number_format($sale->discount_amount, 0, ',', '.') }}</td>
            </tr>
        @endif
        <tr class="grand-total">
            <td class="label">TOTAL:</td>
            <td class="value">Rp {{ number_format($sale->net_amount, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label">Metode Pembayaran:</td>
            <td class="value">{{ ucfirst(str_replace('_', ' ', $sale->payment_method ?? 'CASH')) }}</td>
        </tr>
        <tr>
            <td class="label">Status:</td>
            <td class="value">{{ strtoupper($sale->transaction_status ?? 'PENDING') }}</td>
        </tr>
    </table>

    @if($sale->payment_reference)
        <div style="margin-top:12px; font-size:11px;">
            <strong>Ref:</strong> {{ $sale->payment_reference }}
        </div>
    @endif

    <div class="divider"></div>

    <div class="footer">
        <p>Terima kasih telah berbelanja di AirPanas Walini</p>
        <p>Printed: {{ now()->format('d-m-Y H:i:s') }}</p>
    </div>
</body>
</html>
