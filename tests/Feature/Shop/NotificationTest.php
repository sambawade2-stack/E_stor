<?php

namespace Tests\Feature\Shop;

use App\Enums\OrderStatus;
use App\Enums\PaymentProvider;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingZone;
use App\Notifications\NewOrderAlert;
use App\Notifications\OrderConfirmation;
use App\Notifications\OrderStatusUpdated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    private ShippingZone $zone;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

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

    private function checkout(?string $email = 'awa@example.com'): void
    {
        $this->post(route('shop.cart.add', $this->product), ['quantity' => 1]);

        $this->post(route('shop.checkout.store'), [
            'customer_name' => 'Awa Ndiaye',
            'customer_phone' => '+221 77 123 45 67',
            'customer_email' => $email,
            'address' => 'Sacré-Cœur 3',
            'shipping_zone_id' => $this->zone->id,
            'payment' => 'cash_on_delivery',
        ]);
    }

    public function test_placing_an_order_notifies_the_customer_and_the_admin(): void
    {
        $this->checkout();

        Notification::assertSentTo(
            new AnonymousNotifiable,
            OrderConfirmation::class,
            fn ($notification, $channels, $notifiable) => $notifiable->routes['mail'] === 'awa@example.com'
        );

        Notification::assertSentTo(
            new AnonymousNotifiable,
            NewOrderAlert::class,
            fn ($notification, $channels, $notifiable) => $notifiable->routes['mail'] === setting('shop_email', config('shop.admin_email'))
        );
    }

    public function test_a_guest_without_email_only_notifies_the_admin(): void
    {
        $this->checkout(email: null);

        Notification::assertNotSentTo(new AnonymousNotifiable, OrderConfirmation::class);
        Notification::assertSentTimes(NewOrderAlert::class, 1);
    }

    public function test_a_status_change_notifies_the_customer(): void
    {
        $this->checkout();

        Order::firstOrFail()->transitionTo(OrderStatus::Processing);

        Notification::assertSentTo(
            new AnonymousNotifiable,
            OrderStatusUpdated::class,
            fn ($notification, $channels, $notifiable) => $notifiable->routes['mail'] === 'awa@example.com'
        );
    }

    public function test_notifications_are_queued(): void
    {
        $order = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'status' => OrderStatus::Pending,
            'customer_name' => 'Client Test',
            'customer_email' => 'client@example.com',
            'customer_phone' => '+221 77 123 45 67',
            'address' => 'Sacré-Cœur 3',
            'city' => 'Dakar',
            'subtotal' => 10000,
            'shipping_cost' => 2000,
            'total' => 12000,
            'payment_provider' => PaymentProvider::CashOnDelivery,
        ]);

        $this->assertContains(
            \Illuminate\Contracts\Queue\ShouldQueue::class,
            class_implements(new OrderConfirmation($order))
        );
        $this->assertContains(
            \Illuminate\Contracts\Queue\ShouldQueue::class,
            class_implements(new NewOrderAlert($order))
        );
        $this->assertContains(
            \Illuminate\Contracts\Queue\ShouldQueue::class,
            class_implements(new OrderStatusUpdated($order, OrderStatus::Pending))
        );
    }
}
