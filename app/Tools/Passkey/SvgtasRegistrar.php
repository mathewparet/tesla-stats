<?php
namespace App\Tools\Passkey;

use Svgta\WebAuthn\client;
use App\Contracts\Passkey\PasskeyUser;
use App\Contracts\Passkey\PasskeyRegistrar;

class SvgtasRegistrar extends SvgtasPasskey implements PasskeyRegistrar
{
    /**
     * Get the supported public key parameters
     * 
     * @return PublicKeyCredentialParameters[]
     */
    private function setSupportedPublicKeyParameters()
    {
        return collect(config('passkey.algorithms'))->map(
            fn($algorithm) => $this->webauthn->pubKeyCredParams->add($algorithm::ID)
        )->toArray();
    }

    public function generateOptions(?PasskeyUser $passkeyUser = null)
    {
        $this->setUser($passkeyUser);

        $this->webauthn->userVerification->preferred();
        $this->webauthn->residentKey->preferred();
        $this->webauthn->authenticatorAttachment->all();
        $this->webauthn->attestation->none();

        $this->setSupportedPublicKeyParameters();

        return json_decode($this->webauthn->register()->toJson(), true);
    }

    public function setUser(?PasskeyUser $passkeyUser = null)
    {
        $this->webauthn->user->set(
            $passkeyUser->getUserName(),
            $passkeyUser->getUserId(),
            $passkeyUser->getDisplayName()
        );

        $passkeyUser->passkeys->each(fn($passkey) => $this->webauthn->excludeCredentials->add($passkey->credential_id));

        return $this;
    }

    public function setChallenge(?array $challenge = null)
    {
        
    }

    public function validate(array $data, ?array $challenge = null)
    {
        $aaguid = $this->webauthn->register()->aaguid(json_encode($data));
        return $this->webauthn->register()->validate(); //return a json string
    }

    public function validateSilent(array $data, ?array $challenge = null)
    {
        return rescue(fn() => $this->validate($data, $challenge), false);
    }

    public static function generateChallengeUsing(callable $closure)
    {
        
    }
}