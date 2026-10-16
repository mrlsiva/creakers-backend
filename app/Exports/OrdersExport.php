<?php

namespace App\Exports;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Exports orders from the admin order list.
 *
 * - summary: one row per order (customer, address, status, total)
 * - items:   one row per ordered product, with the order details repeated
 */
class OrdersExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    private int $index = 0;

    public function __construct(
        private Builder $query,
        private bool $withItems = false,
    ) {}

    public function query()
    {
        return $this->withItems
            ? $this->query->with(['site', 'items'])
            : $this->query->with('site')->withCount('items')->withSum('items', 'quantity');
    }

    public function headings(): array
    {
        $order = [
            'S/N',
            'Enquiry No',
            'Date',
            'Site',
            'Status',
            'Customer Name',
            'Phone',
            'Email',
            'Address',
            'City',
            'District',
            'State',
            'Pincode',
        ];

        if (!$this->withItems) {
            return [...$order, 'Items', 'Total Qty', 'Total Amount (₹)', 'Notes'];
        }

        return [
            ...$order,
            'Product Code',
            'Product',
            'Category',
            'MRP (₹)',
            'Discount',
            'Our Price (₹)',
            'Qty',
            'Line Total (₹)',
            'Order Total (₹)',
        ];
    }

    public function map($order): array
    {
        $this->index++;

        $base = [
            $this->index,
            $order->order_number,
            $order->created_at?->format('d M Y, h:i A') ?? '',
            $order->site?->name ?? '',
            Order::statuses()[$order->status] ?? $order->status,
            $order->customer_name,
            $order->customer_phone,
            $order->customer_email ?? '',
            $order->customer_address ?? '',
            $order->customer_city ?? '',
            $order->customer_district ?? '',
            $order->customer_state ?? '',
            $order->customer_pincode ?? '',
        ];

        if (!$this->withItems) {
            return [
                ...$base,
                $order->items_count,
                (int) $order->items_sum_quantity,
                number_format($order->total_amount, 2, '.', ''),
                $order->notes ?? '',
            ];
        }

        if ($order->items->isEmpty()) {
            return [[...$base, '', '', '', '', '', '', '', '', number_format($order->total_amount, 2, '.', '')]];
        }

        // Returning an array of rows makes Laravel Excel write one line per item
        return $order->items->map(fn($item) => [
            ...$base,
            $item->product_id,
            $item->product_name,
            $item->category_name ?? '',
            number_format($item->mrp, 2, '.', ''),
            $item->discount_type === 'flat'
                ? '₹' . number_format($item->discount_value, 0)
                : ($item->discount_value ? $item->discount_value . '%' : ''),
            number_format($item->our_price, 2, '.', ''),
            $item->quantity,
            number_format($item->subtotal, 2, '.', ''),
            number_format($order->total_amount, 2, '.', ''),
        ])->all();
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
