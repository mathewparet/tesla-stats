<?php

namespace App\Providers;

use Inertia\Inertia;
use App\Tools\Passkey\Passkey;
use Illuminate\Validation\Rule;
use Illuminate\Support\ServiceProvider;
use App\Contracts\Passkey\Passkey as ContractsPasskey;
use App\Contracts\Passkey\PasskeyAuthenticator;
use App\Contracts\Passkey\PasskeyRegistrar;
use App\Tools\Passkey\SvgtasAuthenticator;
use App\Tools\Passkey\SvgtasRegistrar;
use Illuminate\Http\Request;

class PasskeyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->bindClasses();
    }

    private function defineValidationRules()
    {
        /**
         * Example:
         * 
         * ```
         * 'passkey' => Rule::passkey($challenge)
         * ```
         * 
         * or
         * 
         * ```
         * 'passkey' => new PasskeyRule($challenge)
         * ```
         */
        // Rule::macro('passkey', fn($challenge) => new PasskeyRule($challenge));
    }

    private function bindClasses()
    {
        $this->app->bind(PasskeyRegistrar::class, SvgtasRegistrar::class);
        $this->app->bind(PasskeyAuthenticator::class, SvgtasAuthenticator::class);
        $this->app->singleton(ContractsPasskey::class, Passkey::class);
    }

    private function defineViews()
    {
        Passkey::useManagementView(fn(Request $request) => Inertia::render('Passkey/Management', ['passkeys' => $request->user()->passkeys()->get()]));
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->defineValidationRules();
        $this->defineViews();
    }
}
