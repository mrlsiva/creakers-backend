<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Order extends Model
{
    protected $fillable = [
        'site_id', 'order_number', 'customer_name', 'customer_phone',
        'customer_email', 'customer_address', 'customer_city',
        'customer_district', 'customer_state', 'customer_pincode',
        'total_amount', 'status', 'notes',
    ];

    protected $casts = ['total_amount' => 'decimal:2'];

    public static function statuses(): array
    {
        return Cache::remember('order_statuses', 60, fn() =>
            OrderStatus::where('is_active', true)
                ->orderBy('sort_order')
                ->pluck('name', 'key')
                ->toArray()
        );
    }

    public static function statusColor(string $key): string
    {
        $colors = Cache::remember('order_status_colors', 60, fn() =>
            OrderStatus::where('is_active', true)
                ->pluck('color', 'key')
                ->toArray()
        );

        return $colors[$key] ?? 'gray';
    }

    public static function defaultStatus(): string
    {
        return Cache::remember('order_default_status', 60, fn() =>
            OrderStatus::where('is_default', true)->value('key') ?? 'pending'
        );
    }

    public static function clearStatusCache(): void
    {
        Cache::forget('order_statuses');
        Cache::forget('order_status_colors');
        Cache::forget('order_default_status');
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
