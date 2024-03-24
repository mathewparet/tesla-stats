<?php
namespace App\Tools\Passkey;

use App\Contracts\Passkey\PasskeyRegistrar;
use App\Contracts\Passkey\Passkey as ContractsPasskey;
use App\Contracts\Passkey\PasskeyAuthenticator;
use Laravel\Fortify\Http\Responses\SimpleViewResponse;

class Passkey implements ContractsPasskey
{
    /**
     * @var callable|null
     */
    private static $applicationConfigurationClosure;

    /**
     * @var callable|null
     */
    public static $createModelCallback;
    
    /**
     * @var callable|null
     */
    public static $updateModelCallback;

    /**
     * -----------------------------------------------------------
     * Other variables
     * -----------------------------------------------------------
     * 
     * @var \App\Contracts\Passkey\PasskeyRegistrar $registrar
     */
    public function __construct(private PasskeyRegistrar $registrar, private PasskeyAuthenticator $authenticator) { }

    /**
     * Get the registrar instance
     * 
     * @return \App\Contracts\Passkey\PasskeyRegistrar
     */
    public function registrar()
    {
        return $this->registrar;
    }

    /**
     * Get the authenticator instance
     * 
     * @return \App\Contracts\Passkey\PasskeyAuthenticator
     */
    public function authenticator()
    {
        return $this->authenticator;
    }

    /**
     * Configure the application identity for passkey to use
     * 
     * @param callable $closure
     * 
     * Example:
     * ```
     * use App\Facades\Passkey\Passkey;
     * Passkey::configureApplicationIdentityUsing(fn() => [
     *     'name' => config('app.name'),
     *     'domain' => parse_url(config('app.url'), PHP_URL_HOST),
     *     'logo' => config('passkey.logo', null),
     * ]);
     * ```
     */
    public static function configureApplicationIdentityUsing(callable $closure)
    {
        static::$applicationConfigurationClosure = $closure;
    }

    /**
     * Get the application's identity
     * 
     * @return array
     */
    public static function getApplicationIdentity()
    {
        return isset(static::$applicationConfigurationClosure) && is_callable(static::$applicationConfigurationClosure) 
            ? call_user_func(static::$applicationConfigurationClosure)
            : [
                'name' => config('app.name'),
                'domain' => parse_url(config('app.url'), PHP_URL_HOST),
                'logo' => config('passkey.logo', null),
            ];
    }

    /**
     * Register the vue to be rendered for passkey management
     * 
     * @param callable|string $view
     * @return void
     */
    public static function useManagementView($view)
    {
        app()->singleton('passkey.views.management', function () use ($view) {
            return new SimpleViewResponse($view);
        });
    }

    /**
     * Create the passkey model
     * 
     * @param callable $closure
     * @return void
     * 
     * Example:
     * ```
     * Passkey::createModelUsing(fn(Request $request) => App\Models\Passkey::create($request->safe()->all());
     * ```
     */
    public static function createModelUsing(callable $closure)
    {
        static::$createModelCallback = $closure;
    }
    
    /**
     * Update the passkey model
     * 
     * @param callable $closure
     * @return void
     * 
     * Example:
     * ```
     * Passkey::updateModelUsing(fn(Request $request) => App\Models\Passkey::create($request->safe()->all());
     * ```
     */
    public static function updateModelUsing(callable $closure)
    {
        static::$updateModelCallback = $closure;
    }
}