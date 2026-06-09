<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // The app is hosted under a subdirectory (APP_URL includes
        // /creackers/backend/public). Without forcing the root URL,
        // Laravel generates URLs from the request's scheme+host only,
        // dropping the subdirectory — which breaks Livewire's signed
        // upload URLs (the signature is validated against a different
        // URL than the one it was generated for).
        if ($url = config('app.url')) {
            URL::forceRootUrl($url);

            if (str_starts_with($url, 'https://')) {
                URL::forceScheme('https');
            }
        }
    }
}
