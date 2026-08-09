<?php

namespace Tests\Feature\Shop;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingZone;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderItemImageTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    private ShippingZone $zone;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);

        $category = Category::create(['name' => 'Power banks']);

        $this->product = Product::create([
            'category_id' => $category->id,
            'name' => 'Oraimo Traveler 4',
            'sku' => 'ES-ORA-TRV4',
            'price' => 9500,
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

    public function test_the_customer_sees_the_product_photo_on_their_order(): void
    {
        $image = $this->product->images()->create([
            'path' => 'products/oraimo.webp',
            'alt' => 'Oraimo Traveler 4',
            'is_primary' => true,
        ]);

        $order = $this->placeOrder();

        $this->get(route('shop.order.confirmation', $order))
            ->assertOk()
            ->assertSee($image->url(), false);
    }

    public function test_the_admin_sees_the_product_photo_on_the_order(): void
    {
        $image = $this->product->images()->create([
            'path' => 'products/oraimo.webp',
            'alt' => 'Oraimo Traveler 4',
            'is_primary' => true,
        ]);

        $order = $this->placeOrder();

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee($image->url(), false);
    }

    /**
     * Un produit sans photo ne doit pas laisser d'image cassée dans la
     * commande : on retombe sur le visuel générique.
     */
    public function test_a_product_without_a_photo_falls_back_to_the_placeholder(): void
    {
        $order = $this->placeOrder();

        $this->get(route('shop.order.confirmation', $order))
            ->assertOk()
            ->assertSee('placeholder-product.svg', false);
    }

    /**
     * La ligne de commande fige le nom et le prix, jamais l'image : si le
     * produit est supprimé après coup, l'affichage doit rester intact.
     */
    public function test_a_deleted_product_still_renders_its_order_line(): void
    {
        $order = $this->placeOrder();

        $this->product->delete();

        $this->get(route('shop.order.confirmation', $order))
            ->assertOk()
            ->assertSee('Oraimo Traveler 4')
            ->assertSee('placeholder-product.svg', false);

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('Oraimo Traveler 4');
    }
}
