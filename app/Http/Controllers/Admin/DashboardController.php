<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $stats = [
            'totalOrders' => Order::count(),
            'totalMedicines' => Medicine::count(),
            'totalUsers' => User::count(),
        ];

        $recentOrders = Order::query()
            ->with(['user:id,name,email'])
            ->latest()
            ->take(5)
            ->get();

        $lowStockMedicines = Medicine::query()
            ->orderBy('stock_quantity')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentOrders', 'lowStockMedicines'));
    }
}
