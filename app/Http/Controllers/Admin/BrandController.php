<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BrandRequest;
use App\Models\Brand;
use App\Services\Images\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BrandController extends Controller
{
    public function __construct(private readonly ImageService $images)
    {
    }

    public function index(): View
    {
        return view('admin.brands.index', [
            'brands' => Brand::withCount('products')->orderBy('name')->get(),
        ]);
    }

    public function store(BrandRequest $request): RedirectResponse
    {
        $this->save($request, new Brand);

        return back()->with('success', 'Marque créée.');
    }

    public function update(BrandRequest $request, Brand $brand): RedirectResponse
    {
        $this->save($request, $brand);

        return back()->with('success', 'Marque mise à jour.');
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        if ($brand->products()->withTrashed()->exists()) {
            return back()->with('error', 'Impossible de supprimer une marque liée à des produits.');
        }

        $this->images->delete($brand->logo);
        $brand->delete();

        return back()->with('success', 'Marque supprimée.');
    }

    private function save(BrandRequest $request, Brand $brand): void
    {
        $data = [
            'name' => $request->validated('name'),
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->hasFile('logo')) {
            $this->images->delete($brand->logo);
            $data['logo'] = $this->images->store($request->file('logo'), 'brands', maxWidth: 400);
        }

        $brand->fill($data)->save();
    }
}
