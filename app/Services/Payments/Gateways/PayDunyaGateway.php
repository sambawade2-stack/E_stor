<?php

namespace App\Services\Payments\Gateways;

use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Services\Payments\Contracts\PaymentGateway;
use App\Services\Payments\Data\PaymentInitiation;
use App\Services\Payments\Data\PaymentVerification;
use App\Services\Payments\PaymentException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * PayDunya : agrégateur couvrant Wave, Orange Money, Free Money et
 * cartes bancaires. https://developers.paydunya.com
 */
class PayDunyaGateway implements PaymentGateway
{
    private const LIVE_URL = 'https://app.paydunya.com/api/v1';

    private const SANDBOX_URL = 'https://app.paydunya.com/sandbox-api/v1';

    public function provider(): PaymentProvider
    {
        return PaymentProvider::PayDunya;
    }

    public function isConfigured(): bool
    {
        return filled(config('services.paydunya.master_key'))
            && filled(config('services.paydunya.private_key'))
            && filled(config('services.paydunya.token'));
    }

    public function label(): string
    {
        return 'Paiement en ligne';
    }

    public function description(): string
    {
        return 'Wave, Orange Money, Free Money ou carte bancaire — paiement sécurisé via PayDunya';
    }

    public function initiate(Order $order): PaymentInitiation
    {
        $response = $this->client()->post('/checkout-invoice/create', [
            'invoice' => [
                'total_amount' => (float) $order->total,
                'description' => 'Commande '.$order->order_number.' — '.setting('shop_name'),
            ],
            'store' => [
                'name' => setting('shop_name'),
                'phone' => setting('shop_phone'),
            ],
            'custom_data' => [
                'order_number' => $order->order_number,
            ],
            'actions' => [
                'callback_url' => route('webhooks.paydunya'),
                'return_url' => route('shop.payment.return', $order),
                'cancel_url' => route('shop.payment.return', $order),
            ],
        ]);

        $data = $response->json();

        if (! $response->successful() || ($data['response_code'] ?? null) !== '00') {
            throw new PaymentException(
                'PayDunya : impossible de démarrer le paiement ('.($data['response_text'] ?? 'erreur inconnue').').'
            );
        }

        return new PaymentInitiation(
            redirectUrl: $data['response_text'],
            checkoutToken: $data['token'],
        );
    }

    public function verify(string $token): PaymentVerification
    {
        $response = $this->client()->get('/checkout-invoice/confirm/'.$token);
        $data = $response->json() ?? [];

        $status = match ($data['status'] ?? null) {
            'completed' => PaymentStatus::Paid,
            'cancelled' => PaymentStatus::Failed,
            default => PaymentStatus::Pending,
        };

        $amount = $data['invoice']['total_amount'] ?? $data['total_amount'] ?? null;

        return new PaymentVerification(
            status: $status,
            reference: $data['receipt_url'] ?? $data['invoice']['token'] ?? $token,
            payload: $data,
            amount: is_numeric($amount) ? (float) $amount : null,
        );
    }

    private function client(): PendingRequest
    {
        $base = config('services.paydunya.mode') === 'live' ? self::LIVE_URL : self::SANDBOX_URL;

        return Http::baseUrl($base)
            ->timeout(20)
            ->withHeaders([
                'PAYDUNYA-MASTER-KEY' => config('services.paydunya.master_key'),
                'PAYDUNYA-PRIVATE-KEY' => config('services.paydunya.private_key'),
                'PAYDUNYA-PUBLIC-KEY' => config('services.paydunya.public_key'),
                'PAYDUNYA-TOKEN' => config('services.paydunya.token'),
            ])
            ->acceptJson();
    }
}
