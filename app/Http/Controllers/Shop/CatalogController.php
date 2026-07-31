<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    /**
     * Catalogue complet, avec recherche (?q=), filtres et tri.
     */
    public function index(Request $request): View
    {
        return $this->renderCatalog($request, title: 'Catalogue');
    }

    /**
     * Produits d'une catégorie.
     */
    public function category(Request $request, Category $category): View
    {
        abort_unless($category->is_active, 404);

        return $this->renderCatalog(
            $request,
            title: $category->name,
            category: $category,
        );
    }

    /**
     * Produits actuellement en promotion.
     */
    public function promotions(Request $request): View
    {
        return $this->renderCatalog(
            $request,
            title: 'Promotions',
            onSaleOnly: true,
        );
    }

    private function renderCatalog(
        Request $request,
        string $title,
        ?Category $category = null,
        bool $onSaleOnly = false,
    ): View {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'brand' => ['nullable', 'string', 'exists:brands,slug'],
            'category' => ['nullable', 'string', 'exists:categories,slug'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'sort' => ['nullable', 'in:recent,price_asc,price_desc,popular'],
            'in_stock' => ['nullable', 'boolean'],
        ]);

        $products = Product::query()
            ->active()
            ->with(['primaryImage', 'category:id,name,slug', 'brand:id,name,slug'])
            ->withAvg('approvedReviews as rating', 'rating')
            ->when($category, fn (Builder $q) => $q->where('category_id', $category->id))
            ->when(! $category && ! empty($validated['category']),
                fn (Builder $q) => $q->whereHas('category', fn ($c) => $c->where('slug', $validated['category'])))
            ->when($onSaleOnly, fn (Builder $q) => $q->onSale())
            ->when(! empty($validated['q']), fn (Builder $q) => $q->search($validated['q']))
            ->when(! empty($validated['brand']),
                fn (Builder $q) => $q->whereHas('brand', fn ($b) => $b->where('slug', $validated['brand'])))
            ->when(isset($validated['min_price']), fn (Builder $q) => $q->where('price', '>=', $validated['min_price']))
            ->when(isset($validated['max_price']), fn (Builder $q) => $q->where('price', '<=', $validated['max_price']))
            ->when($request->boolean('in_stock'), fn (Builder $q) => $q->inStock());

        $products = match ($validated['sort'] ?? 'recent') {
            'price_asc' => $products->orderBy('price'),
            'price_desc' => $products->orderByDesc('price'),
            'popular' => $products->orderByDesc('views_count'),
            default => $products->latest(),
        };

        return view('shop.catalog', [
            'title' => $title,
            'category' => $category,
            'onSaleOnly' => $onSaleOnly,
            'products' => $products->paginate(12)->withQueryString(),
            'categories' => Category::active()->root()->ordered()->get(['id', 'name', 'slug']),
            'brands' => Brand::active()->orderBy('name')->get(['id', 'name', 'slug']),
        ]);
    }
}
