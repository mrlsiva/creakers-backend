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
        .doc-header { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        .doc-header td { padding: 0; border: none; }
        .doc-header .doc-title   { font-size: 22px; font-weight: bold; letter-spacing: 4px; color: #222; }
        .doc-header .doc-right   { text-align: right; vertical-align: top; }
        .doc-header .enquiry-no  { font-size: 11px; color: #555; }
        .doc-header .doc-date    { font-size: 12px; color: #333; margin-top: 3px; }

        /* ── From / To section ── */
        .parties { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        .party { width: 50%; vertical-align: top; text-align: left; padding-right: 16px; }
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
    <table class="doc-header">
        <tr>
            <td style="vertical-align:top;">
                <div class="doc-title">ENQUIRY</div>
            </td>
            <td class="doc-right">
                <div class="enquiry-no">Enquiry No : {{ $order->order_number }}</div>
                <div class="doc-date">Date : {{ $order->created_at->format('d/m/Y') }}</div>
            </td>
        </tr>
    </table>

    {{-- From / To --}}
    <table class="parties">
        <tr>
            <td class="party">
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
            </td>
            <td class="party" style="padding-right:0; padding-left:16px; text-align:left;">
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
            </td>
        </tr>
    </table>

    {{-- Items Table --}}
    <table>
        <thead>
            <tr>
                <th>S.No</th>
                <th>Code</th>
                <th style="text-align:left;">Product</th>
                <th>M.R.P</th>
                <th>Discount</th>
                <th>Our Price</th>
                <th>Qty</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $i => $item)
            @php
                $discLabel = $item->discount_type === 'flat'
                    ? '₹' . number_format($item->discount_value, 0)
                    : $item->discount_value . '%';
            @endphp
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item->product_id }}</td>
                <td class="product-name">{{ $item->product_name }}</td>
                <td><span class="mrp-strike">{{ number_format($item->mrp, 0) }}</span></td>
                <td>{{ $discLabel }}</td>
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
