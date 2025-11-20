<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Optional helper view for dashboard index. Consider moving this to a
     * dedicated DashboardController if you need more dashboard actions.
     */
    public function index_dashboard()
    {
        // Gather product stats for dashboard widgets
        try {
            $totalProducts = \App\Models\Product::count();
            $inStock = \App\Models\Product::where('stock', '>', 0)->count();
            $outOfStock = \App\Models\Product::where('stock', '<=', 0)->count();

            // Get categories with product counts and some products ordered by name
            $categories = \App\Models\Category::withCount('products')
                ->with(['products' => function($q){ $q->select('id','name','category_id')->orderBy('name'); }])
                ->orderBy('name')
                ->get();

            // Orders summary
            $totalOrders = \App\Models\Order::count();
            $recentOrders = \App\Models\Order::orderBy('created_at', 'desc')->limit(5)->get();
        } catch (\Throwable $e) {
            $totalProducts = $inStock = $outOfStock = 0;
            $categories = collect();
            $totalOrders = 0;
            $recentOrders = collect();
        }

        return view('settings.dashboard.index', compact('totalProducts', 'inStock', 'outOfStock', 'categories', 'totalOrders', 'recentOrders'));
    }
}
