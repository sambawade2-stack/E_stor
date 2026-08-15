<?php

namespace Tests\Feature\Security;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Password;
use Spatie\Permission\Models\Permission;
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
        Setting::set('shop_name', 'ES</script><script>alert(1)</script>');

        $html = $this->get(route('shop.home'))->getContent();

        $this->assertStringNotContainsString('</script><script>alert', $html);
    }

    /**
     * La connexion est la porte d'entrée restante : les clients n'ayant plus
     * de compte, c'est le seul formulaire d'authentification exposé. Il doit
     * résister au bourrinage de mots de passe.
     */
    public function test_login_is_rate_limited(): void
    {
        $attempt = fn () => $this->post('/login', [
            'email' => 'flood@example.com',
            'password' => 'mauvais-mot-de-passe',
        ]);

        for ($i = 0; $i < 5; $i++) {
            $attempt()->assertStatus(302);
        }

        $attempt()->assertSessionHasErrors('email');
        $this->assertGuest();
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

    /**
     * Fixation de session : l'identifiant doit changer à la connexion, sinon
     * un identifiant imposé avant l'authentification resterait valable après.
     */
    public function test_login_regenerates_the_session_id(): void
    {
        $user = User::factory()->create();

        $this->get('/login');
        $avant = $this->app['session']->getId();

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $this->assertNotSame($avant, $this->app['session']->getId());
    }

    /**
     * La règle de mot de passe s'applique toujours — non plus à l'inscription,
     * supprimée, mais à la réinitialisation, seule voie pour en définir un.
     */
    public function test_weak_passwords_are_rejected_when_resetting(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'alllowercase',
            'password_confirmation' => 'alllowercase',
        ])->assertSessionHasErrors('password');
    }

    public function test_admin_routes_are_protected_by_role_middleware(): void
    {
        $customer = User::factory()->create();
        $customer->assignRole('customer');

        $this->actingAs($customer)->get(route('admin.dashboard'))->assertForbidden();
    }

    /**
     * Le SVG n'est pas décodable par le driver GD (erreur 500 à l'upload) et
     * serait, s'il était stocké tel quel, un vecteur de XSS stocké.
     */
    public function test_an_svg_logo_is_rejected(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::firstOrCreate(['name' => 'admin']));
        Permission::firstOrCreate(['name' => 'manage settings']);
        $admin->givePermissionTo('manage settings');

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10">'
            .'<script>alert(document.cookie)</script></svg>';

        $this->actingAs($admin)
            ->patch(route('admin.settings.update'), [
                'shop_name' => 'ES',
                'shop_email' => 'contact@es.test',
                'shop_phone' => '770000000',
                'whatsapp_number' => '221770000000',
                'currency_symbol' => 'FCFA',
                'logo' => UploadedFile::fake()->createWithContent('logo.svg', $svg),
            ])
            ->assertSessionHasErrors('logo');

        $this->assertNull(Setting::get('logo_path'));
    }

    /**
     * Sans le flag Secure, le cookie de session part en clair sur une
     * première requête HTTP et peut être intercepté.
     */
    public function test_the_session_cookie_is_secure_in_production(): void
    {
        $original = $_SERVER['APP_ENV'] ?? null;

        try {
            $_SERVER['APP_ENV'] = 'production';
            $this->assertTrue(
                (require base_path('config/session.php'))['secure'],
                'Le flag Secure doit être actif par défaut en production.'
            );

            $_SERVER['APP_ENV'] = 'local';
            $this->assertFalse(
                (require base_path('config/session.php'))['secure'],
                'En local (HTTP), forcer Secure empêcherait toute session.'
            );
        } finally {
            if ($original === null) {
                unset($_SERVER['APP_ENV']);
            } else {
                $_SERVER['APP_ENV'] = $original;
            }
        }
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
