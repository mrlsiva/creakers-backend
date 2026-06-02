<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>New Order — #{{ $order->order_number }}</title>
<style>
  body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; color: #333; }
  .wrapper { max-width: 640px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
  .header { background: #1a1a2e; color: #fff; padding: 24px 32px; }
  .header h1 { margin: 0; font-size: 20px; }
  .header p { margin: 4px 0 0; font-size: 13px; opacity: .75; }
  .body { padding: 28px 32px; }
  .section-title { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: #888; margin: 24px 0 10px; }
  .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px 24px; }
  .info-item label { font-size: 11px; color: #888; display: block; margin-bottom: 2px; }
  .info-item span { font-size: 14px; font-weight: 600; }
  table { width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 13px; }
  th { background: #f8f8f8; text-align: left; padding: 8px 10px; font-size: 11px; text-transform: uppercase; letter-spacing: .05em; color: #666; border-bottom: 1px solid #eee; }
  td { padding: 10px; border-bottom: 1px solid #f0f0f0; vertical-align: top; }
  .total-row td { font-weight: 700; font-size: 15px; border-top: 2px solid #eee; border-bottom: none; }
  .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; background: #fff3cd; color: #856404; }
  .footer { padding: 16px 32px; background: #f8f8f8; font-size: 11px; color: #999; text-align: center; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <h1>New Order Received</h1>
    <p>{{ $order->site->name }} &mdash; {{ $order->created_at->format('d M Y, h:i A') }}</p>
  </div>

  <div class="body">
    <div class="info-grid">
      <div class="info-item">
        <label>Order Number</label>
        <span>{{ $order->order_number }}</span>
      </div>
      <div class="info-item">
        <label>Status</label>
        <span><span class="badge">{{ ucfirst($order->status) }}</span></span>
      </div>
    </div>

    <div class="section-title">Customer Details</div>
    <div class="info-grid">
      <div class="info-item">
        <label>Name</label>
        <span>{{ $order->customer_name }}</span>
      </div>
      <div class="info-item">
        <label>Phone</label>
        <span>{{ $order->customer_phone }}</span>
      </div>
      @if($order->customer_email)
      <div class="info-item">
        <label>Email</label>
        <span>{{ $order->customer_email }}</span>
      </div>
      @endif
      <div class="info-item">
        <label>Pincode</label>
        <span>{{ $order->customer_pincode }}</span>
      </div>
      <div class="info-item" style="grid-column: 1 / -1;">
        <label>Address</label>
        <span>{{ $order->customer_address }}@if($order->customer_city), {{ $order->customer_city }}@endif@if($order->customer_district), {{ $order->customer_district }}@endif@if($order->customer_state), {{ $order->customer_state }}@endif</span>
      </div>
    </div>

    @if($order->notes)
    <div class="section-title">Notes</div>
    <p style="margin:0; font-size:14px;">{{ $order->notes }}</p>
    @endif

    <div class="section-title">Order Items</div>
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
          <td colspan="3" style="text-align:right">Total Amount</td>
          <td style="text-align:right">₹{{ number_format($order->total_amount, 2) }}</td>
        </tr>
      </tbody>
    </table>
  </div>

  <div class="footer">This is an automated notification from {{ $order->site->name }}.</div>
</div>
</body>
</html>
