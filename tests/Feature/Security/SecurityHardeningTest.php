<?php

namespace Tests\Feature\Security;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'customer']);
    }

    /**
     * Un nom de produit contenant "</script>" ne doit jamais pouvoir casser
     * la balise JSON-LD et injecter du script arbitraire (XSS stocké).
     */
    public function test_product_json_ld_escapes_script_breakout_attempts(): void
    {
        $category = Category::create(['name' => 'Chargeurs']);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Chargeur</script><script>alert(document.cookie)</script>',
            'sku' => 'ES-XSS-TEST',
            'price' => 5000,
            'stock_quantity' => 1,
            'is_active' => true,
        ]);

        $html = $this->get(route('shop.product', $product))->getContent();

        $this->assertStringNotContainsString('</script><script>alert', $html);
        $this->assertStringContainsString('</script>', $html);
    }

    /**
     * Même vérification sur le JSON-LD de l'accueil (paramètres boutique).
     */
    public function test_home_json_ld_escapes_script_breakout_attempts(): void
    {
        \App\Models\Setting::set('shop_name', 'ES</script><script>alert(1)</script>');

        $html = $this->get(route('shop.home'))->getContent();

        $this->assertStringNotContainsString('</script><script>alert', $html);
    }

    public function test_registration_is_rate_limited(): void
    {
        // Données volontairement invalides (mots de passe non concordants) :
        // l'inscription échoue à chaque fois, l'utilisateur reste invité,
        // ce qui permet de vérifier le throttle sans jamais déclencher la
        // connexion automatique (qui ferait sortir des routes "guest").
        $attempt = fn () => $this->post(route('register'), [
            'name' => 'Test',
            'email' => 'flood@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'not-the-same',
        ]);

        for ($i = 0; $i < 5; $i++) {
            $attempt()->assertStatus(302)->assertSessionHasErrors('password');
        }

        $attempt()->assertStatus(429);
    }

    public function test_password_reset_request_is_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/forgot-password', ['email' => 'someone@example.com'])
                ->assertStatus(302);
        }

        $this->post('/forgot-password', ['email' => 'someone@example.com'])
            ->assertStatus(429);
    }

    public function test_registration_regenerates_the_session_id(): void
    {
        $this->get('/register');
        $originalSessionId = $this->app['session']->getId();

        $this->post(route('register'), [
            'name' => 'Awa Ndiaye',
            'email' => 'awa-session@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ]);

        $this->assertNotSame($originalSessionId, $this->app['session']->getId());
    }

    public function test_weak_passwords_are_rejected_at_registration(): void
    {
        $this->post(route('register'), [
            'name' => 'Test',
            'email' => 'weak@example.com',
            'password' => 'alllowercase',
            'password_confirmation' => 'alllowercase',
        ])->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('users', ['email' => 'weak@example.com']);
    }

    public function test_admin_routes_are_protected_by_role_middleware(): void
    {
        $customer = User::factory()->create();
        $customer->assignRole('customer');

        $this->actingAs($customer)->get(route('admin.dashboard'))->assertForbidden();
    }

    /**
     * La CSP n'est appliquée qu'en production : en local/CI, le serveur de
     * dev Vite charge son client HMR depuis une autre origine, une CSP
     * stricte casserait le rechargement à chaud.
     */
    public function test_csp_header_is_only_sent_in_production(): void
    {
        $this->get(route('shop.home'))->assertHeaderMissing('Content-Security-Policy');

        $this->app['env'] = 'production';

        $response = $this->get(route('shop.home'))->assertHeader('Content-Security-Policy');

        $this->assertStringContainsString("object-src 'none'", $response->headers->get('Content-Security-Policy'));
    }
}
