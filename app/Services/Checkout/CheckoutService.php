<?php

namespace App\Services\Checkout;

use App\Enums\OrderStatus;
use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Events\OrderPlaced;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\Cart\CartService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CheckoutService
{
    public function __construct(private readonly CartService $cart) {}

    /**
     * Transforme le panier en commande : vérifie les stocks sous verrou,
     * fige les prix, décrémente les stocks et consomme le coupon.
     *
     * @param  array{customer_name: string, customer_email: ?string, customer_phone: string, address: string, city: string, notes: ?string}  $customer
     *
     * @throws RuntimeException si un produit n'a plus le stock suffisant
     */
    public function placeOrder(array $customer, ?User $user, PaymentProvider $provider): Order
    {
        $items = $this->cart->items();

        if ($items->isEmpty()) {
            throw new RuntimeException('Le panier est vide.');
        }

        $coupon = $this->cart->coupon();

        $order = DB::transaction(function () use ($items, $coupon, $customer, $user, $provider) {
            // Verrouille les lignes produit pour éviter les surventes concurrentes
            $products = Product::whereKey($items->pluck('product.id'))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // Idem pour le coupon : sans relecture sous verrou, deux commandes
            // simultanées lisent le même used_count, passent toutes deux la
            // validation et dépassent max_uses.
            if ($coupon !== null) {
                $coupon = Coupon::whereKey($coupon->getKey())->lockForUpdate()->first();
            }

            $subtotal = 0.0;
            $lines = [];

            foreach ($items as $item) {
                $product = $products[$item['product']->id];
                $quantity = $item['quantity'];

                if (! $product->is_active || $product->stock_quantity < $quantity) {
                    throw new RuntimeException(
                        "Stock insuffisant pour « {$product->name} » (disponible : {$product->stock_quantity})."
                    );
                }

                $unitPrice = $product->current_price;
                $subtotal += $unitPrice * $quantity;

                $lines[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                ];
            }

            // Revalidation sous verrou : le quota a pu être épuisé, ou le
            // coupon désactivé/expiré, depuis son application au panier.
            if ($coupon !== null && ! $coupon->isValidFor($subtotal)) {
                $coupon = null;
            }

            $discount = $coupon?->discountFor($subtotal) ?? 0.0;

            // Livraison convenue avec le client après la commande : rien
            // n'est facturé ici (cf. CartService::shippingCost).
            $shippingCost = 0.0;

            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $user?->id,
                'status' => OrderStatus::Pending,
                ...$customer,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'shipping_cost' => $shippingCost,
                'total' => round($subtotal - $discount + $shippingCost, 2),
                'coupon_id' => $coupon?->id,
                'coupon_code' => $coupon?->code,
                'payment_provider' => $provider,
                'payment_status' => PaymentStatus::Pending,
            ]);

            foreach ($lines as $line) {
                $order->items()->create([
                    'product_id' => $line['product']->id,
                    'product_name' => $line['product']->name,
                    'sku' => $line['product']->sku,
                    'unit_price' => $line['unit_price'],
                    'quantity' => $line['quantity'],
                    'total' => round($line['unit_price'] * $line['quantity'], 2),
                ]);

                $line['product']->decrement('stock_quantity', $line['quantity']);
            }

            $coupon?->increment('used_count');

            return $order;
        });

        $this->cart->clear();

        OrderPlaced::dispatch($order->load('items'));

        return $order;
    }
}
