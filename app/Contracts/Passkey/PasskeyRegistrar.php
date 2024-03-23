<?php
namespace App\Contracts\Passkey;

use Closure;
use App\Contracts\Passkey\PasskeyUser;

interface PasskeyRegistrar
{
    /**
     * Generate registration options
     * 
     * @param null|\App\Contracts\Passkey\PasskeyUser $passkeyUser
     * @return mixed
     */
    public function generateOptions(?PasskeyUser $passkeyUser = null);

    /**
     * The user related to the passkey
     *
     * @param null|\App\Contracts\Passkey\PasskeyUser $passkeyUser
     * @return \App\Contracts\Passkey\PasskeyRegistrar
     */
    public function setUser(?PasskeyUser $passkeyUser = null);

    /**
     * Verify the registration challenge
     * 
     * @param array $challenge
     * @return \App\Contracts\Passkey\PasskeyRegistrar
     */
    public function setChallenge(?array $challenge = null);

    /**
     * Verify the registration challenge response
     * 
     * @param array $data
     * @param array $challenge = null
     * @return \Webauthn\PublicKeyCredentialSource
     * @throws \Exception
     */

    public function validate(array $data, array $challenge = null);

    /**
     * Check if challenge response is valid
     * 
     * @param array $data
     * @param array $challenge = null
     * @return bool|\Webauthn\PublicKeyCredentialSource
     */
    public function validateSilent(array $data, array $challenge = null);

    /**
     * Override the challenge generator
     *
     * @param callable $closure
     * @return void
     * 
     * Example:
     * 
     * ```
     * use App\Facades\Passkey\PasskeyRegistrar;
     * PasskeyRegistrar::generateChallengeUsing(fn() => random_bytes(16))
     * ```
     */
    public static function generateChallengeUsing(callable $closure);
}