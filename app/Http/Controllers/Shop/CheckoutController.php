<?php

namespace App\Http\Controllers\Shop;

use App\Enums\PaymentProvider;
use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Models\Order;
use App\Models\ShippingZone;
use App\Services\Cart\CartService;
use App\Services\Checkout\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use RuntimeException;

class CheckoutController extends Controller
{
    public function __construct(private readonly CartService $cart)
    {
    }

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
        ]);
    }

    public function store(CheckoutRequest $request, CheckoutService $checkout): RedirectResponse
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('shop.cart');
        }

        $validated = $request->validated();

        $zone = ShippingZone::findOrFail($validated['shipping_zone_id']);
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
                provider: PaymentProvider::CashOnDelivery,
            );
        } catch (RuntimeException $e) {
            return redirect()->route('shop.cart')->with('error', $e->getMessage());
        }

        // Autorise l'invité à consulter sa page de confirmation
        session()->put('last_order_number', $order->order_number);

        return redirect()->route('shop.order.confirmation', $order);
    }

    public function confirmation(Order $order): View
    {
        $canView = session('last_order_number') === $order->order_number
            || (auth()->check() && $order->user_id === auth()->id());

        abort_unless($canView, 404);

        $order->load('items');

        return view('shop.order-confirmation', compact('order'));
    }
}
