<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderStatus extends Model
{
    protected $fillable = ['name', 'key', 'color', 'sort_order', 'is_active', 'is_default'];

    protected $casts = [
        'is_active'  => 'boolean',
        'is_default' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (OrderStatus $status) {
            if ($status->is_default) {
                static::where('id', '!=', $status->id)->update(['is_default' => false]);
            }
        });

        static::saved(fn() => Order::clearStatusCache());
        static::deleted(fn() => Order::clearStatusCache());
    }
}
