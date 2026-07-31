<?php

namespace Tests\Feature\Account;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AccountOrdersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'customer']);
    }

    private function makeOrder(array $attributes = []): Order
    {
        return Order::create([
            'order_number' => Order::generateOrderNumber(),
            'status' => OrderStatus::Pending,
            'customer_name' => 'Client Test',
            'customer_phone' => '+221 77 123 45 67',
            'address' => 'Sacré-Cœur 3',
            'city' => 'Dakar',
            'subtotal' => 10000,
            'shipping_cost' => 2000,
            'total' => 12000,
            ...$attributes,
        ]);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('account.orders'))->assertRedirect(route('login'));
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_a_user_sees_their_own_orders(): void
    {
        $user = User::factory()->create();
        $order = $this->makeOrder(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('account.orders'))
            ->assertOk()
            ->assertSee($order->order_number);

        $this->actingAs($user)
            ->get(route('account.orders.show', $order))
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('12 000');
    }

    public function test_a_user_cannot_see_someone_elses_order(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $order = $this->makeOrder(['user_id' => $owner->id]);

        $this->actingAs($intruder)
            ->get(route('account.orders.show', $order))
            ->assertNotFound();
    }

    public function test_the_dashboard_shows_account_statistics(): void
    {
        $user = User::factory()->create();
        $this->makeOrder(['user_id' => $user->id]);
        $this->makeOrder(['user_id' => $user->id, 'status' => OrderStatus::Delivered]);
        $this->makeOrder(['user_id' => $user->id, 'status' => OrderStatus::Cancelled]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('24 000'); // total dépensé hors commandes annulées
    }

    public function test_guest_orders_are_claimed_at_registration(): void
    {
        $order = $this->makeOrder(['customer_email' => 'awa@example.com']);

        $this->post(route('register'), [
            'name' => 'Awa Ndiaye',
            'email' => 'awa@example.com',
            'phone' => '+221 77 123 45 67',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));

        $user = User::where('email', 'awa@example.com')->firstOrFail();

        $this->assertTrue($user->hasRole('customer'));
        $this->assertSame($user->id, $order->fresh()->user_id);
    }

    public function test_guest_orders_are_claimed_at_login(): void
    {
        $user = User::factory()->create(['email' => 'moussa@example.com', 'password' => bcrypt('password')]);
        $order = $this->makeOrder(['customer_email' => 'moussa@example.com']);

        $this->post(route('login'), [
            'email' => 'moussa@example.com',
            'password' => 'password',
        ]);

        $this->assertSame($user->id, $order->fresh()->user_id);
    }
}
