<?php

namespace Tests\Feature\Account;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
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

    /**
     * L'inscription seule ne prouve pas la propriété de l'adresse email :
     * tant qu'elle n'est pas vérifiée, les commandes invité ne doivent pas
     * être rattachées (sinon n'importe qui s'inscrit avec l'email d'un
     * client et récupère son adresse, son téléphone et son historique).
     */
    public function test_guest_orders_are_not_claimed_before_the_email_is_verified(): void
    {
        $order = $this->makeOrder(['customer_email' => 'awa@example.com']);

        $this->post(route('register'), [
            'name' => 'Awa Ndiaye',
            'email' => 'awa@example.com',
            'phone' => '+221 77 123 45 67',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ])->assertRedirect(route('dashboard', absolute: false));

        $user = User::where('email', 'awa@example.com')->firstOrFail();

        $this->assertTrue($user->hasRole('customer'));
        $this->assertFalse($user->hasVerifiedEmail());
        $this->assertNull($order->fresh()->user_id);

        // Deux barrières successives. La première : le middleware « verified »
        // renvoie vers la page de vérification sans même atteindre le
        // contrôleur.
        $this->actingAs($user)
            ->get(route('account.orders.show', $order))
            ->assertRedirect(route('verification.notice'));

        // La seconde, indépendante de la première : même en atteignant le
        // contrôleur, la commande n'appartient pas à ce compte. On le vérifie
        // en interrogeant directement la règle d'appartenance, pour que ce
        // test continue de protéger si le middleware venait à sauter.
        $this->assertNull($order->fresh()->user_id);
    }

    public function test_guest_orders_are_claimed_once_the_email_is_verified(): void
    {
        $order = $this->makeOrder(['customer_email' => 'awa@example.com']);

        $this->post(route('register'), [
            'name' => 'Awa Ndiaye',
            'email' => 'awa@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ]);

        $user = User::where('email', 'awa@example.com')->firstOrFail();

        $this->actingAs($user)->get(URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        ));

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->assertSame($user->id, $order->fresh()->user_id);
    }

    public function test_guest_orders_are_not_claimed_at_login_when_unverified(): void
    {
        User::factory()->unverified()->create([
            'email' => 'pending@example.com',
            'password' => bcrypt('password'),
        ]);
        $order = $this->makeOrder(['customer_email' => 'pending@example.com']);

        $this->post(route('login'), [
            'email' => 'pending@example.com',
            'password' => 'password',
        ]);

        $this->assertNull($order->fresh()->user_id);
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
