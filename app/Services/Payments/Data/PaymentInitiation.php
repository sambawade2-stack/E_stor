<?php

namespace App\Services\Payments\Data;

/**
 * Résultat du démarrage d'un paiement.
 */
final readonly class PaymentInitiation
{
    public function __construct(
        public ?string $redirectUrl,
        public ?string $checkoutToken,
    ) {
    }

    /**
     * Paiement sans redirection (ex. paiement à la livraison).
     */
    public static function none(): self
    {
        return new self(null, null);
    }

    public function requiresRedirect(): bool
    {
        return $this->redirectUrl !== null;
    }
}
