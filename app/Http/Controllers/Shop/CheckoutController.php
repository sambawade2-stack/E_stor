<?php

namespace App\Http\Controllers\Shop;

use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Models\Order;
use App\Models\ShippingZone;
use App\Services\Cart\CartService;
use App\Services\Checkout\CheckoutService;
use App\Services\Orders\GuestOrderAccess;
use App\Services\Payments\PaymentManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use RuntimeException;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $cart,
        private readonly PaymentManager $payments,
        private readonly GuestOrderAccess $guestAccess,
    ) {}

    public function show(): View|RedirectResponse
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('shop.cart');
        }

        return view('shop.checkout', [
            'items' => $this->cart->items(),
            'coupon' => $this->cart->coupon(),
            'zone' => $this->cart->shippingZone(),
            'zones' => ShippingZone::active()->ordered()->get(),
            'subtotal' => $this->cart->subtotal(),
            'discount' => $this->cart->discount(),
            'paymentMethods' => $this->payments->available(),
        ]);
    }

    public function store(CheckoutRequest $request, CheckoutService $checkout): RedirectResponse
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('shop.cart');
        }

        $validated = $request->validated();

        $provider = PaymentProvider::from($validated['payment']);

        if (! $this->payments->isAvailable($provider)) {
            return back()->withErrors(['payment' => 'Ce moyen de paiement n\'est pas disponible.'])->withInput();
        }

        $zone = ShippingZone::active()->findOrFail($validated['shipping_zone_id']);
        $this->cart->setShippingZone($zone);

        try {
            $order = $checkout->placeOrder(
                customer: [
                    'customer_name' => $validated['customer_name'],
                    'customer_email' => $validated['customer_email'] ?? null,
                    'customer_phone' => $validated['customer_phone'],
                    'address' => $validated['address'],
                    'city' => $zone->name,
                    'notes' => $validated['notes'] ?? null,
                ],
                user: $request->user(),
                provider: $provider,
            );
        } catch (RuntimeException $e) {
            return redirect()->route('shop.cart')->with('error', $e->getMessage());
        }

        // Autorise l'invité à consulter sa confirmation (et le retour paiement)
        $this->guestAccess->grant($order);

        return $this->initiatePayment($order, $provider);
    }

    /**
     * Démarre le paiement en ligne si le fournisseur l'exige.
     * En cas d'échec du fournisseur, la commande reste enregistrée :
     * le client est rassuré et le paiement pourra se faire autrement.
     */
    private function initiatePayment(Order $order, PaymentProvider $provider): RedirectResponse
    {
        try {
            $initiation = $this->payments->gateway($provider)->initiate($order);
        } catch (\Throwable $e) {
            Log::error('Échec d\'initiation du paiement.', [
                'order' => $order->order_number,
                'provider' => $provider->value,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('shop.order.confirmation', $order)
                ->with('error', 'Le paiement en ligne est momentanément indisponible — nous vous contacterons pour finaliser le règlement.');
        }

        if (! $initiation->requiresRedirect()) {
            return redirect()->route('shop.order.confirmation', $order);
        }

        $order->payments()->create([
            'provider' => $provider,
            'status' => PaymentStatus::Pending,
            'amount' => $order->total,
            'currency' => config('shop.currency'),
            'checkout_token' => $initiation->checkoutToken,
        ]);

        return redirect()->away($initiation->redirectUrl);
    }

    public function confirmation(Order $order): View
    {
        abort_unless($this->guestAccess->allows($order), 404);

        $order->load('items.product.primaryImage');

        return view('shop.order-confirmation', compact('order'));
    }
}
