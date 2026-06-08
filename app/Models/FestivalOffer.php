<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FestivalOffer extends Model
{
    protected $fillable = [
        'site_id', 'is_active', 'title', 'sub_title', 'ends_at',
        'button_label', 'button_url', 'button_open_in_new_tab',
    ];

    protected $casts = [
        'is_active'              => 'boolean',
        'ends_at'                => 'datetime',
        'button_open_in_new_tab' => 'boolean',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
