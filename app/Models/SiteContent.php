<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteContent extends Model
{
    protected $fillable = ['site_id', 'key', 'title', 'body', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
