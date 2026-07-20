<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'category_id', 'name', 'slug', 'description', 'per',
        'image', 'image_vigo', 'image_ghilli', 'gallery', 'sort_order', 'is_active',
        'is_bestseller', 'badge_text',
    ];

    protected $casts = [
        'gallery' => 'array',
        'is_active' => 'boolean',
        'is_bestseller' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }

    public function priceForSite(int $siteId): ?ProductPrice
    {
        return $this->prices()->where('site_id', $siteId)->first();
    }

    public function imageForSite(Site $site): ?string
    {
        return match ($site->slug) {
            'vigo'   => $this->image_vigo,
            'ghilli' => $this->image_ghilli,
            default  => null,
        } ?: $this->image;
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
