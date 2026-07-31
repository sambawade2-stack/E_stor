<?php

namespace App\Providers;

use App\Models\Category;
use App\Services\Cart\CartService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Catégories du menu, partagées avec le header et le footer publics
        View::composer(['components.shop-layout', 'partials.shop.*'], function ($view) {
            $view->with('navCategories', Cache::remember(
                'nav.categories',
                now()->addHour(),
                fn () => Category::active()->root()->ordered()->get(['id', 'name', 'slug'])
            ));
        });

        // Compteur d'articles du panier, affiché dans le header
        View::composer('partials.shop.header', function ($view) {
            $view->with('cartCount', app(CartService::class)->count());
        });
    }
}
