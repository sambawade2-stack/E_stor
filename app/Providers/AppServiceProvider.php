<?php

namespace App\Providers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Review;
use App\Services\Cart\CartService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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

        // Règle de mot de passe par défaut (inscription, changement, réinitialisation) :
        // au moins 8 caractères, majuscule + minuscule + chiffre. En production,
        // rejette en plus les mots de passe connus comme compromis (API Have I Been
        // Pwned, k-anonymat — aucun mot de passe en clair n'est transmis).
        // Désactivé hors production pour ne pas dépendre d'un accès réseau en local/CI.
        Password::defaults(function () {
            $rule = Password::min(8)->letters()->mixedCase()->numbers();

            return $this->app->isProduction() ? $rule->uncompromised() : $rule;
        });

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

        // L'accueil (sections mises en cache 10 min) suit tout ce qu'il affiche :
        // produits, images, catégories, marques, avis. Sans ça, un produit ou
        // une image ajoutée depuis l'admin peut sembler « ne pas s'afficher »
        // pendant jusqu'à 10 minutes.
        foreach ([Product::class, ProductImage::class, Category::class, Brand::class, Review::class] as $model) {
            $model::saved(fn () => Cache::forget('home.sections'));
            $model::deleted(fn () => Cache::forget('home.sections'));
        }

        // Menu des catégories (header/footer), même logique
        Category::saved(fn () => Cache::forget('nav.categories'));
        Category::deleted(fn () => Cache::forget('nav.categories'));

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
