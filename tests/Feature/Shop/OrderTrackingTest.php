<?php

namespace Tests\Feature\Shop;

use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTrackingTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(array $attributes = []): Order
    {
        return Order::create([
            'order_number' => Order::generateOrderNumber(),
            'status' => OrderStatus::Pending,
            'customer_name' => 'Awa Ndiaye',
            'customer_phone' => '+221 77 123 45 67',
            'address' => 'Sacré-Cœur 3',
            'city' => 'Dakar',
            'subtotal' => 10000,
            'shipping_cost' => 2000,
            'total' => 12000,
            ...$attributes,
        ]);
    }

    public function test_the_tracking_form_is_public(): void
    {
        $this->get(route('shop.order.track'))
            ->assertOk()
            ->assertSee('Suivre ma commande')
            ->assertSee('Pas besoin de compte');
    }

    public function test_an_order_is_found_with_its_number_and_phone(): void
    {
        $order = $this->makeOrder();

        $this->post(route('shop.order.track.find'), [
            'order_number' => $order->order_number,
            'customer_phone' => '+221 77 123 45 67',
        ])->assertRedirect(route('shop.order.track.show', $order));

        $this->get(route('shop.order.track.show', $order))
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('Commande reçue')
            ->assertSee('12 000');
    }

    /**
     * Le client saisit son numéro comme il veut : avec ou sans indicatif,
     * avec ou sans espaces.
     */
    public function test_the_phone_is_matched_whatever_its_format(): void
    {
        $order = $this->makeOrder(['customer_phone' => '+221 77 123 45 67']);

        foreach (['771234567', '77 123 45 67', '+221771234567', '00221 77 123 45 67'] as $format) {
            $this->post(route('shop.order.track.find'), [
                'order_number' => $order->order_number,
                'customer_phone' => $format,
            ])->assertRedirect(route('shop.order.track.show', $order), "Format refusé : {$format}");

            $this->flushSession();
        }
    }

    public function test_a_wrong_phone_does_not_give_access(): void
    {
        $order = $this->makeOrder();

        $this->post(route('shop.order.track.find'), [
            'order_number' => $order->order_number,
            'customer_phone' => '+221 70 000 00 00',
        ])->assertRedirect()->assertSessionHasErrors('order_number');

        $this->get(route('shop.order.track.show', $order))->assertNotFound();
    }

    /**
     * Le message doit être identique que la commande existe ou non, sinon
     * il devient un oracle permettant d'énumérer les numéros de commande.
     */
    public function test_the_error_message_does_not_reveal_whether_the_order_exists(): void
    {
        $order = $this->makeOrder();

        $wrongPhone = $this->post(route('shop.order.track.find'), [
            'order_number' => $order->order_number,
            'customer_phone' => '+221 70 000 00 00',
        ])->getSession()->get('errors')->first('order_number');

        $this->flushSession();

        $unknownOrder = $this->post(route('shop.order.track.find'), [
            'order_number' => 'ES-20200101-ZZZZZZ',
            'customer_phone' => '+221 77 123 45 67',
        ])->getSession()->get('errors')->first('order_number');

        $this->assertSame($wrongPhone, $unknownOrder);
    }

    public function test_the_result_page_is_not_reachable_without_a_lookup(): void
    {
        $order = $this->makeOrder();

        $this->get(route('shop.order.track.show', $order))->assertNotFound();
    }

    public function test_lookups_are_rate_limited(): void
    {
        $payload = [
            'order_number' => 'ES-20200101-ZZZZZZ',
            'customer_phone' => '+221 77 000 00 00',
        ];

        for ($i = 0; $i < 10; $i++) {
            $this->post(route('shop.order.track.find'), $payload)->assertStatus(302);
        }

        $this->post(route('shop.order.track.find'), $payload)->assertStatus(429);
    }

    /**
     * Régression : l'accès invité reposait sur une clé de session unique
     * écrasée à chaque commande — le client qui commandait deux fois
     * perdait la confirmation de la première.
     */
    public function test_a_guest_keeps_access_to_every_order_of_their_session(): void
    {
        $category = Category::create(['name' => 'Chargeurs']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Chargeur Test 25W',
            'sku' => 'TEST-25W',
            'price' => 10000,
            'stock_quantity' => 10,
            'is_active' => true,
        ]);

        $place = function () use ($product) {
            $this->post(route('shop.cart.add', $product), ['quantity' => 1]);

            $this->post(route('shop.checkout.store'), [
                'customer_name' => 'Awa Ndiaye',
                'customer_phone' => '+221 77 123 45 67',
                'address' => 'Sacré-Cœur 3',
                'city' => 'Dakar',
                'payment' => 'cash_on_delivery',
            ]);
        };

        $place();
        $first = Order::latest('id')->firstOrFail();

        $place();
        $second = Order::latest('id')->firstOrFail();

        $this->assertNotSame($first->order_number, $second->order_number);

        $this->get(route('shop.order.confirmation', $first))->assertOk();
        $this->get(route('shop.order.confirmation', $second))->assertOk();
    }

    public function test_a_logged_in_customer_reaches_their_order_without_a_lookup(): void
    {
        $user = User::factory()->create();
        $order = $this->makeOrder(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('shop.order.track.show', $order))
            ->assertOk()
            ->assertSee($order->order_number);
    }

    public function test_the_timeline_reflects_the_real_order_progress(): void
    {
        $order = $this->makeOrder();

        $this->post(route('shop.order.track.find'), [
            'order_number' => $order->order_number,
            'customer_phone' => '771234567',
        ]);

        $this->get(route('shop.order.track.show', $order))
            ->assertOk()
            ->assertSee('En attente');

        $order->transitionTo(OrderStatus::Paid);
        $order->transitionTo(OrderStatus::Shipped);

        $this->get(route('shop.order.track.show', $order))
            ->assertOk()
            ->assertSee('Expédiée')
            ->assertSee('Paiement confirmé');
    }

    public function test_a_cancelled_order_is_shown_as_such(): void
    {
        $order = $this->makeOrder();

        $this->post(route('shop.order.track.find'), [
            'order_number' => $order->order_number,
            'customer_phone' => '771234567',
        ]);

        $order->transitionTo(OrderStatus::Cancelled);

        $this->get(route('shop.order.track.show', $order))
            ->assertOk()
            ->assertSee('Commande annulée');
    }
}
