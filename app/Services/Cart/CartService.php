<?php

namespace App\Services\Cart;

use App\Models\Coupon;
use App\Models\Product;
use App\Models\ShippingZone;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Collection;

/**
 * Panier stocké en session : [product_id => quantité].
 * Le coupon et la zone de livraison choisis y sont également conservés.
 */
class CartService
{
    private const ITEMS_KEY = 'cart.items';

    private const COUPON_KEY = 'cart.coupon';

    private const SHIPPING_KEY = 'cart.shipping_zone';

    public function __construct(private readonly Session $session)
    {
    }

    /* ----------------------------------------------------------------- */
    /* Articles                                                           */
    /* ----------------------------------------------------------------- */

    /**
     * Lignes du panier avec leurs produits chargés.
     * Les produits supprimés ou désactivés sont retirés silencieusement.
     *
     * @return Collection<int, array{product: Product, quantity: int, line_total: float}>
     */
    public function items(): Collection
    {
        $stored = $this->storedItems();

        if ($stored === []) {
            return collect();
        }

        $products = Product::active()
            ->with(['primaryImage', 'category:id,name,slug'])
            ->findMany(array_keys($stored));

        // Purge les identifiants qui ne correspondent plus à un produit actif
        $this->session->put(self::ITEMS_KEY, $products->pluck('id')
            ->mapWithKeys(fn ($id) => [$id => $stored[$id]])
            ->all());

        return $products->map(function (Product $product) use ($stored) {
            $quantity = min($stored[$product->id], max($product->stock_quantity, 1));

            return [
                'product' => $product,
                'quantity' => $quantity,
                'line_total' => $product->current_price * $quantity,
            ];
        })->values();
    }

    public function add(Product $product, int $quantity = 1): void
    {
        $items = $this->storedItems();
        $quantity = ($items[$product->id] ?? 0) + $quantity;

        $items[$product->id] = min($quantity, $product->stock_quantity);

        $this->session->put(self::ITEMS_KEY, $items);
    }

    public function update(Product $product, int $quantity): void
    {
        if ($quantity < 1) {
            $this->remove($product);

            return;
        }

        $items = $this->storedItems();
        $items[$product->id] = min($quantity, $product->stock_quantity);

        $this->session->put(self::ITEMS_KEY, $items);
    }

    public function remove(Product $product): void
    {
        $items = $this->storedItems();
        unset($items[$product->id]);

        $this->session->put(self::ITEMS_KEY, $items);
    }

    public function clear(): void
    {
        $this->session->forget(['cart.items', 'cart.coupon', 'cart.shipping_zone']);
    }

    public function count(): int
    {
        return array_sum($this->storedItems());
    }

    public function isEmpty(): bool
    {
        return $this->items()->isEmpty();
    }

    /* ----------------------------------------------------------------- */
    /* Coupon                                                             */
    /* ----------------------------------------------------------------- */

    public function applyCoupon(Coupon $coupon): void
    {
        $this->session->put(self::COUPON_KEY, $coupon->code);
    }

    public function removeCoupon(): void
    {
        $this->session->forget(self::COUPON_KEY);
    }

    /**
     * Coupon appliqué, s'il est toujours valide pour le sous-total courant.
     */
    public function coupon(): ?Coupon
    {
        $code = $this->session->get(self::COUPON_KEY);

        if ($code === null) {
            return null;
        }

        $coupon = Coupon::where('code', $code)->first();

        if ($coupon === null || ! $coupon->isValidFor($this->subtotal())) {
            $this->removeCoupon();

            return null;
        }

        return $coupon;
    }

    /* ----------------------------------------------------------------- */
    /* Livraison                                                          */
    /* ----------------------------------------------------------------- */

    public function setShippingZone(ShippingZone $zone): void
    {
        $this->session->put(self::SHIPPING_KEY, $zone->id);
    }

    public function shippingZone(): ?ShippingZone
    {
        $id = $this->session->get(self::SHIPPING_KEY);

        return $id ? ShippingZone::active()->find($id) : null;
    }

    /* ----------------------------------------------------------------- */
    /* Totaux                                                             */
    /* ----------------------------------------------------------------- */

    public function subtotal(): float
    {
        return round($this->items()->sum('line_total'), 2);
    }

    public function discount(): float
    {
        return $this->coupon()?->discountFor($this->subtotal()) ?? 0.0;
    }

    public function shippingCost(): float
    {
        return (float) ($this->shippingZone()?->cost ?? 0);
    }

    public function total(): float
    {
        return round($this->subtotal() - $this->discount() + $this->shippingCost(), 2);
    }

    /**
     * @return array<int, int>
     */
    private function storedItems(): array
    {
        return $this->session->get(self::ITEMS_KEY, []);
    }
}
