<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enquiry - {{ $order->order_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
            color: #111;
            background: #fff;
            padding: 24px;
        }

        /* ── Top header bar ── */
        .doc-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 14px;
        }
        .doc-header .enquiry-no  { font-size: 12px; color: #333; }
        .doc-header .doc-title   { font-size: 20px; font-weight: bold; letter-spacing: 4px; color: #222; }
        .doc-header .doc-date    { font-size: 12px; color: #333; }

        /* ── Divider ── */
        hr { border: none; border-top: 1.5px solid #e74c3c; margin-bottom: 14px; }

        /* ── From / To section ── */
        .parties {
            display: flex;
            justify-content: space-between;
            margin-bottom: 18px;
            gap: 20px;
        }
        .party { width: 48%; }
        .party .label {
            font-size: 10px;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }
        .party .company-name {
            font-size: 15px;
            font-weight: bold;
            color: #c0392b;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .party .detail-line { margin-bottom: 2px; font-size: 11px; color: #333; }
        .party .phone-line  { margin-bottom: 2px; font-size: 11px; }
        .party .email-line  { font-size: 11px; color: #555; }

        /* ── Items table ── */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        thead tr {
            background: #c0392b;
            color: #fff;
        }
        thead th {
            padding: 7px 8px;
            text-align: center;
            font-size: 11px;
            font-weight: bold;
            border: 1px solid #c0392b;
        }
        tbody tr:nth-child(even) { background: #fef9f9; }
        tbody tr:nth-child(odd)  { background: #fff; }
        tbody td {
            padding: 6px 8px;
            border: 1px solid #e0e0e0;
            text-align: center;
            font-size: 11px;
            vertical-align: middle;
        }
        tbody td.product-name { text-align: left; }
        .mrp-strike { text-decoration: line-through; color: #999; }

        /* ── Total row ── */
        .total-row td {
            border: none;
            padding: 8px 8px 2px;
            font-size: 12px;
        }
        .total-label {
            text-align: right;
            font-weight: bold;
            color: #555;
        }
        .total-amount {
            font-weight: bold;
            font-size: 14px;
            color: #c0392b;
            text-align: center;
            min-width: 70px;
        }

        /* ── Footer note ── */
        .footer-note {
            margin-top: 30px;
            font-size: 10px;
            color: #999;
            text-align: center;
        }
    </style>
</head>
<body>

    {{-- Header --}}
    <div class="doc-header">
        <span class="enquiry-no">Enquiry No : {{ $order->order_number }}</span>
        <span class="doc-title">ENQUIRY</span>
        <span class="doc-date">Date : {{ $order->created_at->format('d/m/Y') }}</span>
    </div>

    <hr>

    {{-- From / To --}}
    <div class="parties">
        <div class="party">
            <div class="label">From</div>
            <div class="company-name">{{ $order->site->name }}</div>
            @if($order->site->address)
                <div class="detail-line">{{ $order->site->address }}</div>
            @endif
            @if($order->site->phone)
                <div class="phone-line">📞 {{ $order->site->phone }}</div>
            @endif
            @if($order->site->admin_email)
                <div class="email-line">✉ {{ $order->site->admin_email }}</div>
            @endif
        </div>

        <div class="party">
            <div class="label">To</div>
            <div class="company-name">{{ $order->customer_name }}</div>
            @if($order->customer_address)
                <div class="detail-line">{{ $order->customer_address }}</div>
            @endif
            @if($order->customer_city || $order->customer_district)
                <div class="detail-line">
                    {{ implode(', ', array_filter([$order->customer_city, $order->customer_district])) }}
                    @if($order->customer_pincode) - {{ $order->customer_pincode }} @endif
                </div>
            @endif
            @if($order->customer_phone)
                <div class="phone-line">📞 {{ $order->customer_phone }}</div>
            @endif
        </div>
    </div>

    {{-- Items Table --}}
    <table>
        <thead>
            <tr>
                <th>S.No</th>
                <th>Code</th>
                <th style="text-align:left;">Product</th>
                <th>M.R.P</th>
                <th>Disc %</th>
                <th>Our Price</th>
                <th>Qty</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $i => $item)
            @php
                $disc = $item->mrp > 0
                    ? round((($item->mrp - $item->our_price) / $item->mrp) * 100)
                    : 0;
            @endphp
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item->product_id }}</td>
                <td class="product-name">{{ $item->product_name }}</td>
                <td><span class="mrp-strike">{{ number_format($item->mrp, 0) }}</span></td>
                <td>{{ $disc }}%</td>
                <td>{{ number_format($item->our_price, 0) }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ number_format($item->subtotal, 0) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="6"></td>
                <td class="total-label">Payable Amount</td>
                <td class="total-amount">{{ number_format($order->total_amount, 0) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer-note">
        This is a computer-generated enquiry. Thank you for your order.
    </div>

</body>
</html>
