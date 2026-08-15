<?php

namespace Tests\Feature\Shop;

use App\Enums\OrderStatus;
use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

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

    }

    private function configurePayDunya(): void
    {
        config([
            'services.paydunya.master_key' => 'test-master',
            'services.paydunya.private_key' => 'test-private',
            'services.paydunya.token' => 'test-token',
        ]);
    }

    private function checkout(string $payment): TestResponse
    {
        $this->post(route('shop.cart.add', $this->product), ['quantity' => 1]);

        return $this->post(route('shop.checkout.store'), [
            'customer_name' => 'Awa Ndiaye',
            'customer_phone' => '+221 77 123 45 67',
            'customer_email' => 'awa@example.com',
            'address' => 'Sacré-Cœur 3',
            'city' => 'Dakar',
            'payment' => $payment,
        ]);
    }

    public function test_paydunya_is_rejected_when_not_configured(): void
    {
        $this->checkout('paydunya')->assertSessionHasErrors('payment');

        $this->assertSame(0, Order::count());
    }

    public function test_checkout_with_paydunya_redirects_to_the_payment_page(): void
    {
        $this->configurePayDunya();

        Http::fake([
            '*/checkout-invoice/create' => Http::response([
                'response_code' => '00',
                'response_text' => 'https://paydunya.com/checkout/invoice/tok_123',
                'token' => 'tok_123',
            ]),
        ]);

        $this->checkout('paydunya')
            ->assertRedirect('https://paydunya.com/checkout/invoice/tok_123');

        $order = Order::firstOrFail();
        $payment = Payment::firstOrFail();

        $this->assertSame(PaymentProvider::PayDunya, $order->payment_provider);
        $this->assertSame(PaymentStatus::Pending, $payment->status);
        $this->assertSame('tok_123', $payment->checkout_token);
        $this->assertEquals(10000, (float) $payment->amount); // livraison non facturée
    }

    public function test_checkout_survives_a_paydunya_outage(): void
    {
        $this->configurePayDunya();

        Http::fake(['*/checkout-invoice/create' => Http::response(null, 500)]);

        $order = null;
        $this->checkout('paydunya');
        $order = Order::firstOrFail();

        // La commande est conservée malgré l'échec du fournisseur
        $this->assertSame(OrderStatus::Pending, $order->status);
        $this->assertSame(0, Payment::count());
    }

    public function test_the_webhook_marks_the_order_as_paid(): void
    {
        $this->configurePayDunya();

        Http::fake([
            '*/checkout-invoice/create' => Http::response([
                'response_code' => '00',
                'response_text' => 'https://paydunya.com/checkout/invoice/tok_123',
                'token' => 'tok_123',
            ]),
            '*/checkout-invoice/confirm/*' => Http::response([
                'status' => 'completed',
                'receipt_url' => 'https://paydunya.com/receipt/abc.pdf',
            ]),
        ]);

        $this->checkout('paydunya');

        // Webhook sans session ni CSRF, comme l'enverrait PayDunya
        $this->flushSession();
        $this->post(route('webhooks.paydunya'), [
            'data' => ['invoice' => ['token' => 'tok_123']],
        ])->assertNoContent();

        $order = Order::firstOrFail();
        $payment = Payment::firstOrFail();

        $this->assertSame(OrderStatus::Paid, $order->status);
        $this->assertSame(PaymentStatus::Paid, $order->payment_status);
        $this->assertSame(PaymentStatus::Paid, $payment->status);
        $this->assertSame('https://paydunya.com/receipt/abc.pdf', $payment->provider_reference);
        $this->assertNotNull($order->paid_at);

        // Idempotence : un second webhook ne change rien
        $this->post(route('webhooks.paydunya'), [
            'data' => ['invoice' => ['token' => 'tok_123']],
        ])->assertNoContent();

        $this->assertSame(1, Payment::count());
        $this->assertSame(OrderStatus::Paid, $order->fresh()->status);
    }

    public function test_the_webhook_ignores_unknown_tokens(): void
    {
        $this->configurePayDunya();

        $this->post(route('webhooks.paydunya'), [
            'data' => ['invoice' => ['token' => 'tok_inconnu']],
        ])->assertNoContent();

        $this->assertSame(0, Payment::count());
    }

    public function test_the_return_url_confirms_the_payment(): void
    {
        $this->configurePayDunya();

        Http::fake([
            '*/checkout-invoice/create' => Http::response([
                'response_code' => '00',
                'response_text' => 'https://paydunya.com/checkout/invoice/tok_123',
                'token' => 'tok_123',
            ]),
            '*/checkout-invoice/confirm/*' => Http::response(['status' => 'completed']),
        ]);

        $this->checkout('paydunya');
        $order = Order::firstOrFail();

        $this->get(route('shop.payment.return', $order))
            ->assertRedirect(route('shop.order.confirmation', $order))
            ->assertSessionHas('success');

        $this->assertSame(OrderStatus::Paid, $order->fresh()->status);
    }

    /**
     * Un « completed » annoncé pour un montant inférieur au total ne doit
     * jamais solder la commande : le fournisseur peut renvoyer une facture
     * réglée partiellement ou modifiée.
     */
    public function test_a_payment_for_the_wrong_amount_does_not_settle_the_order(): void
    {
        $this->configurePayDunya();

        Http::fake([
            '*/checkout-invoice/create' => Http::response([
                'response_code' => '00',
                'response_text' => 'https://paydunya.com/checkout/invoice/tok_123',
                'token' => 'tok_123',
            ]),
            '*/checkout-invoice/confirm/*' => Http::response([
                'status' => 'completed',
                'invoice' => ['total_amount' => 100], // au lieu de 10 000
            ]),
        ]);

        $this->checkout('paydunya');

        $this->flushSession();
        $this->post(route('webhooks.paydunya'), [
            'data' => ['invoice' => ['token' => 'tok_123']],
        ])->assertNoContent();

        $order = Order::firstOrFail();

        $this->assertSame(PaymentStatus::Pending, Payment::firstOrFail()->status);
        $this->assertSame(OrderStatus::Pending, $order->status);
        $this->assertNull($order->paid_at);
    }

    public function test_a_payment_for_the_expected_amount_settles_the_order(): void
    {
        $this->configurePayDunya();

        Http::fake([
            '*/checkout-invoice/create' => Http::response([
                'response_code' => '00',
                'response_text' => 'https://paydunya.com/checkout/invoice/tok_123',
                'token' => 'tok_123',
            ]),
            '*/checkout-invoice/confirm/*' => Http::response([
                'status' => 'completed',
                'invoice' => ['total_amount' => 10000], // livraison non facturée
            ]),
        ]);

        $this->checkout('paydunya');

        $this->flushSession();
        $this->post(route('webhooks.paydunya'), [
            'data' => ['invoice' => ['token' => 'tok_123']],
        ])->assertNoContent();

        $this->assertSame(PaymentStatus::Paid, Payment::firstOrFail()->status);
        $this->assertSame(OrderStatus::Paid, Order::firstOrFail()->status);
    }

    public function test_cash_on_delivery_still_goes_straight_to_confirmation(): void
    {
        $this->checkout('cash_on_delivery');

        $order = Order::firstOrFail();

        $this->assertSame(PaymentProvider::CashOnDelivery, $order->payment_provider);
        $this->assertSame(0, Payment::count());
        $this->get(route('shop.order.confirmation', $order))->assertOk();
    }
}
