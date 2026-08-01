<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Product;
use App\Services\Cart\CartService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
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
        // HTTPS obligatoire en production (liens, assets, redirections)
        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }

        // Garde-fou N+1 : signale les chargements paresseux dans les logs
        // sans jamais casser la page
        Model::preventLazyLoading();
        Model::handleLazyLoadingViolationUsing(function ($model, string $relation) {
            Log::warning('Lazy loading détecté.', [
                'model' => $model::class,
                'relation' => $relation,
            ]);
        });

        // Le sitemap suit les modifications du catalogue
        foreach ([Product::class, Category::class] as $model) {
            $model::saved(fn () => Cache::forget('sitemap.xml'));
            $model::deleted(fn () => Cache::forget('sitemap.xml'));
        }

        // Catégories du menu, partagées avec le header et le footer publics
        View::composer(['components.shop-layout', 'partials.shop.*'], function ($view) {
            $view->with('navCategories', Cache::remember(
                'nav.categories',
                now()->addHour(),
                fn () => Category::active()->root()->ordered()->get(['id', 'name', 'slug'])
            ));
        });

        // Compteurs panier et favoris, affichés dans le header
        View::composer('partials.shop.header', function ($view) {
            $view->with('cartCount', app(CartService::class)->count());
            $view->with('wishlistCount', app(\App\Services\Wishlist\WishlistService::class)->count());
        });
    }
}
