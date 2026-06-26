<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomersExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    public function query()
    {
        return Order::query()
            ->join('sites', 'sites.id', '=', 'orders.site_id')
            ->selectRaw('
                MIN(orders.id)               AS id,
                orders.customer_phone,
                sites.name                    AS site_name,
                MAX(orders.customer_name)    AS customer_name,
                MAX(orders.customer_email)   AS customer_email,
                MAX(orders.customer_city)    AS customer_city,
                MAX(orders.customer_pincode) AS customer_pincode,
                COUNT(*)                     AS orders_count,
                SUM(orders.total_amount)     AS total_spent,
                MAX(orders.created_at)       AS last_order_at
            ')
            ->groupBy('orders.customer_phone', 'orders.site_id', 'sites.name')
            ->orderByDesc('last_order_at');
    }

    public function headings(): array
    {
        return [
            'S/N',
            'Name',
            'Phone',
            'Site',
            'Email',
            'City',
            'Pincode',
            'Total Orders',
            'Total Spent (₹)',
            'Last Order',
        ];
    }

    public function map($row): array
    {
        static $index = 0;
        $index++;

        return [
            $index,
            $row->customer_name ?? '',
            $row->customer_phone,
            $row->site_name ?? '',
            $row->customer_email ?? '',
            $row->customer_city ?? '',
            $row->customer_pincode ?? '',
            $row->orders_count,
            number_format($row->total_spent, 2),
            $row->last_order_at ? \Carbon\Carbon::parse($row->last_order_at)->format('d M Y, h:i A') : '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
