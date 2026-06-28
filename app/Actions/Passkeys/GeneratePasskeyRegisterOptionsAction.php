<?php namespace App\Actions\Passkeys;
/**
 * @copyright (c) 2026 MCortesDev (https://mcortes.dev) - All Rights Reserved
 */

use Cose\Algorithms;
use Spatie\LaravelPasskeys\Actions\GeneratePasskeyRegisterOptionsAction as BaseGeneratePasskeyRegisterOptionsAction;
use Spatie\LaravelPasskeys\Models\Concerns\HasPasskeys;
use Spatie\LaravelPasskeys\Support\Serializer;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialParameters;

/**
 * Generar opciones de registro de passkey con algoritmos soportados
 *
 * @author Moisés Cortés C. <soy@mcortes.dev>
 *
 * @version 1.0.0
 */
class GeneratePasskeyRegisterOptionsAction extends BaseGeneratePasskeyRegisterOptionsAction
{
    public function execute(
        HasPasskeys $authenticatable,
        bool $asJson = true,
    ): string|PublicKeyCredentialCreationOptions {
        $options = PublicKeyCredentialCreationOptions::create(
            rp: $this->relatedPartyEntity(),
            user: $this->generateUserEntity($authenticatable),
            challenge: $this->challenge(),
            pubKeyCredParams: $this->publicKeyCredentialParameters(),
            authenticatorSelection: $this->authenticatorSelection(),
            attestation: PublicKeyCredentialCreationOptions::ATTESTATION_CONVEYANCE_PREFERENCE_NONE,
        );

        if ($asJson) {
            return Serializer::make()->toJson($options);
        }

        return $options;
    }

    /**
     * @return PublicKeyCredentialParameters[]
     */
    protected function publicKeyCredentialParameters(): array
    {
        return [
            PublicKeyCredentialParameters::createPk(Algorithms::COSE_ALGORITHM_ES256),
            PublicKeyCredentialParameters::createPk(Algorithms::COSE_ALGORITHM_RS256),
        ];
    }
}
