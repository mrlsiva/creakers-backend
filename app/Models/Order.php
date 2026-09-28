<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Mail\CustomerOrderStatusUpdate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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

    protected static function booted(): void
    {
        $clearDashboard = function () {
            Cache::forget('dashboard_stats');
            Cache::forget('widget_orders_by_status_all');
        };

        $clearSiteStatusCache = function (Order $order) {
            Cache::forget("widget_orders_by_status_{$order->site_id}");
        };

        static::created($clearDashboard);
        static::updated($clearDashboard);
        static::deleted($clearDashboard);
        static::created($clearSiteStatusCache);
        static::updated($clearSiteStatusCache);
        static::deleted($clearSiteStatusCache);

        // Notify the customer whenever the order status changes
        static::updated(function (Order $order) {
            if (!$order->wasChanged('status') || !$order->customer_email) {
                return;
            }

            try {
                $order->site->mailer()->to($order->customer_email)->send(
                    new CustomerOrderStatusUpdate($order->load('items', 'site'), $order->getOriginal('status'))
                );
            } catch (\Exception $e) {
                Log::error('Customer order status update mail failed: ' . $e->getMessage());
            }
        });
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
