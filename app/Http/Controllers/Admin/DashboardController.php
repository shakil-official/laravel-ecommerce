<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Subcategory;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    /** Dashboard: counters, revenue (paid orders only), latest orders / products. */
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'stats' => [
                'categories' => Category::count(),
                'subcategories' => Subcategory::count(),
                'products' => Product::count(),
            ],
            'orderStats' => [
                'orders' => Order::count(),
                'pending' => Order::where('status', OrderStatus::Pending)->count(),
                'revenue' => (float) Order::where('payment_status', PaymentStatus::Paid)->sum('total'),
            ],
            'latestProducts' => Product::with(['category', 'subcategory'])->latest('id')->limit(5)->get(),
            'latestOrders' => Order::latest('id')->limit(5)->get(),
            'categories' => Category::withCount(['subcategories', 'products'])->orderBy('name')->limit(8)->get(),
        ]);
    }
}
