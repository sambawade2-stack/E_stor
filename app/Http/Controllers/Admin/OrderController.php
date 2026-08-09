<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = Order::query()
            ->withCount('items')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->where(fn ($sub) => $sub
                    ->where('order_number', 'like', "%{$term}%")
                    ->orWhere('customer_name', 'like', "%{$term}%")
                    ->orWhere('customer_phone', 'like', "%{$term}%"));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'statuses' => OrderStatus::cases(),
        ]);
    }

    public function show(Order $order): View
    {
        $order->load(['items.product.primaryImage', 'user', 'payments']);

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', new Enum(OrderStatus::class)],
        ]);

        try {
            $order->transitionTo(OrderStatus::from($validated['status']));
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Statut mis à jour : '.$order->status->label().'.');
    }

    public function invoice(Order $order): View
    {
        $order->load('items.product.primaryImage');

        return view('admin.orders.invoice', compact('order'));
    }
}
