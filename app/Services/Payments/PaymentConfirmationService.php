<?php

namespace App\Services\Payments;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Services\Payments\Data\PaymentVerification;
use Illuminate\Support\Facades\DB;

/**
 * Confirme un paiement auprès du fournisseur et synchronise la commande.
 * Idempotent : rappelé par le webhook ET le retour navigateur sans effet double.
 */
class PaymentConfirmationService
{
    public function __construct(private readonly PaymentManager $payments)
    {
    }

    public function confirm(Payment $payment): PaymentVerification
    {
        $verification = $this->payments
            ->gateway($payment->provider)
            ->verify($payment->checkout_token);

        DB::transaction(function () use ($payment, $verification) {
            // Déjà soldé : ne rien refaire (idempotence webhook/retour)
            if ($payment->status === PaymentStatus::Paid) {
                return;
            }

            if ($verification->isPaid()) {
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
