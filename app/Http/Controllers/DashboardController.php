<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $todaySales = Sale::where('status', 'completed')->whereDate('sold_at', today())->sum('total');
        $monthSales = Sale::where('status', 'completed')->whereMonth('sold_at', now()->month)->whereYear('sold_at', now()->year)->sum('total');

        return view('dashboard', [
            'productCount' => Product::count(),
            'categoryCount' => Category::count(),
            'supplierCount' => Supplier::count(),
            'transactionCount' => Purchase::count() + Sale::count(),
            'lowStockCount' => Product::whereColumn('stock', '<=', 'minimum_stock')->count(),
            'todaySales' => $todaySales,
            'monthSales' => $monthSales,
            'lowStock' => Product::with('category')->whereColumn('stock', '<=', 'minimum_stock')->orderBy('stock')->limit(8)->get(),
            'recentSales' => Sale::with('user')->latest('sold_at')->limit(5)->get(),
            'recentPurchases' => Purchase::with(['user', 'supplier'])->latest('purchased_at')->limit(5)->get(),
            'monthlyReport' => Sale::query()
                ->selectRaw('DATE(sold_at) as day, SUM(total) as total, COUNT(*) as transactions')
                ->where('status', 'completed')->where('sold_at', '>=', now()->subDays(6)->startOfDay())
                ->groupBy(DB::raw('DATE(sold_at)'))->orderBy('day')->get(),
        ]);
    }
}
