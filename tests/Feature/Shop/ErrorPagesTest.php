<?php

namespace Tests\Feature\Shop;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_404_page_is_branded_and_helpful(): void
    {
        $this->get('/page-inexistante')
            ->assertNotFound()
            ->assertSee('cette page n\'existe pas', false)
            ->assertSee('Rechercher un produit', false)
            ->assertSee(route('shop.catalog'), false);
    }

    public function test_an_unknown_product_shows_the_branded_404(): void
    {
        $this->get('/produit/produit-inexistant')
            ->assertNotFound()
            ->assertSee('cette page n\'existe pas', false);
    }
}
