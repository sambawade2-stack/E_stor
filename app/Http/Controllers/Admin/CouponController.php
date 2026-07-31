<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CouponRequest;
use App\Models\Coupon;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CouponController extends Controller
{
    public function index(): View
    {
        return view('admin.coupons.index', [
            'coupons' => Coupon::withCount('orders')->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.coupons.form', ['coupon' => new Coupon]);
    }

    public function store(CouponRequest $request): RedirectResponse
    {
        Coupon::create($this->data($request));

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon créé.');
    }

    public function edit(Coupon $coupon): View
    {
        return view('admin.coupons.form', compact('coupon'));
    }

    public function update(CouponRequest $request, Coupon $coupon): RedirectResponse
    {
        $coupon->update($this->data($request));

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon mis à jour.');
    }

    public function destroy(Coupon $coupon): RedirectResponse
    {
        $coupon->delete();

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon supprimé.');
    }

    /**
     * @return array<string, mixed>
     */
    private function data(CouponRequest $request): array
    {
        return [
            ...$request->safe()->except('is_active'),
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
