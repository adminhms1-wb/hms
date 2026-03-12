<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Hotel;

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
        // Set application timezone from hotel settings
        try {
            $hotel = Hotel::first();
            if ($hotel && $hotel->timezone) {
                config(['app.timezone' => $hotel->timezone]);
                date_default_timezone_set($hotel->timezone);
            }
        } catch (\Exception $e) {
            // Database might not be ready, use default
        }
    }
}
