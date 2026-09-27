<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order #{{ $order->order_number }} — {{ $statusName }}</title>
<style>
  body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; color: #333; }
  .wrapper { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
  .header { background: #1d4ed8; color: #fff; padding: 28px 32px; text-align: center; }
  .header h1 { margin: 0 0 6px; font-size: 22px; }
  .header p { margin: 0; opacity: .85; font-size: 14px; }
  .status { text-align: center; padding: 20px 32px 0; }
  .status .badge { display: inline-block; background: #eff6ff; border: 2px solid #93c5fd; color: #1d4ed8; font-size: 18px; font-weight: 700; padding: 8px 24px; border-radius: 6px; letter-spacing: .05em; }
  .body { padding: 24px 32px; }
  .section-title { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: #888; margin: 20px 0 10px; }
  table { width: 100%; border-collapse: collapse; font-size: 13px; }
  th { background: #f8f8f8; text-align: left; padding: 8px 10px; font-size: 11px; text-transform: uppercase; letter-spacing: .05em; color: #666; border-bottom: 1px solid #eee; }
  td { padding: 10px; border-bottom: 1px solid #f0f0f0; vertical-align: top; }
  .total-row td { font-weight: 700; font-size: 15px; border-top: 2px solid #eee; border-bottom: none; color: #1d4ed8; }
  .address-box { background: #f8f8f8; border-radius: 6px; padding: 12px 16px; font-size: 14px; line-height: 1.6; }
  .note-box { background: #fffbeb; border-left: 3px solid #fbbf24; padding: 10px 14px; border-radius: 0 4px 4px 0; font-size: 14px; }
  .footer { padding: 20px 32px; background: #f8f8f8; font-size: 12px; color: #999; text-align: center; line-height: 1.6; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <h1>Hi {{ $order->customer_name }},</h1>
    <p>There's an update on your order #{{ $order->order_number }}.</p>
  </div>

  <div class="status">
    <p style="margin: 16px 0 6px; font-size: 13px; color: #666;">Current status</p>
    <span class="badge">{{ $statusName }}</span>
    @if($previousStatusName)
    <p style="margin: 8px 0 0; font-size: 12px; color: #999;">Previously: {{ $previousStatusName }}</p>
    @endif
  </div>

  <div class="body">
    <div class="section-title">Order Details</div>
    <div class="address-box">
      <strong>Order No:</strong> {{ $order->order_number }}<br>
      <strong>Order Date:</strong> {{ $order->created_at->format('d/m/Y h:i A') }}<br>
      <strong>Phone:</strong> {{ $order->customer_phone }}
    </div>

    <div class="section-title">Delivery Address</div>
    <div class="address-box">
      {{ $order->customer_address }}@if($order->customer_city), {{ $order->customer_city }}@endif@if($order->customer_district), {{ $order->customer_district }}@endif@if($order->customer_state), {{ $order->customer_state }}@endif — {{ $order->customer_pincode }}
    </div>

    @if($order->notes)
    <div class="section-title">Notes</div>
    <div class="note-box">{{ $order->notes }}</div>
    @endif

    <div class="section-title">Items Ordered</div>
    <table>
      <thead>
        <tr>
          <th>Product</th>
          <th style="text-align:center">Qty</th>
          <th style="text-align:right">Price</th>
          <th style="text-align:right">Subtotal</th>
        </tr>
      </thead>
      <tbody>
        @foreach($order->items as $item)
        <tr>
          <td>
            <strong>{{ $item->product_name }}</strong>
            @if($item->category_name)<br><small style="color:#888">{{ $item->category_name }}</small>@endif
          </td>
          <td style="text-align:center">{{ $item->quantity }}</td>
          <td style="text-align:right">₹{{ number_format($item->our_price, 2) }}</td>
          <td style="text-align:right">₹{{ number_format($item->subtotal, 2) }}</td>
        </tr>
        @endforeach
        <tr class="total-row">
          <td colspan="3" style="text-align:right">Total</td>
          <td style="text-align:right">₹{{ number_format($order->total_amount, 2) }}</td>
        </tr>
      </tbody>
    </table>
  </div>

  <div class="footer">
    Questions? Contact us at <strong>{{ $order->site->admin_email }}</strong><br>
    {{ $order->site->name }}
  </div>
</div>
</body>
</html>
