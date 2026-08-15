<?php

namespace Tests\Feature\Shop;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
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
            'short_description' => 'Un chargeur de test.',
            'price' => 10000,
            'stock_quantity' => 5,
            'is_active' => true,
        ]);

    }

    /**
     * Les images doivent être servies en URL relative. Une URL absolue figée
     * sur APP_URL casse tout l'affichage dès que le site est consulté depuis
     * une autre origine (127.0.0.1 au lieu de localhost, autre port, IP du
     * réseau local…) et se heurte à la CSP `img-src 'self'` en production.
     */
    public function test_product_images_are_served_with_relative_urls(): void
    {
        $image = $this->product->images()->create([
            'path' => 'products/test.webp',
            'alt' => 'Chargeur',
            'is_primary' => true,
        ]);

        $this->assertSame('/storage/products/test.webp', $image->url());

        $html = $this->get(route('shop.product', $this->product))->getContent();

        $this->assertStringContainsString('/storage/products/test.webp', $html);

        // Les balises <img> affichées doivent pointer en relatif. Les URLs
        // absolues restent légitimes dans og:image et le JSON-LD, d'où le
        // ciblage explicite de l'attribut src.
        $this->assertDoesNotMatchRegularExpression(
            '#src="https?://[^"]*/storage/#',
            $html,
            'Les images affichées ne doivent pas être liées en absolu sur APP_URL.'
        );
    }

    /**
     * Open Graph et JSON-LD, eux, exigent des URLs absolues : les réseaux
     * sociaux et les moteurs ne résolvent pas un chemin relatif.
     */
    public function test_social_and_structured_data_images_stay_absolute(): void
    {
        $this->product->images()->create([
            'path' => 'products/test.webp',
            'alt' => 'Chargeur',
            'is_primary' => true,
        ]);

        $html = $this->get(route('shop.product', $this->product))->getContent();

        $this->assertMatchesRegularExpression(
            '#<meta property="og:image" content="https?://[^"]+/storage/products/test\.webp">#',
            $html
        );
        $this->assertStringContainsString(
            url('/storage/products/test.webp'),
            $html
        );
    }

    public function test_the_sitemap_lists_public_pages_and_products(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee(route('shop.catalog'), false)
            ->assertSee(route('shop.product', $this->product), false)
            ->assertSee(route('shop.category', $this->product->category), false);
    }

    public function test_pages_carry_canonical_and_opengraph_tags(): void
    {
        $this->get(route('shop.product', $this->product))
            ->assertOk()
            ->assertSee('<link rel="canonical"', false)
            ->assertSee('property="og:title"', false)
            ->assertSee('property="og:type" content="product"', false)
            ->assertSee('name="twitter:card"', false);
    }

    public function test_the_product_page_exposes_json_ld_structured_data(): void
    {
        $this->get(route('shop.product', $this->product))
            ->assertOk()
            ->assertSee('application/ld+json', false)
            ->assertSee('"@type":"Product"', false)
            ->assertSee('"sku":"TEST-25W"', false)
            ->assertSee('schema.org/InStock', false);
    }

    public function test_the_home_page_exposes_organization_json_ld(): void
    {
        $this->get(route('shop.home'))
            ->assertOk()
            ->assertSee('"@type":"OnlineStore"', false);
    }

    public function test_responses_include_security_headers(): void
    {
        $this->get(route('shop.home'))
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }
}
