<?php
namespace App\Facades\Passkey;

use App\Contracts\Passkey\Passkey as ContractsPasskey;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \App\Contracts\Passkey\Registrar registrar() The Passkey Registrar instance
 * @method static void configureApplicationIdentityUsing(callable $closure) Configure the application's identity if needed. Defaults are generate based on `config('app.name')` and `config('app.url')`.
 * @method static array getApplicationIdentity() Get the application's identity
 * @method static void useManagementView(string|callable $view)
 * @method static void createModelUsing(callable $closure)
 * @method static void updateModelUsing(callable $closure)
 */
class Passkey extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ContractsPasskey::class;
    }
}