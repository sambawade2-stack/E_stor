<?php

namespace Tests\Feature\Shop;

use App\Enums\DiscountType;
use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingZone;
use App\Services\Cart\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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

        $this->assertSame(0, app(CartService::class)->count());
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
            'payment' => 'cash_on_delivery',
        ]);

        $order = Order::firstOrFail();

        $response->assertRedirect(route('shop.order.confirmation', $order));

        $this->assertNull($order->user_id);
        $this->assertSame(OrderStatus::Pending, $order->status);
        $this->assertSame('Dakar', $order->city);
        $this->assertEquals(20000, (float) $order->subtotal);
        // La livraison est convenue après la commande : rien n'est facturé ici
        $this->assertEquals(0, (float) $order->shipping_cost);
        $this->assertEquals(20000, (float) $order->total);
        $this->assertSame('À convenir', $order->shippingLabel());
        $this->assertCount(1, $order->items);

        // Le stock est décrémenté et le panier vidé
        $this->assertSame(3, $this->product->fresh()->stock_quantity);
        $this->assertSame(0, app(CartService::class)->count());

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
            'payment' => 'cash_on_delivery',
        ]);

        $order = Order::firstOrFail();

        $this->assertSame(1, $order->items->first()->quantity);
        $this->assertSame(0, $this->product->fresh()->stock_quantity);
    }

    public function test_checkout_validates_required_fields(): void
    {
        $this->post(route('shop.cart.add', $this->product));

        $this->post(route('shop.checkout.store'), [])
            ->assertSessionHasErrors(['customer_name', 'customer_phone', 'address', 'shipping_zone_id', 'payment']);
    }

    /**
     * Le tarif de la zone ne doit plus apparaître ni entrer dans le total :
     * il varie selon le quartier et le volume, et se convient après coup.
     */
    public function test_the_shipping_zone_price_is_never_shown_nor_charged(): void
    {
        $this->post(route('shop.cart.add', $this->product));

        foreach ([route('shop.cart'), route('shop.checkout')] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('À convenir')
                ->assertDontSee('2 000 FCFA'); // le coût de la zone Dakar
        }

        $this->post(route('shop.checkout.store'), [
            'customer_name' => 'Awa Ndiaye',
            'customer_phone' => '+221 77 123 45 67',
            'address' => 'Sacré-Cœur 3',
            'shipping_zone_id' => $this->zone->id,
            'payment' => 'cash_on_delivery',
        ]);

        $order = Order::firstOrFail();

        // La zone reste enregistrée : on doit savoir où livrer
        $this->assertSame('Dakar', $order->city);
        $this->assertEquals(0, (float) $order->shipping_cost);
        $this->assertEquals($order->subtotal, $order->total);

        $this->get(route('shop.order.confirmation', $order))
            ->assertOk()
            ->assertSee('À convenir');
    }

    /**
     * CheckoutService ajuste le stock avec decrement(), qui ne déclenche
     * aucun événement Eloquent : sans purge explicite, l'accueil restait
     * figé jusqu'à 10 minutes après la vente du dernier exemplaire.
     */
    public function test_the_home_page_cache_is_cleared_when_an_order_is_placed(): void
    {
        $this->product->update(['is_featured' => true, 'stock_quantity' => 1]);

        $this->get(route('shop.home'))->assertOk();
        $this->assertTrue(Cache::has('home.sections'), 'Le cache de l\'accueil devrait être rempli.');

        $this->post(route('shop.cart.add', $this->product));
        $this->post(route('shop.checkout.store'), [
            'customer_name' => 'Awa Ndiaye',
            'customer_phone' => '+221 77 123 45 67',
            'address' => 'Sacré-Cœur 3',
            'shipping_zone_id' => $this->zone->id,
            'payment' => 'cash_on_delivery',
        ]);

        $this->assertSame(0, $this->product->fresh()->stock_quantity);
        $this->assertFalse(
            Cache::has('home.sections'),
            'Le cache de l\'accueil doit être purgé pour refléter le nouveau stock.'
        );
    }

    /**
     * Une zone désactivée n'est pas livrable. Sans filtre sur is_active, la
     * validation la laissait passer puis CartService::shippingZone() la
     * rejetait : la commande partait avec 0 F de frais de port.
     */
    public function test_an_inactive_shipping_zone_is_rejected_at_checkout(): void
    {
        $inactive = ShippingZone::create([
            'name' => 'Ziguinchor',
            'cost' => 15000,
            'is_active' => false,
        ]);

        $this->post(route('shop.cart.add', $this->product));

        $this->post(route('shop.checkout.store'), [
            'customer_name' => 'Awa Ndiaye',
            'customer_phone' => '+221 77 123 45 67',
            'address' => 'Sacré-Cœur 3',
            'shipping_zone_id' => $inactive->id,
            'payment' => 'cash_on_delivery',
        ])->assertSessionHasErrors('shipping_zone_id');

        $this->assertSame(0, Order::count());
    }

    /**
     * Un produit tombé en rupture après son ajout doit sortir du panier :
     * le garder bloquait toute commande, CheckoutService refusant la ligne
     * à chaque tentative.
     */
    public function test_a_product_out_of_stock_is_dropped_from_the_cart(): void
    {
        $this->post(route('shop.cart.add', $this->product), ['quantity' => 2]);

        $this->product->update(['stock_quantity' => 0]);

        $this->get(route('shop.cart'))
            ->assertOk()
            ->assertDontSee('Chargeur Test 25W');

        $this->assertTrue(app(CartService::class)->isEmpty());
        $this->assertSame(0, app(CartService::class)->count());
    }

    public function test_checkout_is_not_blocked_by_a_product_gone_out_of_stock(): void
    {
        $other = Product::create([
            'category_id' => $this->product->category_id,
            'name' => 'Câble USB-C',
            'sku' => 'TEST-USBC',
            'price' => 3000,
            'stock_quantity' => 4,
            'is_active' => true,
        ]);

        $this->post(route('shop.cart.add', $this->product), ['quantity' => 1]);
        $this->post(route('shop.cart.add', $other), ['quantity' => 1]);

        $this->product->update(['stock_quantity' => 0]);

        $this->post(route('shop.checkout.store'), [
            'customer_name' => 'Awa Ndiaye',
            'customer_phone' => '+221 77 123 45 67',
            'address' => 'Sacré-Cœur 3',
            'shipping_zone_id' => $this->zone->id,
            'payment' => 'cash_on_delivery',
        ]);

        $order = Order::firstOrFail();

        $this->assertCount(1, $order->items);
        $this->assertSame('Câble USB-C', $order->items->first()->product_name);
    }

    /**
     * Le quota d'un coupon épuisé entre son application et la validation
     * de la commande ne doit plus donner de remise.
     */
    public function test_a_coupon_whose_quota_ran_out_is_not_applied(): void
    {
        $coupon = Coupon::create([
            'code' => 'PROMO1',
            'type' => DiscountType::Fixed,
            'value' => 1000,
            'max_uses' => 1,
            'used_count' => 0,
            'is_active' => true,
        ]);

        $this->post(route('shop.cart.add', $this->product));
        $this->post(route('shop.cart.coupon'), ['code' => 'PROMO1']);

        // Un autre client consomme le dernier usage disponible
        $coupon->update(['used_count' => 1]);

        $this->post(route('shop.checkout.store'), [
            'customer_name' => 'Awa Ndiaye',
            'customer_phone' => '+221 77 123 45 67',
            'address' => 'Sacré-Cœur 3',
            'shipping_zone_id' => $this->zone->id,
            'payment' => 'cash_on_delivery',
        ]);

        $order = Order::firstOrFail();

        $this->assertSame('0.00', $order->discount);
        $this->assertNull($order->coupon_id);
        $this->assertSame(1, $coupon->fresh()->used_count);
    }

    /**
     * items(), coupon(), subtotal(), discount() et total() s'appellent en
     * cascade : sans mémoïsation, la page rejouait une dizaine de fois les
     * mêmes SELECT.
     */
    public function test_the_cart_page_does_not_repeat_the_same_queries(): void
    {
        Coupon::create([
            'code' => 'PROMO1',
            'type' => DiscountType::Fixed,
            'value' => 500,
            'is_active' => true,
        ]);

        $this->post(route('shop.cart.add', $this->product), ['quantity' => 2]);
        $this->post(route('shop.cart.coupon'), ['code' => 'PROMO1']);

        DB::connection()->enableQueryLog();
        DB::connection()->flushQueryLog();

        $this->get(route('shop.cart'))->assertOk();

        $queries = array_column(DB::connection()->getQueryLog(), 'query');
        $countFor = fn (string $table) => count(array_filter(
            $queries,
            fn (string $sql) => str_contains($sql, 'from "'.$table.'"')
        ));

        $this->assertLessThanOrEqual(2, $countFor('products'), 'Le catalogue est relu plusieurs fois.');
        $this->assertLessThanOrEqual(1, $countFor('coupons'), 'Le coupon est relu plusieurs fois.');
        $this->assertLessThanOrEqual(2, $countFor('shipping_zones'), 'Les zones sont relues plusieurs fois.');
    }
}
