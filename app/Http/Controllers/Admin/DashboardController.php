<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
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
        $topProducts = $this->getTopProducts();
        $statusColors = $this->getStatusColors();
        $statusNames = $this->getStatusNames();

        return view('admin.dashboard', compact(
            'orderStats',
            'revenueStats',
            'productStats',
            'userStats',
            'latestOrders',
            'lowStockProducts',
            'topProducts',
            'statusColors',
            'statusNames'
        ));
    }

    protected function getOrderStats(): array
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $thisWeek = Carbon::now()->startOfWeek();
        $lastWeek = Carbon::now()->subWeek()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        $todayOrders = Order::whereDate('created_at', $today);
        $yesterdayOrders = Order::whereDate('created_at', $yesterday);
        $thisWeekOrders = Order::where('created_at', '>=', $thisWeek);
        $lastWeekOrders = Order::whereBetween('created_at', [$lastWeek, $thisWeek]);
        $thisMonthOrders = Order::where('created_at', '>=', $thisMonth);
        $lastMonthOrders = Order::whereBetween('created_at', [$lastMonth, $thisMonth]);

        $completedOrders = Order::where('status', 'completed');
        $cancelledOrders = Order::where('status', 'cancelled');
        $pendingOrders = Order::where('status', 'pending');
        $overdueOrders = Order::where('status', 'pending')
            ->where('created_at', '<=', Carbon::now()->subDay());

        return [
            'today' => [
                'total' => $todayOrders->count(),
                'completed' => $todayOrders->clone()->where('status', 'completed')->count(),
                'revenue' => $todayOrders->clone()->where('status', 'completed')->sum('total'),
            ],
            'yesterday' => [
                'total' => $yesterdayOrders->count(),
                'completed' => $yesterdayOrders->clone()->where('status', 'completed')->count(),
                'revenue' => $yesterdayOrders->clone()->where('status', 'completed')->sum('total'),
            ],
            'this_week' => [
                'total' => $thisWeekOrders->clone()->count(),
                'completed' => $thisWeekOrders->clone()->where('status', 'completed')->count(),
                'revenue' => $thisWeekOrders->clone()->where('status', 'completed')->sum('total'),
            ],
            'last_week' => [
                'total' => $lastWeekOrders->clone()->count(),
                'completed' => $lastWeekOrders->clone()->where('status', 'completed')->count(),
                'revenue' => $lastWeekOrders->clone()->where('status', 'completed')->sum('total'),
            ],
            'this_month' => [
                'total' => $thisMonthOrders->clone()->count(),
                'completed' => $thisMonthOrders->clone()->where('status', 'completed')->count(),
                'revenue' => $thisMonthOrders->clone()->where('status', 'completed')->sum('total'),
            ],
            'last_month' => [
                'total' => $lastMonthOrders->clone()->count(),
                'completed' => $lastMonthOrders->clone()->where('status', 'completed')->count(),
                'revenue' => $lastMonthOrders->clone()->where('status', 'completed')->sum('total'),
            ],
            'status_counts' => Order::selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),
            'completed_count' => $completedOrders->count(),
            'cancelled_count' => $cancelledOrders->count(),
            'pending_count' => $pendingOrders->count(),
            'overdue_count' => $overdueOrders->count(),
            'completed_percentage' => $this->calculatePercentage($completedOrders->count(), Order::count()),
            'cancelled_percentage' => $this->calculatePercentage($cancelledOrders->count(), Order::count()),
            'pending_percentage' => $this->calculatePercentage($pendingOrders->count(), Order::count()),
            'cancellation_increase' => $this->calculateCancellationIncrease(),
        ];
    }

    protected function getRevenueStats(): array
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $thisWeek = Carbon::now()->startOfWeek();
        $lastWeek = Carbon::now()->subWeek()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        $thisYear = Carbon::now()->startOfYear();
        $lastYear = Carbon::now()->subYear()->startOfYear();

        $todayRevenue = Order::whereDate('created_at', $today)
            ->where('status', 'completed')
            ->sum('total');
        $yesterdayRevenue = Order::whereDate('created_at', $yesterday)
            ->where('status', 'completed')
            ->sum('total');
        $thisWeekRevenue = Order::where('created_at', '>=', $thisWeek)
            ->where('status', 'completed')
            ->sum('total');
        $lastWeekRevenue = Order::whereBetween('created_at', [$lastWeek, $thisWeek])
            ->where('status', 'completed')
            ->sum('total');
        $thisMonthRevenue = Order::where('created_at', '>=', $thisMonth)
            ->where('status', 'completed')
            ->sum('total');
        $lastMonthRevenue = Order::whereBetween('created_at', [$lastMonth, $thisMonth])
            ->where('status', 'completed')
            ->sum('total');
        $thisYearRevenue = Order::where('created_at', '>=', $thisYear)
            ->where('status', 'completed')
            ->sum('total');
        $lastYearRevenue = Order::whereBetween('created_at', [$lastYear, $thisYear])
            ->where('status', 'completed')
            ->sum('total');

        return [
            'today' => $todayRevenue,
            'yesterday' => $yesterdayRevenue,
            'this_week' => $thisWeekRevenue,
            'last_week' => $lastWeekRevenue,
            'this_month' => $thisMonthRevenue,
            'last_month' => $lastMonthRevenue,
            'this_year' => $thisYearRevenue,
            'last_year' => $lastYearRevenue,
            'daily' => $this->getDailyRevenue(),
            'today_percent_change' => $this->calculatePercentageChange($todayRevenue, $yesterdayRevenue),
            'weekly_percent_change' => $this->calculatePercentageChange($thisWeekRevenue, $lastWeekRevenue),
            'monthly_percent_change' => $this->calculatePercentageChange($thisMonthRevenue, $lastMonthRevenue),
            'yearly_percent_change' => $this->calculatePercentageChange($thisYearRevenue, $lastYearRevenue),
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
        $yesterday = Carbon::yesterday();
        $thisWeek = Carbon::now()->startOfWeek();
        $lastWeek = Carbon::now()->subWeek()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        $thisYear = Carbon::now()->startOfYear();

        $todayUsers = User::whereDate('created_at', $today)->count();
        $yesterdayUsers = User::whereDate('created_at', $yesterday)->count();
        $thisWeekUsers = User::where('created_at', '>=', $thisWeek)->count();
        $lastWeekUsers = User::whereBetween('created_at', [$lastWeek, $thisWeek])->count();
        $thisMonthUsers = User::where('created_at', '>=', $thisMonth)->count();
        $lastMonthUsers = User::whereBetween('created_at', [$lastMonth, $thisMonth])->count();
        $thisYearUsers = User::where('created_at', '>=', $thisYear)->count();

        // Lấy dữ liệu 7 ngày gần nhất cho biểu đồ
        $last7DaysLabels = [];
        $last7DaysData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $last7DaysLabels[] = $date->format('d/m');
            $last7DaysData[] = User::whereDate('created_at', $date)->count();
        }

        // Tính tỉ lệ quay lại (khách hàng có ít nhất 2 đơn hàng)
        $returningCustomers = User::has('orders', '>=', 2)->count();
        $totalCustomers = User::count();
        $retentionRate = $totalCustomers > 0 ? round(($returningCustomers / $totalCustomers) * 100) : 0;

        return [
            'total' => $totalCustomers,
            'today' => $todayUsers,
            'yesterday' => $yesterdayUsers,
            'this_week' => $thisWeekUsers,
            'last_week' => $lastWeekUsers,
            'this_month' => $thisMonthUsers,
            'last_month' => $lastMonthUsers,
            'this_year' => $thisYearUsers,
            'today_percent_change' => $this->calculatePercentageChange($todayUsers, $yesterdayUsers),
            'weekly_percent_change' => $this->calculatePercentageChange($thisWeekUsers, $lastWeekUsers),
            'monthly_percent_change' => $this->calculatePercentageChange($thisMonthUsers, $lastMonthUsers),
            'last_7_days_labels' => $last7DaysLabels,
            'last_7_days_data' => $last7DaysData,
            'retention_rate' => $retentionRate,
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

    protected function getTopProducts()
    {
        return ProductVariant::withCount(['orderItems as sold_count' => function($query) {
                $query->selectRaw('COALESCE(SUM(quantity), 0)')
                    ->whereHas('order', function($q) {
                        $q->where('status', 'completed');
                    });
            }])
            ->orderBy('sold_count', 'desc')
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

    protected function calculatePercentage($part, $total): float
    {
        return $total > 0 ? round(($part / $total) * 100, 1) : 0;
    }

    protected function calculatePercentageChange($current, $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        return round((($current - $previous) / $previous) * 100, 1);
    }

    protected function calculateCancellationIncrease(): float
    {
        $thisWeekCancellations = Order::where('status', 'cancelled')
            ->where('created_at', '>=', Carbon::now()->startOfWeek())
            ->count();
            
        $lastWeekCancellations = Order::where('status', 'cancelled')
            ->whereBetween('created_at', [
                Carbon::now()->subWeek()->startOfWeek(),
                Carbon::now()->subWeek()->endOfWeek()
            ])
            ->count();

        return $this->calculatePercentageChange($thisWeekCancellations, $lastWeekCancellations);
    }
}