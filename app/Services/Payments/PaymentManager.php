<?php

namespace App\Services\Payments;

use App\Enums\PaymentProvider;
use App\Services\Payments\Contracts\PaymentGateway;
use App\Services\Payments\Gateways\CashOnDeliveryGateway;
use App\Services\Payments\Gateways\PayDunyaGateway;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class PaymentManager
{
    /**
     * Passerelles enregistrées. Pour ajouter un fournisseur (Wave en
     * direct, carte…) : créer la Gateway et l'ajouter ici.
     *
     * @return Collection<int, PaymentGateway>
     */
    public function gateways(): Collection
    {
        return collect([
            app(CashOnDeliveryGateway::class),
            app(PayDunyaGateway::class),
        ]);
    }

    /**
     * Passerelles proposables au checkout (correctement configurées).
     *
     * @return Collection<int, PaymentGateway>
     */
    public function available(): Collection
    {
        return $this->gateways()->filter(fn (PaymentGateway $gateway) => $gateway->isConfigured())->values();
    }

    public function gateway(PaymentProvider $provider): PaymentGateway
    {
        $gateway = $this->gateways()->first(
            fn (PaymentGateway $gateway) => $gateway->provider() === $provider
        );

        if ($gateway === null || ! $gateway->isConfigured()) {
            throw new InvalidArgumentException("Moyen de paiement indisponible : {$provider->value}.");
        }

        return $gateway;
    }

    public function isAvailable(PaymentProvider $provider): bool
    {
        return $this->available()->contains(
            fn (PaymentGateway $gateway) => $gateway->provider() === $provider
        );
    }
}
