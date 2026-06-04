<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteContact extends Model
{
    protected $fillable = [
        'site_id', 'address', 'phones', 'email', 'opening_time',
        'facebook_url', 'instagram_url', 'youtube_url',
        'twitter_url', 'whatsapp', 'telegram_url',
        'tiktok_url', 'linkedin_url', 'pinterest_url',
        'social_links', 'map_embed_url',
    ];

    protected $casts = ['social_links' => 'array'];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
