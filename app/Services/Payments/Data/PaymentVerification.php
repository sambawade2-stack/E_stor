<?php

namespace App\Services\Payments\Data;

use App\Enums\PaymentStatus;

/**
 * État réel d'un paiement, tel que confirmé par le fournisseur.
 */
final readonly class PaymentVerification
{
    /**
     * @param  array<string, mixed>  $payload  réponse brute du fournisseur (audit)
     */
    public function __construct(
        public PaymentStatus $status,
        public ?string $reference,
        public array $payload = [],
    ) {
    }

    public function isPaid(): bool
    {
        return $this->status === PaymentStatus::Paid;
    }
}
