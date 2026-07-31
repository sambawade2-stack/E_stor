<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Page d'accueil : toutes les sections sont mises en cache 10 minutes.
     */
    public function index(): View
    {
        $data = Cache::remember('home.sections', now()->addMinutes(10), function () {
            $withCard = fn ($query) => $query->active()
                ->with(['primaryImage', 'category:id,name,slug'])
                ->withAvg('approvedReviews as rating', 'rating');

            return [
                'categories' => Category::active()->root()->ordered()
                    ->withCount(['products' => fn ($q) => $q->active()])
                    ->get(),
                'featured' => $withCard(Product::query())->featured()->latest()->take(8)->get(),
                'latest' => $withCard(Product::query())->latest()->take(8)->get(),
                'popular' => $withCard(Product::query())->orderByDesc('views_count')->take(8)->get(),
                'onSale' => $withCard(Product::query())->onSale()->take(4)->get(),
                'brands' => Brand::active()->orderBy('name')->get(),
                'reviews' => Review::approved()->with('product:id,name,slug')->latest()->take(3)->get(),
            ];
        });

        return view('shop.home', $data);
    }
}
