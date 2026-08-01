<?php

namespace Tests\Feature\Shop;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistTest extends TestCase
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

    public function test_a_product_can_be_added_and_removed_from_the_wishlist(): void
    {
        // Ajout
        $this->post(route('shop.wishlist.toggle', $this->product))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->get(route('shop.wishlist'))
            ->assertOk()
            ->assertSee('Chargeur Test 25W');

        // Retrait (toggle)
        $this->post(route('shop.wishlist.toggle', $this->product));

        $this->get(route('shop.wishlist'))
            ->assertOk()
            ->assertDontSee('Chargeur Test 25W');
    }

    public function test_inactive_products_are_purged_from_the_wishlist(): void
    {
        $this->post(route('shop.wishlist.toggle', $this->product));

        $this->product->update(['is_active' => false]);

        $this->get(route('shop.wishlist'))
            ->assertOk()
            ->assertDontSee('Chargeur Test 25W');
    }

    public function test_quick_add_from_a_card_stays_on_the_current_page(): void
    {
        $this->from(route('shop.catalog'))
            ->post(route('shop.cart.add', $this->product), ['quick' => 1])
            ->assertRedirect(route('shop.catalog'))
            ->assertSessionHas('success');
    }
}
