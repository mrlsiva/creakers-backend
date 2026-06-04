<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderStep extends Model
{
    protected $fillable = ['site_id', 'title', 'description', 'icon', 'sort_order', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
