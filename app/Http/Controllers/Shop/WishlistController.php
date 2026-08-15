<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Wishlist\WishlistService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function __construct(private readonly WishlistService $wishlist) {}

    public function index(): View
    {
        return view('shop.wishlist', [
            'products' => $this->wishlist->products(),
        ]);
    }

    public function toggle(Product $product): RedirectResponse
    {
        abort_unless($product->is_active, 404);

        $added = $this->wishlist->toggle($product);

        return back()->with(
            'success',
            $added ? 'Ajouté à vos favoris ❤' : 'Retiré de vos favoris.'
        );
    }
}
