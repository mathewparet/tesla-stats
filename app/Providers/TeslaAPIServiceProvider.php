<?php

namespace App\Providers;

use App\APIService\TeslaAPIServiceManager;
use App\Contracts\TeslaAPIServiceManager as ContractsTeslaAPIServiceManager;
use Illuminate\Support\ServiceProvider;

class TeslaAPIServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(ContractsTeslaAPIServiceManager::class, TeslaAPIServiceManager::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
