<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Orders</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10px;
            color: #111;
            padding: 20px;
        }

        .doc-header { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .doc-header td { border: none; padding: 0; }
        .doc-title { font-size: 20px; font-weight: bold; letter-spacing: 3px; color: #222; }
        .doc-right { text-align: right; vertical-align: top; font-size: 10px; color: #555; }

        table.orders { width: 100%; border-collapse: collapse; }
        table.orders thead tr { background: #c0392b; color: #fff; }
        table.orders th {
            padding: 6px;
            font-size: 10px;
            border: 1px solid #c0392b;
            text-align: center;
        }
        table.orders td {
            padding: 5px 6px;
            border: 1px solid #e0e0e0;
            vertical-align: top;
        }
        table.orders tbody tr:nth-child(even) { background: #fef9f9; }
        .center { text-align: center; }
        .right  { text-align: right; }
        .muted  { color: #666; }

        .total-row td { border: none; padding-top: 8px; font-weight: bold; }
        .grand-total { color: #c0392b; font-size: 12px; }
    </style>
</head>
<body>

    <table class="doc-header">
        <tr>
            <td><div class="doc-title">ORDERS</div></td>
            <td class="doc-right">
                <div>Generated : {{ now()->format('d/m/Y, h:i A') }}</div>
                <div>Total orders : {{ $orders->count() }}</div>
            </td>
        </tr>
    </table>

    <table class="orders">
        <thead>
            <tr>
                <th>S/N</th>
                <th>Enquiry No</th>
                <th>Date</th>
                <th>Site</th>
                <th>Status</th>
                <th style="text-align:left;">Customer</th>
                <th>Phone</th>
                <th style="text-align:left;">Address</th>
                <th>Items</th>
                <th>Qty</th>
                <th>Amount (₹)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $i => $order)
            <tr>
                <td class="center">{{ $i + 1 }}</td>
                <td class="center">{{ $order->order_number }}</td>
                <td class="center">{{ $order->created_at?->format('d/m/Y') }}</td>
                <td class="center">{{ $order->site?->name }}</td>
                <td class="center">{{ $statuses[$order->status] ?? $order->status }}</td>
                <td>
                    {{ $order->customer_name }}
                    @if($order->customer_email)
                        <div class="muted">{{ $order->customer_email }}</div>
                    @endif
                </td>
                <td class="center">{{ $order->customer_phone }}</td>
                <td>
                    {{ implode(', ', array_filter([
                        $order->customer_address,
                        $order->customer_city,
                        $order->customer_district,
                        $order->customer_state,
                    ])) }}{{ $order->customer_pincode ? ' - ' . $order->customer_pincode : '' }}
                </td>
                <td class="center">{{ $order->items_count }}</td>
                <td class="center">{{ (int) $order->items_sum_quantity }}</td>
                <td class="right">{{ number_format($order->total_amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="10" class="right">Grand Total</td>
                <td class="right grand-total">₹{{ number_format($orders->sum('total_amount'), 2) }}</td>
            </tr>
        </tfoot>
    </table>

</body>
</html>
