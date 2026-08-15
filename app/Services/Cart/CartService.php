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

    /**
     * Caches par requête. items(), coupon() et shippingZone() s'appellent en
     * cascade (total → subtotal → items, discount → coupon → subtotal → items…) :
     * sans mémoïsation, l'affichage du panier rejouait une dizaine de fois
     * les mêmes SELECT.
     *
     * @var Collection<int, array{product: Product, quantity: int, line_total: float}>|null
     */
    private ?Collection $itemsCache = null;

    private bool $couponResolved = false;

    private ?Coupon $couponCache = null;

    private bool $zoneResolved = false;

    private ?ShippingZone $zoneCache = null;

    public function __construct(private readonly Session $session) {}

    /**
     * Invalide les caches après toute modification du panier.
     */
    private function flushCache(): void
    {
        $this->itemsCache = null;
        $this->couponResolved = false;
        $this->couponCache = null;
        $this->zoneResolved = false;
        $this->zoneCache = null;
    }

    /* ----------------------------------------------------------------- */
    /* Articles */
    /* ----------------------------------------------------------------- */

    /**
     * Lignes du panier avec leurs produits chargés.
     * Les produits supprimés, désactivés ou en rupture sont retirés
     * silencieusement : les laisser bloquerait définitivement la commande,
     * CheckoutService refusant toute ligne dont le stock est insuffisant.
     *
     * @return Collection<int, array{product: Product, quantity: int, line_total: float}>
     */
    public function items(): Collection
    {
        if ($this->itemsCache !== null) {
            return $this->itemsCache;
        }

        $stored = $this->storedItems();

        if ($stored === []) {
            return $this->itemsCache = collect();
        }

        $products = Product::active()
            ->inStock()
            ->with(['primaryImage', 'category:id,name,slug'])
            ->findMany(array_keys($stored));

        // Purge les identifiants qui ne correspondent plus à un produit
        // commandable, et cale les quantités sur le stock réellement dispo.
        $kept = $products
            ->mapWithKeys(fn (Product $product) => [
                $product->id => min($stored[$product->id], $product->stock_quantity),
            ])
            ->all();

        if ($kept !== $stored) {
            $this->session->put(self::ITEMS_KEY, $kept);
        }

        return $this->itemsCache = $products->map(fn (Product $product) => [
            'product' => $product,
            'quantity' => $kept[$product->id],
            'line_total' => $product->current_price * $kept[$product->id],
        ])->values();
    }

    public function add(Product $product, int $quantity = 1): void
    {
        $items = $this->storedItems();
        $quantity = ($items[$product->id] ?? 0) + $quantity;

        $items[$product->id] = min($quantity, $product->stock_quantity);

        $this->session->put(self::ITEMS_KEY, $items);
        $this->flushCache();
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
        $this->flushCache();
    }

    public function remove(Product $product): void
    {
        $items = $this->storedItems();
        unset($items[$product->id]);

        $this->session->put(self::ITEMS_KEY, $items);
        $this->flushCache();
    }

    public function clear(): void
    {
        $this->session->forget([self::ITEMS_KEY, self::COUPON_KEY, self::SHIPPING_KEY]);
        $this->flushCache();
    }

    /**
     * Nombre d'articles réellement commandables (badge du header).
     * Basé sur items() et non sur la session brute, pour ne pas afficher un
     * compteur incluant des produits retirés ou en rupture.
     */
    public function count(): int
    {
        return (int) $this->items()->sum('quantity');
    }

    public function isEmpty(): bool
    {
        return $this->items()->isEmpty();
    }

    /* ----------------------------------------------------------------- */
    /* Coupon */
    /* ----------------------------------------------------------------- */

    public function applyCoupon(Coupon $coupon): void
    {
        $this->session->put(self::COUPON_KEY, $coupon->code);
        $this->flushCache();
    }

    public function removeCoupon(): void
    {
        $this->session->forget(self::COUPON_KEY);
        $this->flushCache();
    }

    /**
     * Coupon appliqué, s'il est toujours valide pour le sous-total courant.
     */
    public function coupon(): ?Coupon
    {
        if ($this->couponResolved) {
            return $this->couponCache;
        }

        $code = $this->session->get(self::COUPON_KEY);

        if ($code === null) {
            $this->couponResolved = true;

            return $this->couponCache = null;
        }

        $coupon = Coupon::where('code', $code)->first();

        if ($coupon === null || ! $coupon->isValidFor($this->subtotal())) {
            $this->session->forget(self::COUPON_KEY);
            $coupon = null;
        }

        $this->couponResolved = true;

        return $this->couponCache = $coupon;
    }

    /* ----------------------------------------------------------------- */
    /* Livraison */
    /* ----------------------------------------------------------------- */

    public function setShippingZone(ShippingZone $zone): void
    {
        $this->session->put(self::SHIPPING_KEY, $zone->id);
        $this->flushCache();
    }

    public function shippingZone(): ?ShippingZone
    {
        if ($this->zoneResolved) {
            return $this->zoneCache;
        }

        $id = $this->session->get(self::SHIPPING_KEY);
        $this->zoneResolved = true;

        return $this->zoneCache = $id ? ShippingZone::active()->find($id) : null;
    }

    /* ----------------------------------------------------------------- */
    /* Totaux */
    /* ----------------------------------------------------------------- */

    public function subtotal(): float
    {
        return round($this->items()->sum('line_total'), 2);
    }

    public function discount(): float
    {
        return $this->coupon()?->discountFor($this->subtotal()) ?? 0.0;
    }

    /**
     * Les frais de livraison ne sont plus facturés en ligne : ils varient
     * selon le quartier, le volume et le moment, et sont convenus avec le
     * client à la confirmation. La zone reste demandée pour savoir où
     * livrer, mais n'entre plus dans le total.
     */
    public function shippingCost(): float
    {
        return 0.0;
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
