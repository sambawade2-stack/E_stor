<?php

namespace Tests\Feature\Shop;

use App\Models\Category;
use App\Models\Product;
use App\Models\ShippingZone;
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

        ShippingZone::create(['name' => 'Dakar', 'cost' => 2000]);
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
