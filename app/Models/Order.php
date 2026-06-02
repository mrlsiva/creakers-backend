<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'site_id', 'order_number', 'customer_name', 'customer_phone',
        'customer_email', 'customer_address', 'customer_city',
        'customer_district', 'customer_state', 'customer_pincode',
        'total_amount', 'status', 'notes',
    ];

    protected $casts = ['total_amount' => 'decimal:2'];

    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_PROCESSING = 'processing';
    const STATUS_DISPATCHED = 'dispatched';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';

    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_CONFIRMED => 'Confirmed',
            self::STATUS_PROCESSING => 'Processing',
            self::STATUS_DISPATCHED => 'Dispatched',
            self::STATUS_DELIVERED => 'Delivered',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    public static function statusDescriptions(): array
    {
        return [
            self::STATUS_PENDING => 'Order received and awaiting confirmation.',
            self::STATUS_CONFIRMED => 'Order has been confirmed by the admin.',
            self::STATUS_PROCESSING => 'Order is being prepared and packed.',
            self::STATUS_DISPATCHED => 'Order has been dispatched and is on its way.',
            self::STATUS_DELIVERED => 'Order has been delivered to the customer.',
            self::STATUS_CANCELLED => 'Order has been cancelled.',
        ];
    }

    public function getStatusDescriptionAttribute(): string
    {
        return self::statusDescriptions()[$this->status] ?? 'Status information unavailable.';
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
