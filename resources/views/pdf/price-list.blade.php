<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $site->name }} - Price List</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
            color: #222;
            padding: 22px 26px;
        }

        /* ── Letterhead ── */
        .header { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .header td { border: none; vertical-align: middle; padding: 0; }
        .header .logo-cell { width: 64px; }
        .header .logo-cell img { height: 52px; width: auto; }
        .brand-name { font-size: 19px; font-weight: bold; color: #8b0000; text-transform: uppercase; letter-spacing: 1px; }
        .brand-tagline { font-size: 10px; color: #888; margin-top: 2px; }
        .doc-right { text-align: right; }
        .doc-title { font-size: 15px; font-weight: bold; color: #222; letter-spacing: 2px; }
        .doc-date { font-size: 10px; color: #777; margin-top: 3px; }
        .header-rule { height: 3px; background: linear-gradient(90deg, #8b0000, #ffd700); margin-bottom: 16px; }

        /* ── Category section ── */
        .category-title {
            background: #8b0000;
            color: #ffd700;
            font-size: 12px;
            font-weight: bold;
            padding: 6px 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 16px;
        }
        .category-title:first-of-type { margin-top: 0; }

        table.items { width: 100%; border-collapse: collapse; }
        table.items thead th {
            padding: 6px 8px;
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #8b0000;
            background: #fff8e1;
            border-bottom: 2px solid #ffd700;
            text-align: center;
        }
        table.items thead th.col-name { text-align: left; }
        table.items tbody td {
            padding: 6px 8px;
            font-size: 11px;
            border-bottom: 1px solid #f5e6c8;
            text-align: center;
        }
        table.items tbody td.col-name { text-align: left; font-weight: 500; }
        table.items tbody tr:nth-child(even) { background: #fffdf5; }
        .mrp-strike { text-decoration: line-through; color: #aaa; }
        .our-price { font-weight: bold; color: #8b0000; }
        .savings { color: #2e7d32; font-weight: bold; }

        /* ── Footer ── */
        .footer {
            margin-top: 26px;
            padding-top: 10px;
            border-top: 1px solid #eee;
            font-size: 9px;
            color: #888;
            text-align: center;
        }
        .footer .contact { margin-top: 4px; }
        .footer .disclaimer { margin-top: 6px; }

        /* ── Watermark (repeats on every page, tiled across the full page) ── */
        .watermark-item {
            position: fixed;
            width: 34%;
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            color: #000;
            opacity: 0.06;
            letter-spacing: 1px;
            text-transform: uppercase;
            white-space: nowrap;
            transform: rotate(-30deg);
            z-index: -1;
        }
    </style>
</head>
<body>

    @php
        $wmRows = 7;
        $wmCols = 3;
    @endphp
    @for ($r = 0; $r < $wmRows; $r++)
        @for ($c = 0; $c < $wmCols; $c++)
            <div
                class="watermark-item"
                style="top: {{ $r * (100 / $wmRows) }}%; left: {{ $c * (100 / $wmCols) }}%;"
            >{{ strtoupper($site->name) }}</div>
        @endfor
    @endfor

    <table class="header">
        <tr>
            @if($logoPath)
                <td class="logo-cell"><img src="{{ $logoPath }}" alt="{{ $site->name }}"></td>
            @endif
            <td>
                <div class="brand-name">{{ $site->name }}</div>
                <div class="brand-tagline">Fireworks &amp; Crackers</div>
            </td>
            <td class="doc-right">
                <div class="doc-title">PRICE LIST</div>
                <div class="doc-date">Generated: {{ $generatedAt }}</div>
            </td>
        </tr>
    </table>
    <div class="header-rule"></div>

    @foreach($categories as $category)
        <div class="category-title">{{ $category->name }}</div>
        <table class="items">
            <thead>
                <tr>
                    <th class="col-name">Product</th>
                    <th>Per</th>
                    <th>M.R.P</th>
                    <th>Discount</th>
                    <th>Our Price</th>
                    <th>You Save</th>
                </tr>
            </thead>
            <tbody>
                @foreach($category->products as $product)
                    @php $price = $product->prices->first(); @endphp
                    @if($price)
                        @php
                            $discLabel = $price->discount_type === 'flat'
                                ? '₹' . number_format($price->discount_value, 0)
                                : number_format($price->discount_value, 0) . '%';
                            $savings = $price->mrp - $price->our_price;
                        @endphp
                        <tr>
                            <td class="col-name">{{ $product->name }}</td>
                            <td>{{ $product->per ?: '-' }}</td>
                            <td><span class="mrp-strike">₹{{ number_format($price->mrp, 0) }}</span></td>
                            <td>{{ $discLabel }}</td>
                            <td class="our-price">₹{{ number_format($price->our_price, 0) }}</td>
                            <td class="savings">₹{{ number_format($savings, 0) }}</td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    @endforeach

    <div class="footer">
        <div>{{ $site->name }}@if($site->address) &middot; {{ $site->address }}@endif</div>
        <div class="contact">
            @if($site->phone)📞 {{ $site->phone }}@endif
            @if($site->admin_email)&nbsp;&middot;&nbsp;✉ {{ $site->admin_email }}@endif
        </div>
        <div class="disclaimer">Prices are subject to change without prior notice. This is a computer-generated price list.</div>
    </div>

</body>
</html>
