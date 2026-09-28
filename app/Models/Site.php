<?php

namespace App\Models;

use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Mail;

class Site extends Model
{
    protected $fillable = [
        'name', 'title', 'slug', 'admin_email', 'phone', 'address', 'logo', 'nav_icon', 'is_active',
        'mail_host', 'mail_port', 'mail_username', 'mail_password', 'mail_encryption',
        'mail_from_address', 'mail_from_name',
    ];

    protected $hidden = ['mail_password'];

    protected $casts = [
        'is_active' => 'boolean',
        'mail_port' => 'integer',
        'mail_password' => 'encrypted',
    ];

    public function hasMailSettings(): bool
    {
        return filled($this->mail_host) && filled($this->mail_username) && filled($this->mail_password);
    }

    /**
     * The mailer for this site's emails. Uses the SMTP settings configured
     * on the site in the admin panel, or the default .env mailer if none.
     */
    public function mailer(): Mailer
    {
        if (!$this->hasMailSettings()) {
            return Mail::mailer();
        }

        $name = "site_{$this->id}";

        config(["mail.mailers.{$name}" => [
            'transport' => 'smtp',
            'host' => $this->mail_host,
            'port' => $this->mail_port ?: 587,
            'username' => $this->mail_username,
            'password' => $this->mail_password,
            'scheme' => $this->mail_encryption === 'ssl' ? 'smtps' : null,
            'timeout' => null,
            'from' => [
                'address' => $this->mail_from_address ?: $this->mail_username,
                'name' => $this->mail_from_name ?: $this->name,
            ],
        ]]);

        // Drop any cached instance so edited settings take effect in long-running processes
        Mail::purge($name);

        return Mail::mailer($name);
    }

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
