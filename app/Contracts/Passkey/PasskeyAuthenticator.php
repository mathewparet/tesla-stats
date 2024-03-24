<?php
namespace App\Contracts\Passkey;

use App\Contracts\Passkey\PasskeyUser;

interface PasskeyAuthenticator
{
    /**
     * Generate the authentication options
     * 
     * @param \App\Contracts\Passkey\PasskeyUser|null $passkeyUser
     * @return array
     */
    public function generateOptions(?PasskeyUser $passkeyUser = null);

    /**
     * Set the user
     * 
     * @param \App\Contracts\Passkey\PasskeyUser|null $passkeyUser
     * @return \App\Contracts\Passkey\PasskeyAuthenticator
     */
    public function setUser(?PasskeyUser $passkeyUser = null);
}