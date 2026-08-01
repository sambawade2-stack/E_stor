<?php

namespace App\Services\Wishlist;

use App\Models\Product;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Collection;

/**
 * Liste de favoris stockée en session (cohérente avec le panier invité).
 */
class WishlistService
{
    private const KEY = 'wishlist.items';

    public function __construct(private readonly Session $session)
    {
    }

    /**
     * Ajoute ou retire un produit. Retourne true s'il a été ajouté.
     */
    public function toggle(Product $product): bool
    {
        $ids = $this->ids();

        if (in_array($product->id, $ids, true)) {
            $ids = array_values(array_diff($ids, [$product->id]));
            $added = false;
        } else {
            $ids[] = $product->id;
            $added = true;
        }

        $this->session->put(self::KEY, $ids);

        return $added;
    }

    public function has(int $productId): bool
    {
        return in_array($productId, $this->ids(), true);
    }

    public function count(): int
    {
        return count($this->ids());
    }

    /**
     * @return Collection<int, Product>
     */
    public function products(): Collection
    {
        $ids = $this->ids();

        if ($ids === []) {
            return collect();
        }

        $products = Product::active()
            ->with(['primaryImage', 'category:id,name,slug'])
            ->withAvg('approvedReviews as rating', 'rating')
            ->findMany($ids);

        // Purge les produits devenus indisponibles
        $this->session->put(self::KEY, $products->pluck('id')->all());

        return $products;
    }

    /**
     * @return array<int, int>
     */
    private function ids(): array
    {
        return $this->session->get(self::KEY, []);
    }
}
