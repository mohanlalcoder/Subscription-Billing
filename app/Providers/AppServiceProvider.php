<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

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
        Schema::defaultStringLength(191);
        RateLimiter::for('usage', function (Request $request) {
            // Use an authenticated merchant ID in the real implementation.
            $key = $request->user()
                ? 'merchant:' . $request->user()->getAuthIdentifier()
                : 'ip:' . $request->ip();

            return Limit::perMinute(120)->by($key);
        });
    }
}
