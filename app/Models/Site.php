<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends Model
{
    protected $fillable = ['name', 'title', 'slug', 'admin_email', 'phone', 'address', 'logo', 'nav_icon', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function productPrices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function contents(): HasMany
    {
        return $this->hasMany(SiteContent::class);
    }

    public function contact(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(SiteContact::class);
    }

    public function homeBanner(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(HomeBanner::class);
    }

    public function festivalOffer(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(FestivalOffer::class);
    }
}
