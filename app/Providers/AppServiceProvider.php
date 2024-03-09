<?php

namespace App\Providers;

use Monolog\Level;
use Monolog\Logger;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Monolog\Processor\IntrospectionProcessor;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->addDebugInfoToLogs();
    }

    private function addDebugInfoToLogs()
    {
        $introspection = new IntrospectionProcessor (
            Level::Debug,
            [
                'Monolog\\',
                'Illuminate\\',
            ]
        );
        Log::pushProcessor($introspection);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
