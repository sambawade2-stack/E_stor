<?php

namespace Tests\Feature\Shop;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\ShippingZone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsAppContactTest extends TestCase
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

        $this->zone = ShippingZone::create(['name' => 'Dakar', 'cost' => 2000]);
    }

    private function placeOrder(): Order
    {
        $this->post(route('shop.cart.add', $this->product));

        $this->post(route('shop.checkout.store'), [
            'customer_name' => 'Awa Ndiaye',
            'customer_phone' => '+221 77 123 45 67',
            'address' => 'Sacré-Cœur 3',
            'shipping_zone_id' => $this->zone->id,
            'payment' => 'cash_on_delivery',
        ]);

        return Order::latest('id')->firstOrFail();
    }

    /**
     * Un réglage WhatsApp distinct finissait par diverger du téléphone de la
     * boutique : le site renvoyait alors vers un numéro qui n'était plus le
     * bon. Le téléphone des « Informations générales » fait désormais foi.
     */
    public function test_the_whatsapp_link_uses_the_shop_phone(): void
    {
        Setting::set('shop_phone', '+221 77 702 67 66');

        $this->assertStringStartsWith('https://wa.me/221777026766', whatsapp_link());

        $this->get(route('shop.home'))
            ->assertOk()
            ->assertSee('wa.me/221777026766', false);
    }

    public function test_changing_the_shop_phone_changes_the_whatsapp_number(): void
    {
        Setting::set('shop_phone', '+221 76 111 22 33');

        $this->get(route('shop.home'))
            ->assertOk()
            ->assertSee('wa.me/221761112233', false);
    }

    /**
     * Après une commande, le bouton flottant doit rappeler le numéro de
     * commande depuis n'importe quelle page : le client n'a pas à aller le
     * rechercher pour en discuter.
     */
    public function test_the_floating_button_carries_the_last_order_number(): void
    {
        $order = $this->placeOrder();

        foreach ([route('shop.home'), route('shop.catalog'), route('shop.cart')] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee(rawurlencode('ma commande '.$order->order_number), false);
        }
    }

    public function test_a_visitor_without_an_order_gets_the_generic_message(): void
    {
        $this->get(route('shop.home'))
            ->assertOk()
            ->assertSee(rawurlencode('informations sur vos produits'), false)
            ->assertDontSee('ma%20commande%20ES-', false);
    }

    /**
     * Sur une page qui impose son propre message (fiche produit), celui-ci
     * reste prioritaire sur le rappel de commande.
     */
    public function test_a_page_specific_message_wins_over_the_order_reminder(): void
    {
        $this->placeOrder();

        $this->get(route('shop.product', $this->product))
            ->assertOk()
            ->assertSee(rawurlencode('Je souhaite commander ce produit'), false);
    }
}
