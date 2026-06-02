<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPrice extends Model
{
    protected $fillable = ['product_id', 'site_id', 'mrp', 'discount_type', 'discount_value', 'our_price'];

    protected $casts = [
        'mrp'            => 'decimal:2',
        'discount_value' => 'decimal:2',
        'our_price'      => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
