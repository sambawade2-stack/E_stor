<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);

        $product->load([
            'images',
            'category:id,name,slug',
            'brand:id,name,slug',
            'approvedReviews' => fn ($q) => $q->latest()->take(10),
        ]);

        $product->loadAvg('approvedReviews as rating', 'rating');
        $product->loadCount('approvedReviews as reviews_count');

        // Compteur de popularité (sans toucher updated_at)
        Product::withoutTimestamps(fn () => $product->increment('views_count'));

        $similar = Product::active()
            ->where('category_id', $product->category_id)
            ->whereKeyNot($product->id)
            ->with(['primaryImage', 'category:id,name,slug'])
            ->withAvg('approvedReviews as rating', 'rating')
            ->inRandomOrder()
            ->take(4)
            ->get();

        return view('shop.product', compact('product', 'similar'));
    }

    /**
     * Fragment HTML pour la modale « Aperçu rapide » ouverte depuis une
     * carte produit (fetch Alpine.js) — pas de layout, juste le contenu.
     */
    public function quickView(Product $product): View
    {
        abort_unless($product->is_active, 404);

        $product->load(['images', 'category:id,name,slug']);
        $product->loadAvg('approvedReviews as rating', 'rating');
        $product->loadCount('approvedReviews as reviews_count');

        return view('shop.partials.quick-view', compact('product'));
    }
}
