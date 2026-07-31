<?php

namespace App\Services\Payments\Gateways;

use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Services\Payments\Contracts\PaymentGateway;
use App\Services\Payments\Data\PaymentInitiation;
use App\Services\Payments\Data\PaymentVerification;

class CashOnDeliveryGateway implements PaymentGateway
{
    public function provider(): PaymentProvider
    {
        return PaymentProvider::CashOnDelivery;
    }

    public function isConfigured(): bool
    {
        return true;
    }

    public function label(): string
    {
        return 'Paiement à la livraison';
    }

    public function description(): string
    {
        return 'Payez en espèces ou par Wave / Orange Money à la réception';
    }

    public function initiate(Order $order): PaymentInitiation
    {
        return PaymentInitiation::none();
    }

    public function verify(string $token): PaymentVerification
    {
        // Encaissé à la livraison : voir Order::transitionTo(Delivered)
        return new PaymentVerification(PaymentStatus::Pending, null);
    }
}
