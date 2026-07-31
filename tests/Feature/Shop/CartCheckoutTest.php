<?php

namespace Tests\Feature\Shop;

use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingZone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    private ShippingZone $zone;

    protected function setUp(): void
    {
        parent::setUp();

        $category = Category::create(['name' => 'Chargeurs']);

        $this->product = Product::create([
            'category_id' => $category->id,
            'name' => 'Chargeur Test 25W',
            'sku' => 'TEST-25W',
            'price' => 10000,
            'stock_quantity' => 5,
            'is_active' => true,
        ]);

        $this->zone = ShippingZone::create([
            'name' => 'Dakar',
            'cost' => 2000,
            'delivery_delay' => '24h',
        ]);
    }

    public function test_a_product_can_be_added_to_the_cart(): void
    {
        $response = $this->post(route('shop.cart.add', $this->product), ['quantity' => 2]);

        $response->assertRedirect(route('shop.cart'));

        $this->get(route('shop.cart'))
            ->assertOk()
            ->assertSee('Chargeur Test 25W')
            ->assertSee('20 000');
    }

    public function test_cart_quantity_is_capped_at_available_stock(): void
    {
        $this->post(route('shop.cart.add', $this->product), ['quantity' => 99]);

        $this->get(route('shop.cart'))->assertSee('Stock maximum atteint');
    }

    public function test_an_out_of_stock_product_cannot_be_added(): void
    {
        $this->product->update(['stock_quantity' => 0]);

        $this->post(route('shop.cart.add', $this->product))
            ->assertSessionHas('error');

        $this->assertSame(0, app(\App\Services\Cart\CartService::class)->count());
    }

    public function test_a_valid_coupon_reduces_the_total(): void
    {
        Coupon::create(['code' => 'PROMO10', 'type' => 'percentage', 'value' => 10, 'is_active' => true]);

        $this->post(route('shop.cart.add', $this->product), ['quantity' => 2]);
        $this->post(route('shop.cart.coupon'), ['code' => 'PROMO10'])
            ->assertSessionHas('success');

        $this->get(route('shop.cart'))->assertSee('−2 000');
    }

    public function test_an_invalid_coupon_is_rejected(): void
    {
        $this->post(route('shop.cart.add', $this->product));

        $this->post(route('shop.cart.coupon'), ['code' => 'INCONNU'])
            ->assertSessionHasErrors('code');
    }

    public function test_a_guest_can_place_an_order(): void
    {
        $this->post(route('shop.cart.add', $this->product), ['quantity' => 2]);

        $response = $this->post(route('shop.checkout.store'), [
            'customer_name' => 'Awa Ndiaye',
            'customer_phone' => '+221 77 123 45 67',
            'customer_email' => 'awa@example.com',
            'address' => 'Sacré-Cœur 3, villa 123',
            'shipping_zone_id' => $this->zone->id,
        ]);

        $order = Order::firstOrFail();

        $response->assertRedirect(route('shop.order.confirmation', $order));

        $this->assertNull($order->user_id);
        $this->assertSame(OrderStatus::Pending, $order->status);
        $this->assertSame('Dakar', $order->city);
        $this->assertEquals(20000, (float) $order->subtotal);
        $this->assertEquals(2000, (float) $order->shipping_cost);
        $this->assertEquals(22000, (float) $order->total);
        $this->assertCount(1, $order->items);

        // Le stock est décrémenté et le panier vidé
        $this->assertSame(3, $this->product->fresh()->stock_quantity);
        $this->assertSame(0, app(\App\Services\Cart\CartService::class)->count());

        // L'invité peut voir sa confirmation, pas un autre visiteur
        $this->get(route('shop.order.confirmation', $order))->assertOk();
        $this->flushSession();
        $this->get(route('shop.order.confirmation', $order))->assertNotFound();
    }

    public function test_checkout_clamps_quantity_to_remaining_stock(): void
    {
        $this->post(route('shop.cart.add', $this->product), ['quantity' => 3]);

        // Le stock chute entre l'ajout au panier et le checkout :
        // la commande est passée avec la quantité restante, jamais plus
        $this->product->update(['stock_quantity' => 1]);

        $this->post(route('shop.checkout.store'), [
            'customer_name' => 'Awa Ndiaye',
            'customer_phone' => '+221 77 123 45 67',
            'address' => 'Sacré-Cœur 3',
            'shipping_zone_id' => $this->zone->id,
        ]);

        $order = Order::firstOrFail();

        $this->assertSame(1, $order->items->first()->quantity);
        $this->assertSame(0, $this->product->fresh()->stock_quantity);
    }

    public function test_checkout_validates_required_fields(): void
    {
        $this->post(route('shop.cart.add', $this->product));

        $this->post(route('shop.checkout.store'), [])
            ->assertSessionHasErrors(['customer_name', 'customer_phone', 'address', 'shipping_zone_id']);
    }
}
