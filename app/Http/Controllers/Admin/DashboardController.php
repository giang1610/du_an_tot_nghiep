<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $timeRanges = $this->getTimeRanges();

        $data = [
            'revenue' => $this->getRevenueData($timeRanges),
            'orders' => $this->getOrderData($timeRanges),
            'customers' => $this->getCustomerData($timeRanges),
            'products' => $this->getProductData(),
            'status_info' => [
                'names' => $this->getStatusNames(),
                'colors' => $this->getStatusColors(),
                'badge_classes' => $this->getStatusBadgeClasses()
            ]
        ];

        return view('admin.dashboard', array_merge($data, [
            'pendingOrders' => $this->getPendingOrders(),
            'lowStockProducts' => $this->getLowStockProducts(),
            'topProducts' => $this->getTopProducts()
        ]));
    }

    protected function getTimeRanges(): array
    {
        $now = Carbon::now();
        return [
            'today' => Carbon::today(),
            'yesterday' => Carbon::yesterday(),
            'this_week' => $now->clone()->startOfWeek(),
            'last_week' => $now->clone()->subWeek()->startOfWeek(),
            'this_month' => $now->clone()->startOfMonth(),
            'last_month' => $now->clone()->subMonth()->startOfMonth(),
            'this_year' => $now->clone()->startOfYear(),
            'last_30_days' => $now->clone()->subDays(30)
        ];
    }

    protected function getRevenueData(array $ranges): array
    {
        $revenueToday = Order::whereDate('created_at', $ranges['today'])
            ->where('status', 'completed')
            ->sum('total');

        $revenueYesterday = Order::whereDate('created_at', $ranges['yesterday'])
            ->where('status', 'completed')
            ->sum('total');

        $dailyChange = $revenueYesterday > 0
            ? round(($revenueToday - $revenueYesterday) / $revenueYesterday * 100, 2)
            : 0;

        $revenueThisMonth = Order::where('created_at', '>=', $ranges['this_month'])
            ->where('status', 'completed')
            ->sum('total');

        $revenueLastMonth = Order::whereBetween('created_at', [$ranges['last_month'], $ranges['this_month']])
            ->where('status', 'completed')
            ->sum('total');

        $monthlyChange = $revenueLastMonth > 0
            ? round(($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth * 100, 2)
            : 0;

        // Daily revenue for chart (last 7 days)
        $dailyRevenue = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $revenue = Order::whereDate('created_at', $date)
                ->where('status', 'completed')
                ->sum('total');

            $dailyRevenue['labels'][] = $date->format('d/m');
            $dailyRevenue['data'][] = $revenue;
        }

        return [
            'today' => $revenueToday,
            'yesterday' => $revenueYesterday,
            'daily_change' => $dailyChange,
            'this_month' => $revenueThisMonth,
            'last_month' => $revenueLastMonth,
            'monthly_change' => $monthlyChange,
            'this_year' => Order::where('created_at', '>=', $ranges['this_year'])
                ->where('status', 'completed')
                ->sum('total'),
            'daily_data' => $dailyRevenue,
            'formatted' => [
                'today' => number_format($revenueToday),
                'yesterday' => number_format($revenueYesterday),
                'this_month' => number_format($revenueThisMonth),
                'last_month' => number_format($revenueLastMonth)
            ]
        ];
    }

    protected function getOrderData(array $ranges): array
    {
        $totalOrders = Order::count();
        $statusCounts = Order::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $pendingOrders24h = Order::where('status', 'pending')
            ->where('created_at', '<', Carbon::now()->subHours(24))
            ->count();

        return [
            'total' => $totalOrders,
            'today' => Order::whereDate('created_at', $ranges['today'])->count(),
            'this_month' => Order::where('created_at', '>=', $ranges['this_month'])->count(),
            'status_counts' => $statusCounts,
            'pending_24h' => $pendingOrders24h,
            'completion_rate' => $totalOrders > 0
                ? round(($statusCounts['completed'] ?? 0) / $totalOrders * 100, 2)
                : 0,
            'cancellation_rate' => $totalOrders > 0
                ? round(($statusCounts['cancelled'] ?? 0) / $totalOrders * 100, 2)
                : 0
        ];
    }

    protected function getCustomerData(array $ranges): array
    {
        $newToday = User::whereDate('created_at', $ranges['today'])->count();
        $newThisMonth = User::where('created_at', '>=', $ranges['this_month'])->count();
        $newLastMonth = User::whereBetween('created_at', [$ranges['last_month'], $ranges['this_month']])->count();

        $monthlyChange = $newLastMonth > 0
            ? round(($newThisMonth - $newLastMonth) / $newLastMonth * 100, 2)
            : 0;

        // Returning customers (made at least 2 orders)
        $returningCustomers = User::has('orders', '>=', 2)->count();
        $totalCustomers = User::count();

        return [
            'total' => $totalCustomers,
            'new_today' => $newToday,
            'new_this_month' => $newThisMonth,
            'monthly_change' => $monthlyChange,
            'returning' => $returningCustomers,
            'return_rate' => $totalCustomers > 0
                ? round($returningCustomers / $totalCustomers * 100, 2)
                : 0
        ];
    }

    protected function getProductData(): array
    {
        return [
            'total' => Product::count(),
            'active' => Product::where('status', 1)->count(),
            'out_of_stock' => Product::whereHas('variants.stock', function($query) {
                $query->where('quantity', '<=', 0);
            })->count(),
            'low_stock' => Product::whereHas('variants.stock', function($query) {
                $query->where('quantity', '<', 10)
                      ->where('quantity', '>', 0);
            })->count(),
        ];
    }

    protected function getPendingOrders()
    {
        return Order::with('user')
            ->where('status', 'pending')
            ->where('created_at', '<', Carbon::now()->subHours(24))
            ->orderBy('created_at', 'asc')
            ->limit(5)
            ->get()
            ->map(function($order) {
                $order->status_name = $this->getStatusNames()[$order->status] ?? ucfirst($order->status);
                $order->status_color = $this->getStatusColors()[$order->status] ?? '#6c757d';
                return $order;
            });
    }

    protected function getLowStockProducts()
    {
        return Product::whereHas('variants.stock', function($query) {
                $query->where('quantity', '<', 10);
            })
            ->with(['variants' => function($query) {
                $query->whereHas('stock', function($q) {
                    $q->where('quantity', '<', 10);
                })->with(['stock', 'color', 'size']);
            }, 'category'])
            ->orderByRaw('(SELECT MIN(quantity) FROM stocks WHERE stocks.product_variant_id IN (SELECT id FROM product_variants WHERE product_id = products.id))')
            ->limit(5)
            ->get();
    }

    protected function getTopProducts()
    {
        return Product::withCount(['orderItems as sold' => function($query) {
                $query->select(DB::raw('COALESCE(SUM(quantity), 0)'))
                    ->whereHas('order', function($q) {
                        $q->where('status', 'completed');
                    });
            }])
            ->with(['variants.stock'])
            ->orderBy('sold', 'desc')
            ->limit(5)
            ->get()
            ->map(function($product) {
                $product->stock = $product->variants->sum(function($variant) {
                    return $variant->stock->quantity ?? 0;
                });
                return $product;
            });
    }

    protected function getStatusNames(): array
    {
        return [
            'pending' => 'Chờ xử lý',
            'processing' => 'Đang xử lý',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
            'shipped' => 'Đã giao',
            'failed' => 'Thất bại',
            'picking' => 'Đang lấy hàng',
            'shipping' => 'Đang giao',
            'returned' => 'Đã trả hàng',
        ];
    }

    protected function getStatusColors(): array
    {
        return [
            'pending' => '#f6c23e',
            'processing' => '#36b9cc',
            'completed' => '#1cc88a',
            'cancelled' => '#e74a3b',
            'shipped' => '#4e73df',
            'failed' => '#5a5c69',
            'picking' => '#36b9cc',
            'shipping' => '#4e73df',
            'returned' => '#5a5c69',
        ];
    }

    protected function getStatusBadgeClasses(): array
    {
        return [
            'pending' => 'bg-warning',
            'processing' => 'bg-info',
            'completed' => 'bg-success',
            'cancelled' => 'bg-danger',
            'shipped' => 'bg-primary',
            'failed' => 'bg-secondary',
            'picking' => 'bg-info',
            'shipping' => 'bg-primary',
            'returned' => 'bg-dark',
        ];
    }
}
