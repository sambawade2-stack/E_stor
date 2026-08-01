<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ShippingZone;
use App\Services\Cart\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cart)
    {
    }

    public function index(): View
    {
        return view('shop.cart', [
            'items' => $this->cart->items(),
            'coupon' => $this->cart->coupon(),
            'zone' => $this->cart->shippingZone(),
            'zones' => ShippingZone::active()->ordered()->get(),
            'subtotal' => $this->cart->subtotal(),
            'discount' => $this->cart->discount(),
            'shippingCost' => $this->cart->shippingCost(),
            'total' => $this->cart->total(),
        ]);
    }

    public function add(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->is_active, 404);

        $validated = $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        if (! $product->inStock()) {
            return back()->with('error', 'Ce produit est en rupture de stock.');
        }

        $this->cart->add($product, (int) ($validated['quantity'] ?? 1));

        if ($request->boolean('buy_now')) {
            return redirect()->route('shop.checkout');
        }

        // Ajout rapide depuis une carte produit : on ne coupe pas la navigation
        if ($request->boolean('quick')) {
            return back()->with('success', 'Produit ajouté au panier 🛒');
        }

        return redirect()->route('shop.cart')
            ->with('success', 'Produit ajouté au panier.');
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        $this->cart->update($product, (int) $validated['quantity']);

        return redirect()->route('shop.cart');
    }

    public function remove(Product $product): RedirectResponse
    {
        $this->cart->remove($product);

        return redirect()->route('shop.cart')
            ->with('success', 'Produit retiré du panier.');
    }

    public function applyCoupon(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50'],
        ]);

        $coupon = Coupon::active()->where('code', $validated['code'])->first();

        if ($coupon === null || ! $coupon->isValidFor($this->cart->subtotal())) {
            return back()->withErrors(['code' => 'Ce code promo est invalide ou expiré.']);
        }

        $this->cart->applyCoupon($coupon);

        return back()->with('success', 'Code promo appliqué !');
    }

    public function removeCoupon(): RedirectResponse
    {
        $this->cart->removeCoupon();

        return back()->with('success', 'Code promo retiré.');
    }

    public function setShippingZone(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'shipping_zone_id' => ['required', 'integer', 'exists:shipping_zones,id'],
        ]);

        $this->cart->setShippingZone(ShippingZone::findOrFail($validated['shipping_zone_id']));

        return back();
    }
}
