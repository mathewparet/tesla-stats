<?php
namespace App\Contracts\Passkey;

use Closure;

interface Passkey
{

    /**
     * Get the registrar instance
     * 
     * @return \App\Contracts\Passkey\Registrar
     */
    public function registrar();

    /**
     * Configure the application identity for passkey to use
     * 
     * @param callable $closure
     */
    public static function configureApplicationIdentityUsing(callable $closure);

    /**
     * Get the application's identity
     * 
     * @return array
     */
    public static function getApplicationIdentity();

    /**
     * Register the vue to be rendered for passkey management
     * 
     * @param callable|string $view
     * @return void
     */
    public static function useManagementView($view);

    /**
     * Create the passkey model
     * 
     * @param callable $closure
     * @return void
     */
    public static function createModelUsing(callable $closure);
}