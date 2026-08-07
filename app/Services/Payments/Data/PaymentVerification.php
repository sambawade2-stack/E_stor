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
     * @param  ?float  $amount  montant réellement encaissé, si le fournisseur le communique
     */
    public function __construct(
        public PaymentStatus $status,
        public ?string $reference,
        public array $payload = [],
        public ?float $amount = null,
    ) {
    }

    public function isPaid(): bool
    {
        return $this->status === PaymentStatus::Paid;
    }

    /**
     * Le montant encaissé correspond-il à celui attendu ?
     *
     * Un fournisseur peut renvoyer « payé » pour une facture réglée
     * partiellement ou modifiée : sans ce contrôle, la commande serait
     * soldée pour un montant inférieur au total. Tolérance d'un centime
     * pour absorber les arrondis en virgule flottante.
     */
    public function matchesAmount(float $expected): bool
    {
        return $this->amount === null || abs($this->amount - $expected) < 0.01;
    }
}
