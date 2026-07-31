<?php

namespace App\Http\Controllers\Account;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $orders = $user->orders();

        return view('account.dashboard', [
            'ordersCount' => (clone $orders)->count(),
            'pendingCount' => (clone $orders)->whereNotIn('status', [OrderStatus::Delivered, OrderStatus::Cancelled])->count(),
            'totalSpent' => (clone $orders)->where('status', '!=', OrderStatus::Cancelled)->sum('total'),
            'recentOrders' => $user->orders()->withCount('items')->latest()->take(5)->get(),
        ]);
    }
}
