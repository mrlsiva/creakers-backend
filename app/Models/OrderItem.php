<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'product_name', 'category_name',
        'mrp', 'our_price', 'discount_type', 'discount_value', 'quantity', 'subtotal',
    ];

    protected $casts = [
        'mrp'            => 'decimal:2',
        'our_price'      => 'decimal:2',
        'discount_value' => 'decimal:2',
        'subtotal'       => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
