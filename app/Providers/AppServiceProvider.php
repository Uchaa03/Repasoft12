<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        //Call to Service RateLimiter
        $this->configureRateLimiting();
    }

    protected function configureRateLimiting(): void {
        //Limit Rate config of petitions
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(120) //120 petitions per minute
            ->by($request->user()?->id ?: $request->ip());
        });
    }
}
