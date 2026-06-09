<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteContent extends Model
{
    protected $fillable = [
        'site_id', 'key', 'title', 'tag', 'body', 'image', 'features',
        'button_label', 'button_url', 'button_open_in_new_tab', 'is_active',
    ];

    protected $casts = [
        'is_active'              => 'boolean',
        'features'               => 'array',
        'button_open_in_new_tab' => 'boolean',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
