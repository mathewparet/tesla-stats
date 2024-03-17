<?php

namespace App\Providers;

use Monolog\Level;
use Monolog\Logger;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Monolog\Processor\IntrospectionProcessor;
use Illuminate\Auth\Notifications\VerifyEmail;

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

    private function overrideVerificationEmailURL()
    {
        VerifyEmail::createUrlUsing(function ($notifiable) {
            return URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(60),
                [
                    'id' => $notifiable->getKey(),
                    'hash' => sha1($notifiable->getEmailForVerification()),
                    'returnTo' => session()->get('url.intended'),
                ]
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->overrideVerificationEmailURL();
    }
}
