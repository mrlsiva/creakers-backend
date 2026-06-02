<?php

namespace Database\Seeders;

use App\Models\OrderStatus;
use Illuminate\Database\Seeder;

class OrderStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['key' => 'pending',    'name' => 'Pending',    'color' => 'warning', 'sort_order' => 1, 'is_default' => true],
            ['key' => 'confirmed',  'name' => 'Confirmed',  'color' => 'info',    'sort_order' => 2],
            ['key' => 'processing', 'name' => 'Processing', 'color' => 'info',    'sort_order' => 3],
            ['key' => 'dispatched', 'name' => 'Dispatched', 'color' => 'primary', 'sort_order' => 4],
            ['key' => 'delivered',  'name' => 'Delivered',  'color' => 'success', 'sort_order' => 5],
            ['key' => 'cancelled',  'name' => 'Cancelled',  'color' => 'danger',  'sort_order' => 6],
        ];

        foreach ($statuses as $status) {
            OrderStatus::firstOrCreate(['key' => $status['key']], array_merge($status, ['is_active' => true]));
        }
    }
}
