<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\AdminOrderNotification;
use App\Mail\CustomerOrderConfirmation;
use App\Models\Order;
use App\Models\Product;
use App\Models\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{
    public function store(Request $request, string $siteSlug): JsonResponse
    {
        $site = Site::where('slug', $siteSlug)->where('is_active', true)->firstOrFail();

        $validated = $request->validate([
            'customer_name'    => 'required|string|max:255',
            'customer_phone'   => 'required|string|max:20',
            'customer_email'   => 'nullable|email|max:255',
            'customer_address' => 'nullable|string',
            'customer_city'    => 'nullable|string|max:100',
            'customer_district' => 'nullable|string|max:100',
            'customer_state'   => 'nullable|string|max:100',
            'customer_pincode' => 'nullable|string|max:10',
            'notes'            => 'nullable|string',
            'items'            => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $order = DB::transaction(function () use ($validated, $site) {
            $prefix = strtoupper(substr($site->name, 0, 4));
            $year   = now()->year;
            $yearPrefix = $prefix . '-' . $year . '-';

            $last = Order::where('site_id', $site->id)
                ->where('order_number', 'like', $yearPrefix . '%')
                ->lockForUpdate()
                ->orderByDesc('id')
                ->value('order_number');

            $sequence = $last ? (intval(substr($last, strlen($yearPrefix))) + 1) : 1;
            $orderNumber = $yearPrefix . str_pad($sequence, 2, '0', STR_PAD_LEFT);

            $order = Order::create([
                'site_id' => $site->id,
                'order_number' => $orderNumber,
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_email' => $validated['customer_email'] ?? null,
                'customer_address' => $validated['customer_address'],
                'customer_city' => $validated['customer_city'] ?? null,
                'customer_district' => $validated['customer_district'] ?? null,
                'customer_state' => $validated['customer_state'] ?? null,
                'customer_pincode' => $validated['customer_pincode'],
                'notes' => $validated['notes'] ?? null,
                'status' => Order::defaultStatus(),
            ]);

            $total = 0;

            foreach ($validated['items'] as $item) {
                $product = Product::with(['category', 'prices' => fn($q) => $q->where('site_id', $site->id)])
                    ->findOrFail($item['product_id']);

                $price = $product->prices->first();

                if (!$price) {
                    throw new \Exception("Product '{$product->name}' is not available for this site.");
                }

                $subtotal = $price->our_price * $item['quantity'];
                $total += $subtotal;

                $order->items()->create([
                    'product_id'     => $product->id,
                    'product_name'   => $product->name,
                    'category_name'  => $product->category->name ?? null,
                    'mrp'            => $price->mrp,
                    'our_price'      => $price->our_price,
                    'discount_type'  => $price->discount_type,
                    'discount_value' => $price->discount_value,
                    'quantity'       => $item['quantity'],
                    'subtotal'       => $subtotal,
                ]);
            }

            $order->update(['total_amount' => $total]);

            return $order->load('items', 'site');
        });

        // Send emails
        try {
            Mail::to($site->admin_email)->send(new AdminOrderNotification($order));
        } catch (\Exception $e) {
            \Log::error('Admin order notification failed: ' . $e->getMessage());
        }

        if ($order->customer_email) {
            try {
                Mail::to($order->customer_email)->send(new CustomerOrderConfirmation($order));
            } catch (\Exception $e) {
                \Log::error('Customer order confirmation failed: ' . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Order placed successfully.',
            'data' => [
                'order_number' => $order->order_number,
                'status'       => $order->status,
                'created_at'   => $order->created_at->toIso8601String(),
            ],
        ], 201);
    }

    public function track(Request $request, string $siteSlug): JsonResponse
    {
        $site = Site::where('slug', $siteSlug)->firstOrFail();

        $validated = $request->validate([
            'order_number' => 'nullable|string',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
        ]);

        if (empty($validated['order_number']) && empty($validated['customer_email']) && empty($validated['customer_phone'])) {
            return response()->json([
                'message' => 'Provide order_number, customer_email, or customer_phone to track the order.',
            ], 422);
        }

        $query = Order::with('items')->where('site_id', $site->id);

        // Order number is unique — return single order
        if (!empty($validated['order_number'])) {
            $order = $query->where('order_number', $validated['order_number'])->firstOrFail();
            return response()->json(['success' => true, 'data' => $this->formatOrder($order)]);
        }

        if (!empty($validated['customer_email'])) {
            $query->where('customer_email', $validated['customer_email']);
        }

        if (!empty($validated['customer_phone'])) {
            $query->where('customer_phone', $validated['customer_phone']);
        }

        $orders = $query->orderByDesc('created_at')->get();

        if ($orders->isEmpty()) {
            return response()->json(['message' => 'No orders found.'], 404);
        }

        return response()->json([
            'success' => true,
            'total' => $orders->count(),
            'data' => $orders->map(fn($order) => $this->formatOrder($order)),
        ]);
    }

    private function formatOrder(Order $order): array
    {
        return [
                'order_number' => $order->order_number,
                'status' => $order->status,
                'customer_name' => $order->customer_name,
                'customer_phone' => $order->customer_phone,
                'customer_email' => $order->customer_email,
                'customer_address' => $order->customer_address,
                'customer_city' => $order->customer_city,
                'customer_district' => $order->customer_district,
                'customer_state' => $order->customer_state,
                'customer_pincode' => $order->customer_pincode,
                'total_amount' => (float) $order->total_amount,
                'notes' => $order->notes,
                'created_at' => $order->created_at->toIso8601String(),
                'items' => $order->items->map(fn($item) => [
                    'product_id'   => $item->product_id,
                    'product_name' => $item->product_name,
                    'category_name' => $item->category_name,
                    'mrp'          => (float) $item->mrp,
                    'our_price'    => (float) $item->our_price,
                    'price'        => (float) $item->our_price,
                    'quantity'     => $item->quantity,
                    'subtotal'     => (float) $item->subtotal,
                ])->toArray(),
        ];
    }

    public function show(string $siteSlug, string $orderNumber): JsonResponse
    {
        $site = Site::where('slug', $siteSlug)->firstOrFail();

        $order = Order::with('items')
            ->where('site_id', $site->id)
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        return response()->json(['success' => true, 'data' => $this->formatOrder($order)]);
    }
}
