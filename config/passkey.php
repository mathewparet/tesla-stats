<?php

return [
    /**
     * The logo to be used. This MUST be a data URI.
     * 
     * E.g.
     * logo' => 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" fill="#4F46E5"/><rect x="8" y="6" width="8" height="4" fill="#FFFFFF"/><rect x="9" y="10" width="6" height="5" fill="#FFFFFF"/><path d="M8 15h8v2H8z" fill="#FFFFFF"/></svg>'
     */
    'logo' => env('PASSKEY_APP_LOGO', null),
    
    /**
     * Supported algorithms
     */
    'algorithms' => [
        Cose\Algorithm\Signature\RSA\PS256::class,
        Cose\Algorithm\Signature\RSA\PS384::class,
        Cose\Algorithm\Signature\RSA\PS512::class,
        Cose\Algorithm\Signature\RSA\RS256::class,
        Cose\Algorithm\Signature\RSA\RS384::class,
        Cose\Algorithm\Signature\RSA\RS512::class,
        Cose\Algorithm\Signature\ECDSA\ES256::class,
        Cose\Algorithm\Signature\ECDSA\ES384::class,
        Cose\Algorithm\Signature\ECDSA\ES512::class,
        Cose\Algorithm\Signature\EdDSA\Ed256::class,
        Cose\Algorithm\Signature\EdDSA\Ed512::class,
        Cose\Algorithm\Signature\ECDSA\ES256K::class,
    ],

    /**
     * The session key used to store credential options
     */
    'session' => 'publicKeyCredentialCreationOptions',
];