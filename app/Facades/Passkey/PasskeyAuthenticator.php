<?php
namespace App\Facades\Passkey;

use App\Contracts\Passkey\PasskeyAuthenticator as ContractsPasskeyAuthenticator;
use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed generateOptions(?\App\Contracts\Passkey\PasskeyUser $passkeyUser = null)
 * @method static \App\Contracts\Passkey\PasskeyAuthenticatory setUser(?\App\Contracts\Passkey\PasskeyUser $passkeyUser = null)
 * @method static \App\Contracts\Passkey\PasskeyAuthenticatory setChallenge(?array $challenge = null)
 * @method static \Webauthn\PublicKeyCredentialSource validate(string $data, array $challenge = null)
 * @method static bool validateSilent(string $data, array $challenge = null)
 * @method static void generateChallengeUsing(callable $closure)
 */
class PasskeyAuthenticator extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ContractsPasskeyAuthenticator::class;
    }
}