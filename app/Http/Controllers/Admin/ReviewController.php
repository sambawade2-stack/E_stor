<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.reviews.index', [
            'reviews' => Review::with('product:id,name,slug')
                ->when($request->input('state') === 'pending', fn ($q) => $q->where('is_approved', false))
                ->latest()
                ->paginate(15)
                ->withQueryString(),
            'pendingCount' => Review::where('is_approved', false)->count(),
        ]);
    }

    public function approve(Review $review): RedirectResponse
    {
        $review->update(['is_approved' => ! $review->is_approved]);

        return back()->with('success', $review->is_approved ? 'Avis approuvé.' : 'Avis masqué.');
    }

    public function destroy(Review $review): RedirectResponse
    {
        $review->delete();

        return back()->with('success', 'Avis supprimé.');
    }
}
