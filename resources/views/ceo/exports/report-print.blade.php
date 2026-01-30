<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} - {{ config('app.name') }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #1a1a2e; background: #fff; padding: 2rem; font-size: 12px; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 3px solid #6366f1; padding-bottom: 1rem; margin-bottom: 1.5rem; }
        .header h1 { font-size: 1.5rem; color: #0b1120; }
        .header .meta { text-align: right; font-size: 0.8rem; color: #666; }
        .brand { font-size: 0.9rem; font-weight: 700; color: #6366f1; letter-spacing: 0.05em; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 1.5rem; }
        thead th { background: #0b1120; color: #6366f1; padding: 0.5rem 0.75rem; text-align: left; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em; }
        tbody td { padding: 0.5rem 0.75rem; border-bottom: 1px solid #e5e7eb; font-size: 0.8rem; }
        tbody tr:nth-child(even) { background: #f9fafb; }
        .footer { margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #e5e7eb; font-size: 0.7rem; color: #999; display: flex; justify-content: space-between; }
        .print-btn { position: fixed; bottom: 2rem; right: 2rem; background: #6366f1; color: #0b1120; border: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 0.9rem; box-shadow: 0 4px 12px rgba(245,158,11,0.3); }
        .print-btn:hover { background: #d97706; }
        @media print {
            .print-btn { display: none; }
            body { padding: 0.5rem; }
        }
        .section-title { font-size: 1rem; font-weight: 600; color: #0b1120; margin: 1.5rem 0 0.75rem; padding-bottom: 0.25rem; border-bottom: 1px solid #6366f1; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <div class="brand">{{ config('app.name') }} - CEO Report</div>
            <h1>{{ $title }}</h1>
        </div>
        <div class="meta">
            <div>Period: {{ ucfirst($period) }}</div>
            <div>Generated: {{ $generatedAt }}</div>
        </div>
    </div>

    @if(isset($orders))
    <h2 class="section-title">Orders</h2>
    <table>
        <thead>
            <tr><th>Order #</th><th>Status</th><th>Total</th><th>Commission</th><th>Date</th></tr>
        </thead>
        <tbody>
            @foreach($orders as $o)
            <tr>
                <td>{{ $o->order_number }}</td>
                <td>{{ ucfirst($o->status) }}</td>
                <td>UGX {{ number_format($o->total, 2) }}</td>
                <td>UGX {{ number_format($o->platform_commission, 2) }}</td>
                <td>{{ \Carbon\Carbon::parse($o->created_at)->format('Y-m-d H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if(isset($topProducts))
    <h2 class="section-title">Top Products</h2>
    <table>
        <thead><tr><th>#</th><th>Product</th><th>Revenue</th><th>Units</th></tr></thead>
        <tbody>
            @foreach($topProducts as $i => $p)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $p->title }}</td>
                <td>UGX {{ number_format($p->revenue, 2) }}</td>
                <td>{{ number_format($p->units) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if(isset($monthly))
    <h2 class="section-title">Monthly Financials</h2>
    <table>
        <thead><tr><th>Month</th><th>Revenue</th><th>Commission</th></tr></thead>
        <tbody>
            @foreach($monthly as $m)
            <tr>
                <td>{{ $m->month }}</td>
                <td>UGX {{ number_format($m->revenue, 2) }}</td>
                <td>UGX {{ number_format($m->commission, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if(isset($vendors))
    <h2 class="section-title">Vendor Performance</h2>
    <table>
        <thead><tr><th>Vendor</th><th>Total Orders</th><th>Delivered</th><th>Cancelled</th><th>Delivery Score</th><th>Performance</th></tr></thead>
        <tbody>
            @foreach($vendors as $v)
            <tr>
                <td>{{ $v->business_name ?? 'N/A' }}</td>
                <td>{{ $v->total_orders }}</td>
                <td>{{ $v->delivered_orders }}</td>
                <td>{{ $v->cancelled_orders }}</td>
                <td>{{ number_format($v->delivery_score ?? 0, 1) }}</td>
                <td>{{ number_format($v->performance_score ?? 0, 1) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="footer">
        <span>&copy; {{ date('Y') }} {{ config('app.name') }}. Confidential.</span>
        <span>Page 1</span>
    </div>

    <button class="print-btn" onclick="window.print()"><i class="fas fa-print"></i> Print / Save as PDF</button>
</body>
</html>
