<?php

namespace App\Providers;

use App\Contracts\GeoCode;
use Illuminate\Support\ServiceProvider;

class GeoCodeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(GeoCode::class, function ($app) {
            $geoCodeProfile = $this->getActiveGeoCodeProfile();
            return new $geoCodeProfile($this->getActiveGeoCodeProfileConfig());
        });
    }

    private function getActiveGeoCodeProfile()
    {
        return config('geocode.profiles.' . config('geocode.profile') .'.driver');
    }
    
    private function getActiveGeoCodeProfileConfig()
    {
        return config('geocode.profiles.' . config('geocode.profile') .'.config');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
