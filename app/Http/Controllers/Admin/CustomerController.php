<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $customers = User::query()
            ->withCount('orders')
            ->withSum(['orders as total_spent' => fn ($q) => $q->where('status', '!=', OrderStatus::Cancelled)], 'total')
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'admin'))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->where(fn ($sub) => $sub
                    ->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%"));
            })
            ->orderByDesc('orders_count')
            ->paginate(15)
            ->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    public function show(User $customer): View
    {
        abort_if($customer->isAdmin(), 404);

        $customer->loadCount('orders');
        $customer->loadSum(['orders as total_spent' => fn ($q) => $q->where('status', '!=', OrderStatus::Cancelled)], 'total');

        return view('admin.customers.show', [
            'customer' => $customer,
            'orders' => $customer->orders()->withCount('items')->latest()->paginate(10),
        ]);
    }
}
