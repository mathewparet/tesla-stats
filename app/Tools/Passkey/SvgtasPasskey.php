<?php
namespace App\Tools\Passkey;

use Svgta\WebAuthn\client;

class SvgtasPasskey
{
    public function __construct(protected client $webauthn) 
    {
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
}