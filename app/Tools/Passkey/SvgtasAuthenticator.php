<?php
namespace App\Tools\Passkey;

use App\Models\Passkey;
use Illuminate\Support\Facades\Log;
use App\Contracts\Passkey\PasskeyUser;
use App\Contracts\Passkey\PasskeyAuthenticator;

class SvgtasAuthenticator extends SvgtasPasskey implements PasskeyAuthenticator
{
    private $passkeyUser;

    /**
     * Generate the authentication options
     * 
     * @param \App\Contracts\Passkey\PasskeyUser|null $passkeyUser
     * @return array
     */
    public function generateOptions(?PasskeyUser $passkeyUser = null)
    {
        $this->setUser($passkeyUser);

        return json_decode($this->webauthn->authenticate()->toJson(), true );
    }

    /**
     * Set the user
     * 
     * @param \App\Contracts\Passkey\PasskeyUser|null $passkeyUser
     * @return \App\Contracts\Passkey\PasskeyAuthenticator
     */
    public function setUser(?PasskeyUser $passkeyUser = null)
    {
        if($passkeyUser)
            $passkeyUser->passkeys->each(fn($passkey) => $this->webauthn->allowCredentials->add($passkey->credential_id));
        else
            $this->webauthn->userVerification->discouraged();

        $this->passkeyUser = $passkeyUser;

        return $this;
    }

    public function validate(array $data, ?array $challenge = null)
    {
        Log::debug('Passkey validation for user login started');
        $response = $this->webauthn->authenticate()->response(json_encode($data));
        Log::debug('Passkey validation for user login completed', compact('response'));
        if($this->passkeyUser)
            $passkey = $this->passkeyUser->passkeys()->credential($response['credentialId'])->firstOrFail();
        else
            $passkey = Passkey::credential($response['credentialId'])->user($response['userHandle'])->firstOrFail();

        return $this->webauthn->authenticate()->validate($passkey->public_key);
    }
}