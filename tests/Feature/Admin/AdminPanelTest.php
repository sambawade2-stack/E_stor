<?php

namespace Tests\Feature\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->customer = User::factory()->create();
        $this->customer->assignRole('customer');
    }

    public function test_guests_and_customers_cannot_access_the_admin_panel(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));

        $this->actingAs($this->customer)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($this->customer)->get(route('admin.products.index'))->assertForbidden();
        $this->actingAs($this->customer)->get(route('admin.settings.edit'))->assertForbidden();
    }

    public function test_an_admin_can_access_every_module(): void
    {
        foreach ([
            'admin.dashboard',
            'admin.products.index',
            'admin.categories.index',
            'admin.brands.index',
            'admin.coupons.index',
            'admin.orders.index',
            'admin.customers.index',
            'admin.reviews.index',
            'admin.settings.edit',
            'admin.activity.index',
        ] as $route) {
            $this->actingAs($this->admin)->get(route($route))->assertOk();
        }
    }

    public function test_an_admin_is_redirected_from_customer_dashboard_to_admin_dashboard(): void
    {
        $this->actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_an_admin_can_create_a_product(): void
    {
        $category = Category::create(['name' => 'Chargeurs']);

        $this->actingAs($this->admin)->post(route('admin.products.store'), [
            'name' => 'Chargeur Rapide 45W',
            'sku' => 'ES-TEST-45W',
            'category_id' => $category->id,
            'price' => 15000,
            'stock_quantity' => 10,
            'is_active' => '1',
            'features_text' => "Puissance : 45W\nConnecteur : USB-C",
        ])->assertRedirect(route('admin.products.index'));

        $product = Product::where('sku', 'ES-TEST-45W')->firstOrFail();

        $this->assertSame('chargeur-rapide-45w', $product->slug);
        $this->assertSame(['Puissance' => '45W', 'Connecteur' => 'USB-C'], $product->features);
        $this->assertTrue($product->is_active);

        // L'action est journalisée avec son auteur
        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Product::class,
            'subject_id' => $product->id,
            'causer_id' => $this->admin->id,
            'event' => 'created',
        ]);
    }

    public function test_order_status_transitions_follow_the_allowed_flow(): void
    {
        $order = $this->makeOrderWithItem();

        // pending → paid : horodatage + paiement encaissé
        $this->actingAs($this->admin)
            ->patch(route('admin.orders.status', $order), ['status' => 'paid'])
            ->assertSessionHas('success');

        $order->refresh();
        $this->assertSame(OrderStatus::Paid, $order->status);
        $this->assertSame(PaymentStatus::Paid, $order->payment_status);
        $this->assertNotNull($order->paid_at);

        // paid → delivered : transition interdite (il faut expédier d'abord)
        $this->actingAs($this->admin)
            ->patch(route('admin.orders.status', $order), ['status' => 'delivered'])
            ->assertSessionHas('error');

        $this->assertSame(OrderStatus::Paid, $order->fresh()->status);
    }

    public function test_cancelling_an_order_restocks_its_items(): void
    {
        $order = $this->makeOrderWithItem();
        $product = Product::firstOrFail();

        $this->assertSame(8, $product->stock_quantity);

        $this->actingAs($this->admin)
            ->patch(route('admin.orders.status', $order), ['status' => 'cancelled'])
            ->assertSessionHas('success');

        $this->assertSame(OrderStatus::Cancelled, $order->fresh()->status);
        $this->assertSame(10, $product->fresh()->stock_quantity);
    }

    private function makeOrderWithItem(): Order
    {
        $category = Category::create(['name' => 'Chargeurs']);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Chargeur Test',
            'sku' => 'TEST-CHG',
            'price' => 10000,
            'stock_quantity' => 8, // stock déjà décrémenté de 2 à la commande
        ]);

        $order = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'status' => OrderStatus::Pending,
            'customer_name' => 'Client Test',
            'customer_phone' => '+221 77 123 45 67',
            'address' => 'Sacré-Cœur 3',
            'city' => 'Dakar',
            'subtotal' => 20000,
            'shipping_cost' => 2000,
            'total' => 22000,
            'payment_provider' => PaymentProvider::CashOnDelivery,
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'unit_price' => 10000,
            'quantity' => 2,
            'total' => 20000,
        ]);

        return $order;
    }
}
