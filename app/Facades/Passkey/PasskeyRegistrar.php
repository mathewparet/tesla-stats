<?php
namespace App\Facades\Passkey;

use App\Contracts\Passkey\PasskeyRegistrar as ContractsPasskeyRegistrar;
use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed generateOptions(?\App\Contracts\Passkey\PasskeyUser $passkeyUser = null)
 * @method static \App\Contracts\Passkey\PasskeyRegistrar setUser(?\App\Contracts\Passkey\PasskeyUser $passkeyUser = null)
 * @method static \App\Contracts\Passkey\PasskeyRegistrar setChallenge(?array $challenge = null)
 * @method static \Webauthn\PublicKeyCredentialSource validate(string $data, array $challenge = null)
 * @method static bool validateSilent(string $data, array $challenge = null)
 * @method static void generateChallengeUsing(callable $closure)
 */
class PasskeyRegistrar extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ContractsPasskeyRegistrar::class;
    }
}