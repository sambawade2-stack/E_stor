<?php

namespace App\Services\Payments\Contracts;

use App\Enums\PaymentProvider;
use App\Models\Order;
use App\Services\Payments\Data\PaymentInitiation;
use App\Services\Payments\Data\PaymentVerification;

/**
 * Contrat commun à toutes les passerelles de paiement.
 * Pour ajouter un fournisseur (Wave direct, carte…), implémenter cette
 * interface et l'enregistrer dans PaymentManager::gateways().
 */
interface PaymentGateway
{
    public function provider(): PaymentProvider;

    /**
     * La passerelle est-elle utilisable (clés API renseignées…) ?
     */
    public function isConfigured(): bool;

    /**
     * Libellé et description affichés au checkout.
     */
    public function label(): string;

    public function description(): string;

    /**
     * Démarre le paiement d'une commande.
     * Retourne l'URL de redirection éventuelle et le jeton de session
     * de paiement créé chez le fournisseur.
     */
    public function initiate(Order $order): PaymentInitiation;

    /**
     * Vérifie l'état réel d'un paiement AUPRÈS du fournisseur
     * (jamais sur la seule foi du payload d'un webhook).
     */
    public function verify(string $token): PaymentVerification;
}
