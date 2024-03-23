<?php
namespace App\Tools\Passkey;

use Cose\Algorithm\Manager;
use Webauthn\PublicKeyCredential;
use Illuminate\Support\Facades\Log;
use App\Contracts\Passkey\PasskeyUser;
use Webauthn\PublicKeyCredentialLoader;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialParameters;
use Webauthn\PublicKeyCredentialUserEntity;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\AttestationStatement\AttestationObjectLoader;
use Webauthn\AuthenticationExtensions\AuthenticationExtensions;
use Webauthn\AttestationStatement\NoneAttestationStatementSupport;
use Webauthn\AuthenticationExtensions\ExtensionOutputCheckerHandler;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use App\Contracts\Passkey\PasskeyRegistrar as ContractsPasskeyRegistrar;

class PasskeyRegistrar implements ContractsPasskeyRegistrar
{
    /**
     * @var null|\App\Contracts\Passkey\PasskeyUser $passkeyUser
     */
    private PasskeyUser $passkeyUser;

    /**
     * @var array $challenge
     */
    private array $challenge;

    /**
     * @var callable|null $challengeGenerator
     */
    private static $challengeGenerator;

    /**
     * @var \Webauthn\AttestationStatement\AttestationStatementSupportManager $attestationStatementSupportManager
     */
    private AttestationStatementSupportManager $attestationStatementSupportManager;

    /**
     * Generate registration options
     * 
     * @param null|\App\Contracts\Passkey\PasskeyUser $passkeyUser
     * @return mixed
     */
    public function generateOptions(?PasskeyUser $passkeyUser = null)
    {
        $this->setUser($passkeyUser);

        $challengeBytes = $this->generateChallenge();

        $publicKeyCredentialCreationOptions = $this->generatePublicKeyCredentialCreationOptions($challengeBytes);

        return $this->serializeOptions($publicKeyCredentialCreationOptions);
    }

    /**
     * Check if challenge response is valid
     * 
     * @param array $data
     * @param $challenge = null
     * @return bool|\Webauthn\PublicKeyCredentialSource
     */
    public function validateSilent($data, array $challenge = null)
    {
        return rescue(fn() => $this->validate($data, $challenge), false);
    }

    /**
     * Verify the registration challenge response
     * 
     * @param $data
     * @param array $challenge = null
     * @return \Webauthn\PublicKeyCredentialSource
     * @throws \Exception
     */
    public function validate($data, array $challenge = null)
    {
        $this->setChallenge($challenge);

        $authenticatorAttestationResponseValidator = $this->prepareAutenticatorAttestationResponseValidator();

        // $authenticatorAssertionResponseValidator = $this->prepareAuthenticatorAssertionResponseValidator();

        $publicKeyCredential = $this->loadPublicKeyCredential($data);

        if (!$this->isPublicKeyCredentialResponseAttestationResponseType($publicKeyCredential)) 
            return false;

        return $authenticatorAttestationResponseValidator->check(
                    $publicKeyCredential->response,
                    PublicKeyCredentialCreationOptions::createFromArray($this->challenge),
                    Passkey::getApplicationIdentity()['domain']
                );
    }

    /**
     * The user related to the passkey
     *
     * @param null|\App\Contracts\Passkey\PasskeyUser $passkeyUser
     * @return \App\Contracts\Passkey\PasskeyRegistrar
     */
    public function setUser(?PasskeyUser $passkeyUser = null)
    {
        $this->passkeyUser = $passkeyUser ?? $this->passkeyUser;

        return $this;
    }

    /**
     * Verify the registration challenge
     * 
     * @param array $challenge
     * @return \App\Contracts\Passkey\PasskeyRegistrar
     */
    public function setChallenge(?array $challenge = null)
    {
        $this->challenge = $challenge ?? $this->challenge;

        return $this;
    }

    /**
     * Override challenge generation logic
     * 
     * @param callable $closure
     * @return void
     * 
     * Example:
     * 
     * ```
     * Registrar::generateChallengeUsing(fn() => random_bytes(16))
     * ```
     */
    public static function generateChallengeUsing(callable $closure)
    {
        static::$challengeGenerator = $closure;
    }

    /**
     * ------------------------------------------------------------
     * Private Helper Functions come below
     * ------------------------------------------------------------
     */

    /**
     * Prepare attaestation statement support manager
    */
    private function prepareAttestationStatementSupportManager()
    {
        if(isset($this->attestationStatementSupportManager)) return;

        $this->attestationStatementSupportManager = AttestationStatementSupportManager::create();
        $this->attestationStatementSupportManager->add(NoneAttestationStatementSupport::create());
    }

     /**
      * Prepare public key credential loader
      * 
      * @return \Webauthn\PublicKeyCredentialLoader
      */
     private function preparePublicKeyCredentialLoader()
     {
        $this->prepareAttestationStatementSupportManager();

        $attestationObjectLoader = AttestationObjectLoader::create($this->attestationStatementSupportManager);

        $publicKeyCredentialLoader = PublicKeyCredentialLoader::create($attestationObjectLoader);

        return $publicKeyCredentialLoader;
     }


     /**
      * Load public key credential
      * 
      * @param $data
      * @return \Webauthn\PublicKeyCredential
      */
     private function loadPublicKeyCredential($data)
     {
        $publicKeyCredentialLoader =  $this->preparePublicKeyCredentialLoader();

        $data = json_encode(json_decode(json_encode($data)));

        Log::debug('Loading data', compact('data'));
        return $publicKeyCredentialLoader->load($data);
     }

