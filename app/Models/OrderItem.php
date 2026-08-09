<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'sku',
        'unit_price',
        'quantity',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'quantity' => 'integer',
            'total' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    /**
     * Vignette de l'article, avec repli sur l'image générique.
     *
     * La ligne de commande fige le nom, le SKU et le prix, mais pas l'image :
     * on affiche donc celle du produit tel qu'il est aujourd'hui. Si le
     * produit a été supprimé, ou n'a jamais eu de photo, on retombe sur le
     * visuel par défaut plutôt que sur une image cassée.
     *
     * Suppose la relation product.primaryImage déjà chargée (preventLazyLoading).
     */
    public function imageUrl(): string
    {
        return $this->product?->primaryImage?->url()
            ?? asset('images/placeholder-product.svg');
    }
}
