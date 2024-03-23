<?php

namespace App\Providers;

use Inertia\Inertia;
use App\Tools\Passkey\Passkey;
use Illuminate\Validation\Rule;
use App\Tools\Passkey\PasskeyRegistrar;
use Illuminate\Support\ServiceProvider;
use App\Contracts\Passkey\Passkey as ContractsPasskey;
use App\Contracts\Passkey\PasskeyRegistrar as ContractsPasskeyRegistrar;
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
        $this->app->bind(ContractsPasskeyRegistrar::class, SvgtasRegistrar::class);
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