     /**
      * Check if `$publicKeyCredential` is an authenticator attestation response
      * 
      * @param \Webauthn\PublicKeyCredential $publicKeyCredential
      * @return bool
      */
     private function isPublicKeyCredentialResponseAttestationResponseType(PublicKeyCredential $publicKeyCredential)
     {
        return $publicKeyCredential->response instanceof AuthenticatorAttestationResponse;
     }

     /**
      * Prepare authenticator attestation response validator
      * 
      * @return \Webauthn\AuthenticatorAttestationResponseValidator
      */
     private function prepareAutenticatorAttestationResponseValidator()
     {
        $this->prepareAttestationStatementSupportManager();

        $extensionOutputCheckerHandler = ExtensionOutputCheckerHandler::create();

        return AuthenticatorAttestationResponseValidator::create(
            $this->attestationStatementSupportManager,
            null,
            null,
            $extensionOutputCheckerHandler
        );
     }

     /**
      * Prepare authenticator assertion response validator
      * 
      * @return \Webauthn\AuthenticatorAssertionResponseValidator
      */
     private function prepareAuthenticatorAssertionResponseValidator()
     {
        $algorithmManager = $this->getSignatureAlgorithms();

        $extensionOutputCheckerHandler = ExtensionOutputCheckerHandler::create();

        return AuthenticatorAssertionResponseValidator::create(
                    null,
                    null,
                    $extensionOutputCheckerHandler,
                    $algorithmManager
                );
     }

     /**
     * Get supported signature algorithms
     * 
     * @return \Cose\Algorithm\Manager
     */
    private function getSignatureAlgorithms()
    {
        $signatureAlgorithms = collect(config('cose.algorithms'))->map((fn($algorithm) => $algorithm::create()));
        
        return Manager::create()->add(...$signatureAlgorithms->toArray());
    }

    /**
     * Generate a challenge
     */
    private function generateChallenge()
    {
        return isset(static::$challengeGenerator) && is_callable(static::$challengeGenerator) 
                                ? call_user_func(static::$challengeGenerator)
                                : random_bytes(16);
    }

    /**
     * Generate the actual credential creation options
     * 
     * @param string $challengeBytes
     * @return \Webauthn\PublicKeyCredentialCreationOptions
     */
    private function generatePublicKeyCredentialCreationOptions(string $challengeBytes)
    {
        return
            PublicKeyCredentialCreationOptions::create(
                rp: $this->createApplicationEntity(),
                user: $this->createUserEntity(),
                challenge: $challengeBytes,
                pubKeyCredParams: $this->getSupportedPublicKeyParameters(),
                authenticatorSelection: AuthenticatorSelectionCriteria::create(),
                attestation: PublicKeyCredentialCreationOptions::ATTESTATION_CONVEYANCE_PREFERENCE_NONE,
                extensions: AuthenticationExtensions::create([
                    'credProps' => true,
                ])
            );
    }

    /**
     * Creates the application entity
     * 
     * @return PublicKeyCredentialRpEntity
     */
    private function createApplicationEntity()
    {
        $application = Passkey::getApplicationIdentity();

        return PublicKeyCredentialRpEntity::create(
            $application['name'],
            $application['domain'],
            $application['logo'],
        );
    }

    /**
     * Creates the user entity
     * 
     * @return PublicKeyCredentialUserEntity
     */
    private function createUserEntity()
    {
        return PublicKeyCredentialUserEntity::create(
            $this->passkeyUser->getUsername(),
            $this->passkeyUser->getUserId(),
            $this->passkeyUser->getDisplayName(),
            $this->passkeyUser->getUserIcon(),
        );
    }

    /**
     * Get the supported public key parameters
     * 
     * @return PublicKeyCredentialParameters[]
     */
    private function getSupportedPublicKeyParameters()
    {
        return collect(config('cose.algorithms'))->map(
            fn($algorithm) => PublicKeyCredentialParameters::createPk($algorithm::identifier())
        )->toArray();
    }

    /**
     * Serialize the public key credential creation options.
     * 
     * Here we manually serialisee the extensions object coz, it doesn't somehow.
     * 
     * @return mixed
     */
    private function serializeOptions(PublicKeyCredentialCreationOptions $publicKeyCredentialCreationOptions)
    {
        $serializedOptions = $publicKeyCredentialCreationOptions->jsonSerialize();

        $this->addExculdeCredentalsSubArrayIfMissing($serializedOptions);
        
        $this->fixSerializationForExtensions($serializedOptions);

        return $this->fixSerializedOptionsToProperPHPFormat($serializedOptions);
    }

    /**
     * Fix the serialized options to the proper PHP format
     * 
     * @param mixed $serializedOptions
     * @return mixed
     */
    private function fixSerializedOptionsToProperPHPFormat($serializedOptions)
    {
        return json_decode(json_encode($serializedOptions), true);
    }

    /**
     * Add the exclude credentials subarray if it's missing
     * 
     * @param mixed $serializedOptions
     */
    private function addExculdeCredentalsSubArrayIfMissing(&$serializedOptions)
    {
        if (!isset($serializedOptions['excludeCredentials'])) $serializedOptions['excludeCredentials'] = [];
    }

    /**
     * This library for some reason doesn't serialize the extensions object,
     * so we'll do it manually
     * 
     * @param mixed $serializedOptions
     */
    private function fixSerializationForExtensions(&$serializedOptions)
    {
        $serializedOptions['extensions'] = $serializedOptions['extensions']->jsonSerialize();
    }

}