<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $orderStats = $this->getOrderStats();
        $revenueStats = $this->getRevenueStats();
        $productStats = $this->getProductStats();
        $userStats = $this->getUserStats();
        $latestOrders = $this->getLatestOrders();
        $lowStockProducts = $this->getLowStockProducts();
        $statusColors = $this->getStatusColors();
        $statusNames = $this->getStatusNames();

        return view('admin.dashboard', compact(
            'orderStats',
            'revenueStats',
            'productStats',
            'userStats',
            'latestOrders',
            'lowStockProducts',
            'statusColors',
            'statusNames'
        ));
    }

    protected function getOrderStats(): array
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        return [
            'today' => [
                'total' => Order::whereDate('created_at', $today)->count(),
                'completed' => Order::whereDate('created_at', $today)
                    ->where('status', 'completed')
                    ->count(),
                'revenue' => Order::whereDate('created_at', $today)
                    ->where('status', 'completed')
                    ->sum('total'),
            ],
            'yesterday' => [
                'total' => Order::whereDate('created_at', $yesterday)->count(),
                'completed' => Order::whereDate('created_at', $yesterday)
                    ->where('status', 'completed')
                    ->count(),
                'revenue' => Order::whereDate('created_at', $yesterday)
                    ->where('status', 'completed')
                    ->sum('total'),
            ],
            'this_month' => [
                'total' => Order::where('created_at', '>=', $thisMonth)->count(),
                'completed' => Order::where('created_at', '>=', $thisMonth)
                    ->where('status', 'completed')
                    ->count(),
                'revenue' => Order::where('created_at', '>=', $thisMonth)
                    ->where('status', 'completed')
                    ->sum('total'),
            ],
            'last_month' => [
                'total' => Order::whereBetween('created_at', [$lastMonth, $thisMonth])->count(),
                'completed' => Order::whereBetween('created_at', [$lastMonth, $thisMonth])
                    ->where('status', 'completed')
                    ->count(),
                'revenue' => Order::whereBetween('created_at', [$lastMonth, $thisMonth])
                    ->where('status', 'completed')
                    ->sum('total'),
            ],
            'status_counts' => Order::selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),
        ];
    }

    protected function getRevenueStats(): array
    {
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();
        $thisYear = Carbon::now()->startOfYear();

        return [
            'today' => Order::whereDate('created_at', $today)
                ->where('status', 'completed')
                ->sum('total'),
            'this_week' => Order::where('created_at', '>=', $thisWeek)
                ->where('status', 'completed')
                ->sum('total'),
            'this_month' => Order::where('created_at', '>=', $thisMonth)
                ->where('status', 'completed')
                ->sum('total'),
            'this_year' => Order::where('created_at', '>=', $thisYear)
                ->where('status', 'completed')
                ->sum('total'),
            'daily' => $this->getDailyRevenue(),
        ];
    }

    protected function getDailyRevenue(): array
    {
        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now();

        $revenues = Order::selectRaw('DATE(created_at) as date, COALESCE(SUM(total), 0) as total')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy(function($item) {
                return Carbon::parse($item->date)->format('Y-m-d');
            })
            ->map(function($item) {
                return $item->total;
            })
            ->toArray();

        $dates = [];
        $current = clone $startDate;

        while ($current <= $endDate) {
            $dateKey = $current->format('Y-m-d');
            $dateLabel = $current->format('d/m');
            $dates[$dateLabel] = $revenues[$dateKey] ?? 0;
            $current->addDay();
        }

        return $dates;
    }

    protected function getProductStats(): array
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

    protected function getUserStats(): array
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        return [
            'total' => User::count(),
            'today' => User::whereDate('created_at', $today)->count(),
            'this_month' => User::where('created_at', '>=', $thisMonth)->count(),
        ];
    }

    protected function getLatestOrders()
    {
        return Order::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($order) {
                $order->status_name = $this->getStatusName($order->status);
                $order->status_color = $this->getStatusColor($order->status);
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
            ->limit(5)
            ->get();
    }

    protected function getStatusName(string $status): string
    {
        $statuses = [
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

        return $statuses[$status] ?? ucfirst($status);
    }

    protected function getStatusColor(string $status): string
    {
        $colors = [
            'pending' => 'warning',
            'processing' => 'info',
            'completed' => 'success',
            'cancelled' => 'danger',
            'shipped' => 'primary',
            'failed' => 'secondary',
            'picking' => 'info',
            'shipping' => 'primary',
            'returned' => 'dark',
        ];

        return $colors[$status] ?? 'secondary';
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
}