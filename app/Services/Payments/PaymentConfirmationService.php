<?php

namespace App\Services\Payments;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Services\Payments\Data\PaymentVerification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Confirme un paiement auprès du fournisseur et synchronise la commande.
 * Idempotent : rappelé par le webhook ET le retour navigateur sans effet double.
 */
class PaymentConfirmationService
{
    public function __construct(private readonly PaymentManager $payments) {}

    public function confirm(Payment $payment): PaymentVerification
    {
        $verification = $this->payments
            ->gateway($payment->provider)
            ->verify($payment->checkout_token);

        DB::transaction(function () use ($payment, $verification) {
            // Le webhook et le retour navigateur peuvent arriver en même
            // temps : on relit la ligne sous verrou pour que le test
            // d'idempotence ci-dessous porte sur l'état réellement en base,
            // et non sur une instance chargée avant la transaction (sinon
            // les deux requêtes soldent la commande et notifient le client
            // en double).
            $payment = Payment::whereKey($payment->getKey())->lockForUpdate()->first();

            if ($payment === null || $payment->status === PaymentStatus::Paid) {
                return;
            }

            if ($verification->isPaid()) {
                // Un « payé » pour un montant inférieur au total ne solde rien
                if (! $verification->matchesAmount((float) $payment->amount)) {
                    Log::critical('Montant de paiement incohérent — commande non soldée.', [
                        'payment_id' => $payment->id,
                        'expected' => (float) $payment->amount,
                        'received' => $verification->amount,
                    ]);

                    return;
                }

                $payment->update([
                    'status' => PaymentStatus::Paid,
                    'paid_at' => now(),
                    'provider_reference' => $verification->reference,
                    'payload' => $verification->payload,
                ]);

                $order = $payment->order;

                if (in_array($order->status, [OrderStatus::Pending, OrderStatus::Processing], true)) {
                    $order->transitionTo(OrderStatus::Paid);
                }
            } elseif ($verification->status === PaymentStatus::Failed) {
                $payment->update([
                    'status' => PaymentStatus::Failed,
                    'payload' => $verification->payload,
                ]);
            }
        });

        return $verification;
    }
}
