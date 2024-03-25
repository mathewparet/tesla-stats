<?php
namespace App\Tools\Passkey;

use Svgta\WebAuthn\client;

/**
 * @method \Webauthn\PublicKeyCredentialSource validate(array $data, array $challenge = null);
 */
class SvgtasPasskey
{
    public function __construct(protected client $webauthn) 
    {
        if(config('passkey.session', null))
            $this->webauthn->setSessionKey(config('passkey.session'));
        
        $this->createApplicationEntity();
    }
    
    /**
     * Creates the application entity
     * 
     * @return PublicKeyCredentialRpEntity
     */
    protected function createApplicationEntity()
    {
        $application = Passkey::getApplicationIdentity();

        $this->webauthn->rp->set(
            $application['name'],
            $application['domain'],
            $application['logo']
        );
    }

    public function validateSilent(array $data, ?array $challenge = null)
    {
        return rescue(fn() => $this->validate($data, $challenge), false);
    }
}