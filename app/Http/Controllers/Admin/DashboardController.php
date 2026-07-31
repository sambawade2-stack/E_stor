<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $revenueQuery = Order::where('payment_status', PaymentStatus::Paid)
            ->where('status', '!=', OrderStatus::Cancelled);

        // Ventes des 12 derniers mois (commandes payées)
        $monthlySales = (clone $revenueQuery)
            ->where('created_at', '>=', now()->startOfMonth()->subMonths(11))
            ->get(['total', 'created_at'])
            ->groupBy(fn (Order $order) => $order->created_at->format('Y-m'))
            ->map(fn ($orders) => (float) $orders->sum('total'));

        $months = collect(range(11, 0))
            ->map(fn (int $i) => now()->startOfMonth()->subMonths($i))
            ->mapWithKeys(fn ($month) => [
                $month->translatedFormat('M y') => $monthlySales[$month->format('Y-m')] ?? 0.0,
            ]);

        return view('admin.dashboard', [
            'revenue' => (clone $revenueQuery)->sum('total'),
            'revenueThisMonth' => (clone $revenueQuery)->where('created_at', '>=', now()->startOfMonth())->sum('total'),
            'ordersCount' => Order::count(),
            'pendingOrdersCount' => Order::where('status', OrderStatus::Pending)->count(),
            'customersCount' => User::whereHas('orders')->count() + Order::whereNull('user_id')->distinct('customer_phone')->count('customer_phone'),
            'productsCount' => Product::count(),
            'monthlySales' => $months,
            'topProducts' => OrderItem::query()
                ->select('product_id', 'product_name', DB::raw('SUM(quantity) as total_sold'), DB::raw('SUM(total) as total_revenue'))
                ->whereHas('order', fn ($q) => $q->where('status', '!=', OrderStatus::Cancelled))
                ->whereNotNull('product_id')
                ->groupBy('product_id', 'product_name')
                ->orderByDesc('total_sold')
                ->take(5)
                ->get(),
            'lowStockProducts' => Product::active()
                ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                ->orderBy('stock_quantity')
                ->take(5)
                ->get(['id', 'name', 'slug', 'stock_quantity']),
            'recentOrders' => Order::withCount('items')->latest()->take(8)->get(),
        ]);
    }
}
