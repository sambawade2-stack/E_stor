<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Payments\PaymentConfirmationService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class PayDunyaWebhookController extends Controller
{
    /**
     * IPN PayDunya. Le payload n'est jamais cru sur parole :
     * seul le jeton est extrait, puis l'état réel du paiement est
     * confirmé par un appel API serveur-à-serveur.
     */
    public function __invoke(Request $request, PaymentConfirmationService $confirmation): Response
    {
        $token = $request->input('data.invoice.token')
            ?? $request->input('invoice.token')
            ?? $request->input('token');

        if (! is_string($token) || $token === '') {
            return response()->noContent();
        }

        $payment = Payment::where('checkout_token', $token)->first();

        if ($payment === null) {
            Log::warning('Webhook PayDunya : jeton inconnu.', ['token' => $token]);

            return response()->noContent();
        }

        try {
            $confirmation->confirm($payment);
        } catch (\Throwable $e) {
            Log::error('Webhook PayDunya : échec de confirmation.', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            // 500 pour que PayDunya représente la notification
            abort(500);
        }

        return response()->noContent();
    }
}
