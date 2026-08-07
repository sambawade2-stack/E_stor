<?php

namespace App\Http\Controllers\Shop;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Orders\GuestOrderAccess;
use App\Services\Payments\PaymentConfirmationService;
use Illuminate\Http\RedirectResponse;

class PaymentController extends Controller
{
    /**
     * Retour du client depuis la page de paiement du fournisseur.
     */
    public function return(Order $order, PaymentConfirmationService $confirmation, GuestOrderAccess $access): RedirectResponse
    {
        abort_unless($access->allows($order), 404);

        $payment = $order->payments()->whereNotNull('checkout_token')->latest()->first();

        if ($payment === null) {
            return redirect()->route('shop.order.confirmation', $order);
        }

        try {
            $verification = $confirmation->confirm($payment);
        } catch (\Throwable) {
            return redirect()->route('shop.order.confirmation', $order)
                ->with('error', 'Impossible de vérifier le paiement pour le moment. Nous vous confirmerons par téléphone.');
        }

        return redirect()->route('shop.order.confirmation', $order)->with(match ($verification->status) {
            PaymentStatus::Paid => ['success' => 'Paiement confirmé, merci !'],
            PaymentStatus::Failed => ['error' => 'Le paiement a été annulé. Vous pouvez nous contacter pour finaliser votre commande.'],
            default => ['success' => 'Commande enregistrée — le paiement est en cours de confirmation.'],
        });
    }
}
