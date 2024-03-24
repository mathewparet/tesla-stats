<?php
namespace App\Tools\Passkey;

use App\Contracts\Passkey\PasskeyUser;
use App\Contracts\Passkey\PasskeyAuthenticator;

class SvgtasAuthenticator extends SvgtasPasskey implements PasskeyAuthenticator
{
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
            $this->webauthn->userVerification->required();

        return $this;
    }
}