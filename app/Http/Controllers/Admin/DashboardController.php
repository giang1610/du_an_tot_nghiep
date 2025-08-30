<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;


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

        $allStatuses = array_keys($this->getStatusNames());
        $statusCounts = Order::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Đảm bảo đủ tất cả trạng thái
        $statusCountsFull = [];
        foreach ($allStatuses as $status) {
            $statusCountsFull[$status] = isset($statusCounts[$status]) ? $statusCounts[$status] : 0;
        }
        $totalOrders = array_sum($statusCountsFull);

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
            'status_counts' => $statusCountsFull,
            'total_orders' => $totalOrders,
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

        $thisWeekStart = Carbon::now()->startOfWeek();
        $thisWeekEnd = Carbon::now()->endOfWeek();
        $lastWeekStart = Carbon::now()->subWeek()->startOfWeek();
        $lastWeekEnd = Carbon::now()->subWeek()->endOfWeek();

        $thisMonthStart = Carbon::now()->startOfMonth();
        $thisMonthEnd = Carbon::now()->endOfMonth();
        $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        $thisYearStart = Carbon::now()->startOfYear();
        $thisYearEnd = Carbon::now()->endOfYear();
        $lastYearStart = Carbon::now()->subYear()->startOfYear();
        $lastYearEnd = Carbon::now()->subYear()->endOfYear();

        $todayRevenue = Order::whereDate('created_at', $today)
            ->where('status', 'completed')
            ->sum('total');
        $yesterdayRevenue = Order::whereDate('created_at', $yesterday)
            ->where('status', 'completed')
            ->sum('total');
        $thisWeekRevenue = Order::whereBetween('created_at', [$thisWeekStart, $thisWeekEnd])
            ->where('status', 'completed')
            ->sum('total');
        $lastWeekRevenue = Order::whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])
            ->where('status', 'completed')
            ->sum('total');
        $thisMonthRevenue = Order::whereBetween('created_at', [$thisMonthStart, $thisMonthEnd])
            ->where('status', 'completed')
            ->sum('total');
        $lastMonthRevenue = Order::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->where('status', 'completed')
            ->sum('total');
        $thisYearRevenue = Order::whereBetween('created_at', [$thisYearStart, $thisYearEnd])
            ->where('status', 'completed')
            ->sum('total');
        $lastYearRevenue = Order::whereBetween('created_at', [$lastYearStart, $lastYearEnd])
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
            'daily_30' => $this->getDailyRevenue(30),
            'daily_7' => $this->getDailyRevenue(7),
            'daily_90' => $this->getDailyRevenue(90),
            'today_percent_change' => $this->calculatePercentageChange($todayRevenue, $yesterdayRevenue),
            'weekly_percent_change' => $this->calculatePercentageChange($thisWeekRevenue, $lastWeekRevenue),
            'monthly_percent_change' => $this->calculatePercentageChange($thisMonthRevenue, $lastMonthRevenue),
            'yearly_percent_change' => $this->calculatePercentageChange($thisYearRevenue, $lastYearRevenue),
        ];
    }

    protected function getDailyRevenue($days = 30): array
    {
        $startDate = Carbon::now()->subDays($days - 1)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        $revenues = Order::selectRaw('DATE(created_at) as date, COALESCE(SUM(total), 0) as total')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy(function ($item) {
                return Carbon::parse($item->date)->format('Y-m-d');
            })
            ->map(function ($item) {
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
            'out_of_stock' => Product::whereHas('variants.stock', function ($query) {
                $query->where('quantity', '<=', 0);
            })->count(),
            'low_stock' => Product::whereHas('variants.stock', function ($query) {
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
            ->map(function ($order) {
                $order->status_name = $this->getStatusName($order->status);
                $order->status_color = $this->getStatusColor($order->status);
                return $order;
            });
    }

    protected function getLowStockProducts()
    {
        return Product::whereHas('variants.stock', function ($query) {
            $query->where('quantity', '<', 10);
        })
            ->with([
                'variants' => function ($query) {
                    $query->whereHas('stock', function ($q) {
                        $q->where('quantity', '<', 10);
                    })->with(['stock', 'color', 'size']);
                },
                'category'
            ])
            ->limit(5)
            ->get();
    }

    protected function getTopProducts()
    {
        return ProductVariant::with(['product'])
            ->withCount([
                'orderItems as sold_count' => function ($query) {
                    $query->selectRaw('COALESCE(SUM(quantity), 0)')
                        ->whereHas('order', function ($q) {
                            $q->where('status', 'completed');
                        });
                }
            ])
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
            'restocked' => 'Đã trả về kho',
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
            'restocked' => 'secondary',
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
            'restocked' => 'Đã trả về kho',
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
            'restocked' => '#d43494ff',
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

    public function exportExcelDashboard()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Dashboard Report');

        // Set style mặc định
        $defaultStyle = [
            'font' => ['name' => 'Arial', 'size' => 11],
            'alignment' => ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
        ];
        $spreadsheet->getDefaultStyle()->applyFromArray($defaultStyle);

        // Lấy dữ liệu
        $totalOrders = Order::count();
        $totalRevenue = Order::where('status', 'completed')->sum('total'); // Đã sửa lại đúng tên cột
        $totalProductsSold = OrderItem::sum('quantity');
        $totalUsers = User::count();

        $topSellingProducts = Product::withSum('orderItems', 'quantity')
            ->orderByDesc('order_items_sum_quantity')
            ->take(5)
            ->get();

        $monthlyRevenue = Order::selectRaw('MONTH(created_at) as month, SUM(total) as revenue')
            ->where('status', 'completed')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $orderStatusCounts = Order::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Tổng quan
        $sheet->setCellValue('A1', 'TỔNG QUAN');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $sheet->fromArray([
            ['Tổng đơn hàng', $totalOrders],
            ['Tổng doanh thu', number_format($totalRevenue, 0, ',', '.') . ' đ'],
            ['Sản phẩm đã bán', $totalProductsSold],
            ['Người dùng', $totalUsers],
        ], null, 'A2');

        $sheet->getStyle('A2:B5')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // Top 5 sản phẩm bán chạy
        $startRow = 7;
        $sheet->setCellValue("A{$startRow}", 'TOP 5 SẢN PHẨM BÁN CHẠY');
        $sheet->getStyle("A{$startRow}")->getFont()->setBold(true)->setSize(14);

        $startRow++;
        $sheet->setCellValue("A{$startRow}", 'Tên sản phẩm');
        $sheet->setCellValue("B{$startRow}", 'Số lượng đã bán');
        $sheet->getStyle("A{$startRow}:B{$startRow}")->getFont()->setBold(true);

        $row = $startRow + 1;
        foreach ($topSellingProducts as $product) {
            $sheet->setCellValue("A{$row}", $product->name);
            $sheet->setCellValue("B{$row}", $product->order_items_sum_quantity);
            $row++;
        }
        $sheet->getStyle("A" . ($startRow) . ":B" . ($row - 1))
            ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // Doanh thu theo tháng
        $col = 'D';
        $sheet->setCellValue("{$col}1", 'DOANH THU THEO THÁNG');
        $sheet->getStyle("{$col}1")->getFont()->setBold(true)->setSize(14);

        $sheet->setCellValue("{$col}2", 'Tháng');
        $sheet->setCellValue("E2", 'Doanh thu');
        $sheet->getStyle("{$col}2:E2")->getFont()->setBold(true);

        $r = 3;
        foreach ($monthlyRevenue as $item) {
            $sheet->setCellValue("{$col}{$r}", 'Tháng ' . $item->month);
            $sheet->setCellValue("E{$r}", number_format($item->revenue, 0, ',', '.') . ' đ');
            $r++;
        }
        $sheet->getStyle("{$col}2:E" . ($r - 1))
            ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // Trạng thái đơn hàng
        $rowStatus = $row + 2;
        $sheet->setCellValue("D{$rowStatus}", 'SỐ ĐƠN THEO TRẠNG THÁI');
        $sheet->getStyle("D{$rowStatus}")->getFont()->setBold(true)->setSize(14);

        $rowStatus++;
        $sheet->setCellValue("D{$rowStatus}", 'Trạng thái');
        $sheet->setCellValue("E{$rowStatus}", 'Số lượng');
        $sheet->getStyle("D{$rowStatus}:E{$rowStatus}")->getFont()->setBold(true);

        $rowStatus++;
        foreach ($orderStatusCounts as $status => $count) {
            $sheet->setCellValue("D{$rowStatus}", ucfirst($status));
            $sheet->setCellValue("E{$rowStatus}", $count);
            $rowStatus++;
        }
        $sheet->getStyle("D" . ($row + 3) . ":E" . ($rowStatus - 1))
            ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // Auto-size tất cả cột từ A -> E
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Xuất file
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'dashboard_export_' . now()->format('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename);
    }
}
