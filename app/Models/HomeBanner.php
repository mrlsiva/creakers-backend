<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeBanner extends Model
{
    protected $fillable = [
        'site_id', 'image', 'title', 'second_title', 'description',
        'top_small_description', 'buttons',
    ];

    protected $casts = ['buttons' => 'array'];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
