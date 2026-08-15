<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use App\Services\Images\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(private readonly ImageService $images) {}

    public function index(): View
    {
        return view('admin.categories.index', [
            'categories' => Category::withCount('products')->with('parent:id,name')->ordered()->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.categories.form', [
            'category' => new Category,
            'parents' => Category::root()->ordered()->get(['id', 'name']),
        ]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $this->save($request, new Category);

        return redirect()->route('admin.categories.index')->with('success', 'Catégorie créée.');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.form', [
            'category' => $category,
            'parents' => Category::root()->ordered()->whereKeyNot($category->id)->get(['id', 'name']),
        ]);
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $this->save($request, $category);

        return redirect()->route('admin.categories.index')->with('success', 'Catégorie mise à jour.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->withTrashed()->exists() || $category->children()->exists()) {
            return back()->with('error', 'Impossible de supprimer une catégorie qui contient des produits ou des sous-catégories.');
        }

        $this->images->delete($category->image);
        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Catégorie supprimée.');
    }

    private function save(CategoryRequest $request, Category $category): void
    {
        $data = [
            ...$request->safe()->except(['image', 'is_active']),
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->hasFile('image')) {
            $this->images->delete($category->image);
            $data['image'] = $this->images->store($request->file('image'), 'categories', maxWidth: 800);
        }

        $category->fill($data)->save();
    }
}
