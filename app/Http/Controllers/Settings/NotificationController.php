<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        // Gather pending Yape orders and low stock products
        $pendingYape = collect();
        $lowStock = collect();

        // Prefer orders.payment_method if present, otherwise check payments table
        try {
            if (class_exists(\App\Models\Order::class) && \Illuminate\Support\Facades\Schema::hasTable('orders')) {
                if (\Illuminate\Support\Facades\Schema::hasColumn('orders', 'payment_method') && \Illuminate\Support\Facades\Schema::hasColumn('orders', 'status')) {
                    $pendingYape = \App\Models\Order::where('payment_method', 'yape')->whereIn('status', ['pendiente', 'pending'])->orderBy('created_at', 'desc')->get();
                } elseif (\Illuminate\Support\Facades\Schema::hasTable('payments') && \Illuminate\Support\Facades\Schema::hasColumn('payments', 'method') && \Illuminate\Support\Facades\Schema::hasColumn('payments', 'status')) {
                    // Orders may not store payment_method; find orders that have a related payment with method=yape and status in both Spanish/English variants
                    $pendingYape = \App\Models\Order::whereHas('payments', function ($q) {
                        $q->where('method', 'yape');
                    })->whereIn('status', ['pendiente', 'pending'])->orderBy('created_at', 'desc')->get();
                }
            }

            if (class_exists(\App\Models\Product::class) && \Illuminate\Support\Facades\Schema::hasTable('products') && \Illuminate\Support\Facades\Schema::hasColumn('products', 'stock')) {
                $lowStock = \App\Models\Product::where('stock', '<=', 5)->orderBy('stock', 'asc')->get();
            }
        } catch (\Throwable $e) {
            // On any error, return empty collections (view expects collections)
            $pendingYape = collect();
            $lowStock = collect();
        }

        return view('admin.notifications.index', compact('pendingYape', 'lowStock'));
    }
}
