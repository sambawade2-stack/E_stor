<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\Images\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(private readonly ImageService $images)
    {
    }

    public function index(Request $request): View
    {
        $products = Product::query()
            ->with(['category:id,name', 'brand:id,name', 'primaryImage'])
            ->when($request->filled('q'), fn ($q) => $q->search($request->string('q')))
            ->when($request->filled('category'), fn ($q) => $q->where('category_id', $request->integer('category')))
            ->when($request->input('state') === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($request->input('state') === 'low_stock', fn ($q) => $q->whereColumn('stock_quantity', '<=', 'low_stock_threshold'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'categories' => Category::ordered()->get(['id', 'name']),
        ]);
    }

    public function create(): View
    {
        return view('admin.products.form', [
            'product' => new Product,
            'categories' => Category::ordered()->get(['id', 'name']),
            'brands' => Brand::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $product = Product::create($request->productData());

        $this->storeImages($request, $product);

        return redirect()->route('admin.products.index')
            ->with('success', 'Produit créé.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.form', [
            'product' => $product->load('images'),
            'categories' => Category::ordered()->get(['id', 'name']),
            'brands' => Brand::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $product->update($request->productData());

        $this->storeImages($request, $product);

        return redirect()->route('admin.products.index')
            ->with('success', 'Produit mis à jour.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        // Soft delete : le produit reste lié aux anciennes commandes
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Produit supprimé.');
    }

    public function destroyImage(Product $product, ProductImage $image): RedirectResponse
    {
        abort_unless($image->product_id === $product->id, 404);

        $this->images->delete($image->path);
        $image->delete();

        return back()->with('success', 'Image supprimée.');
    }

    public function makePrimaryImage(Product $product, ProductImage $image): RedirectResponse
    {
        abort_unless($image->product_id === $product->id, 404);

        $product->images()->update(['is_primary' => false]);
        $image->update(['is_primary' => true]);

        return back()->with('success', 'Image principale définie.');
    }

    private function storeImages(ProductRequest $request, Product $product): void
    {
        foreach ($request->file('images', []) as $file) {
            $product->images()->create([
                'path' => $this->images->store($file, 'products'),
                'alt' => $product->name,
                'is_primary' => ! $product->images()->where('is_primary', true)->exists(),
                'sort_order' => ($product->images()->max('sort_order') ?? 0) + 1,
            ]);
        }
    }
}
