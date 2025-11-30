<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Optional helper view for dashboard index. Consider moving this to a
     * dedicated DashboardController if you need more dashboard actions.
     */
    public function index_dashboard(\Illuminate\Http\Request $request)
    {
        // Debug log: capture request and auth when client filter is used (use client_* params).
        try {
            if ($request->query('client_id') || $request->query('client_name')) {
                Log::info('Dashboard filter request', [
                    'client_id' => $request->query('client_id'),
                    'client_name' => $request->query('client_name'),
                    'auth_id' => Auth::id(),
                    'session_id' => session()->getId(),
                    'cookies' => $request->cookies->all(),
                    'all_query' => $request->query(),
                ]);
            }
        } catch (\Throwable $e) {
            // ignore logging errors
        }
        // Gather product stats for dashboard widgets
        try {
            $totalProducts = \App\Models\Product::count();
            $inStock = \App\Models\Product::where('stock', '>', 0)->count();
            $outOfStock = \App\Models\Product::where('stock', '<=', 0)->count();

            // Get categories with product counts and a few products (include stock).
            // Order categories by products_count desc so busy categories appear first.
            $categories = \App\Models\Category::withCount('products')
                ->with(['products' => function ($q) {
                    $q->select('id', 'name', 'category_id', 'stock')->orderBy('stock', 'desc')->limit(6);
                }])
                ->orderBy('products_count', 'desc')
                ->orderBy('name')
                ->get();

            // Orders summary
            $totalOrders = \App\Models\Order::count();

            // Date filters: accept start_date and end_date (YYYY-MM-DD). If not provided, default to today.
            $start = $request->query('start_date');
            $end = $request->query('end_date');
            // If only one of the dates is provided, default the missing one to the provided one
            if (empty($start) && empty($end)) {
                $start = now()->format('Y-m-d');
                $end = now()->format('Y-m-d');
            } elseif (empty($start) && ! empty($end)) {
                $start = $end;
            } elseif (! empty($start) && empty($end)) {
                $end = $start;
            }

            // Normalize dates and ensure start <= end
            try {
                $s = \Carbon\Carbon::createFromFormat('Y-m-d', $start);
                $e = \Carbon\Carbon::createFromFormat('Y-m-d', $end);
                if ($s->gt($e)) {
                    // swap
                    [$s, $e] = [$e, $s];
                }
                $startDate = $s->startOfDay()->format('Y-m-d H:i:s');
                $endDate = $e->endOfDay()->format('Y-m-d H:i:s');
                // update string versions for logging and view defaults
                $start = $s->format('Y-m-d');
                $end = $e->format('Y-m-d');
            } catch (\Throwable $ex) {
                $startDate = ($start ?: now()->format('Y-m-d')).' 00:00:00';
                $endDate = ($end ?: now()->format('Y-m-d')).' 23:59:59';
            }

            // Log the received date and client filters for debugging
            try {
                Log::info('Dashboard filter params', [
                    'start' => $start,
                    'end' => $end,
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                    'client_id' => $request->query('client_id'),
                    'client_name' => $request->query('client_name'),
                ]);
            } catch (\Throwable $_logE) {
                // no-op
            }

            // Aggregation: orders count per day within range
            $ordersCountQuery = \App\Models\Order::selectRaw('DATE(created_at) as date, count(*) as count')
                ->whereDate('created_at', '>=', $start)
                ->whereDate('created_at', '<=', $end)
                ->groupBy('date')
                ->orderBy('date', 'desc');

            // Apply client filter to the aggregation as well (client_id or client_name)
            $clientName = $request->query('client_name') ?? $request->query('user_name');
            $clientId = $request->query('client_id') ?? $request->query('user_id');
            if (! empty($clientId)) {
                $ordersCountQuery->where('user_id', $clientId);
            } elseif (! empty($clientName)) {
                $ordersCountQuery->whereHas('user', function ($q) use ($clientName) {
                    $q->where('name', 'like', "%{$clientName}%")
                        ->orWhere('email', 'like', "%{$clientName}%");
                });
            }

            $ordersCount = $ordersCountQuery->get();
            try {
                Log::info('OrdersCountByDay debug', [
                    'rows' => $ordersCount->count(),
                    'items' => $ordersCount->pluck('count', 'date')->toArray(),
                    'sql' => $ordersCountQuery->toSql(),
                    'bindings' => $ordersCountQuery->getBindings(),
                ]);
            } catch (\Throwable $_logE) {
                // ignore logging errors
            }
            $ordersCountByDay = $ordersCount->pluck('count', 'date');

            // Orders list for the range (eager load user/customer and payments)
            $ordersQuery = \App\Models\Order::with(['user', 'payments'])
                ->whereDate('created_at', '>=', $start)
                ->whereDate('created_at', '<=', $end)
                ->orderBy('created_at', 'desc');

            // Optional server-side client filters: client_name or client_id
            // Use previously read 'client_id' / 'client_name' to match the dashboard form
            if (! empty($clientId)) {
                $ordersQuery->where('user_id', $clientId);
            } elseif (! empty($clientName)) {
                // The users table stores full name in `name` (not firstname/lastname).
                // Search by `name` or `email` to avoid SQL errors on missing columns.
                $ordersQuery->whereHas('user', function ($q) use ($clientName) {
                    $q->where('name', 'like', "%{$clientName}%")
                        ->orWhere('email', 'like', "%{$clientName}%");
                });
            }

            // Provide a clients list for the select filter (limit to 200).
            // Use Spatie roles to return only users with role 'cliente' (safer than scanning all users).
            try {
                $clients = \App\Models\User::role('cliente')->orderBy('name')->limit(200)->get(['id', 'name', 'email']);
                // If Spatie returns no users, try fallback to a `role` column (legacy)
                if (empty($clients) || $clients->isEmpty()) {
                    $clients = \App\Models\User::where('role', 'cliente')->orderBy('name')->limit(200)->get(['id', 'name', 'email']);
                }
            } catch (\Throwable $_e) {
                // If role() scope isn't available or fails, try legacy column 'role', then fallback to general users
                try {
                    $clients = \App\Models\User::where('role', 'cliente')->orderBy('name')->limit(200)->get(['id', 'name', 'email']);
                    if (empty($clients) || $clients->isEmpty()) {
                        $clients = \App\Models\User::orderBy('name')->limit(200)->get(['id', 'name', 'email']);
                    }
                } catch (\Throwable $_e2) {
                    $clients = \App\Models\User::orderBy('name')->limit(200)->get(['id', 'name', 'email']);
                }
            }

            // Paginate recent orders so the dashboard shows paginated results similar to products CRUD
            $recentOrders = $ordersQuery->paginate(9)->withQueryString();
            try {
                Log::info('RecentOrders debug', [
                    'count' => $recentOrders->count(),
                    'ids' => $recentOrders->pluck('id')->take(40)->toArray(),
                    'sql' => $ordersQuery->toSql(),
                    'bindings' => $ordersQuery->getBindings(),
                ]);
            } catch (\Throwable $_logE) {
                // ignore
            }

            // Fallback: if we filtered by client_id but no orders found, try to match by shipping_address email
            if (! empty($clientId) && $recentOrders->isEmpty()) {
                try {
                    $u = \App\Models\User::find($clientId);
                    if ($u && ! empty($u->email)) {
                        $fallbackQuery = \App\Models\Order::with(['user', 'payments'])
                            ->whereDate('created_at', '>=', $start)
                            ->whereDate('created_at', '<=', $end)
                            ->where('shipping_address->email', $u->email)
                            ->orderBy('created_at', 'desc');
                        $fallbackOrders = $fallbackQuery->limit(100)->get();
                        if ($fallbackOrders->isNotEmpty()) {
                            $recentOrders = $fallbackOrders;
                            try {
                                Log::info('RecentOrders fallback by shipping_address email', [
                                    'found' => $fallbackOrders->count(),
                                    'ids' => $fallbackOrders->pluck('id')->toArray(),
                                    'sql' => $fallbackQuery->toSql(),
                                    'bindings' => $fallbackQuery->getBindings(),
                                ]);
                            } catch (\Throwable $_e) {
                            }
                        }
                    }
                } catch (\Throwable $_e) {
                    // ignore fallback errors
                }
            }
        } catch (\Throwable $e) {
            $totalProducts = $inStock = $outOfStock = 0;
            $categories = collect();
            $totalOrders = 0;
            $recentOrders = collect();
            $ordersCountByDay = collect();
            $start = now()->format('Y-m-d');
            $end = now()->format('Y-m-d');
            $clientName = '';
            $clients = collect();
            $clientId = null;
        }

        return view('admin.dashboard.index', compact('totalProducts', 'inStock', 'outOfStock', 'categories', 'totalOrders', 'recentOrders', 'ordersCountByDay', 'start', 'end', 'clientName', 'clients', 'clientId'));
    }

    public function Internet_Ilimitado()
    {
        return view('Internet_Ilimitado');
    }

    public function nuestra_tiendas()
    {
        return view('nuestras_tiendas');

    }
}
