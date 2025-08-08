<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Category;
use App\Models\User;
use App\Exports\RevenueReportExport;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    const PERIOD_TODAY = 'today';
    const PERIOD_YESTERDAY = 'yesterday';
    const PERIOD_THIS_WEEK = 'this_week';
    const PERIOD_LAST_WEEK = 'last_week';
    const PERIOD_THIS_MONTH = 'this_month';
    const PERIOD_LAST_MONTH = 'last_month';
    const PERIOD_THIS_YEAR = 'this_year';
    const PERIOD_LAST_YEAR = 'last_year';
    const PERIOD_CUSTOM = 'custom';

    public function revenueReport(Request $request)
    {
        // Xử lý thời gian báo cáo
        $timePeriod = $request->input('time_period', self::PERIOD_THIS_MONTH);
        $compareWith = $request->input('compare_with', null);
        
        $dateRange = $this->getDateRange($timePeriod, $request);
        $startDate = $dateRange['start'];
        $endDate = $dateRange['end'];
        $fromDate = $dateRange['from'];
        $toDate = $dateRange['to'];
        
        // Lấy dữ liệu so sánh nếu có
        $compareData = null;
        if ($compareWith) {
            $compareRange = $this->getComparisonDateRange($timePeriod, $compareWith, $startDate, $endDate);
            $compareData = $this->getReportData($compareRange['start'], $compareRange['end']);
        }

        // Lấy dữ liệu báo cáo chính
        $reportData = $this->getReportData($startDate, $endDate);
        $isEmpty = $reportData['summary']->total_orders === 0;

        // Xuất Excel nếu có yêu cầu
        if ($request->has('export')) {
            $fileName = 'bao_cao_doanh_thu_' . $fromDate . '_den_' . $toDate . '.xlsx';
            return Excel::download(new RevenueReportExport($reportData, $fromDate, $toDate), $fileName);
        }

        // Hiển thị view
        return view('admin.reports.revenue', array_merge($reportData, [
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'time_period' => $timePeriod,
            'compareWith' => $compareWith,
            'isEmpty' => $isEmpty,
            'compareData' => $compareData,
            'periodOptions' => $this->getPeriodOptions(),
            'compareOptions' => $this->getCompareOptions(),
        ]));
    }

    protected function getPeriodOptions()
    {
        return [
            self::PERIOD_TODAY => 'Hôm nay',
            self::PERIOD_YESTERDAY => 'Hôm qua',
            self::PERIOD_THIS_WEEK => 'Tuần này',
            self::PERIOD_LAST_WEEK => 'Tuần trước',
            self::PERIOD_THIS_MONTH => 'Tháng này',
            self::PERIOD_LAST_MONTH => 'Tháng trước',
            self::PERIOD_THIS_YEAR => 'Năm này',
            self::PERIOD_LAST_YEAR => 'Năm trước',
            self::PERIOD_CUSTOM => 'Tùy chọn',
        ];
    }

    protected function getCompareOptions()
    {
        return [
            'previous_period' => 'Kỳ trước',
            'previous_year' => 'Cùng kỳ năm ngoái',
            'previous_month' => 'Cùng kỳ tháng trước',
            'previous_week' => 'Cùng kỳ tuần trước',
        ];
    }

    protected function getDateRange($timePeriod, $request)
    {
        switch ($timePeriod) {
            case self::PERIOD_TODAY:
                $startDate = Carbon::today()->startOfDay();
                $endDate = Carbon::now()->endOfDay();
                $fromDate = $startDate->toDateString();
                $toDate = $endDate->toDateString();
                break;

            case self::PERIOD_YESTERDAY:
                $startDate = Carbon::yesterday()->startOfDay();
                $endDate = Carbon::yesterday()->endOfDay();
                $fromDate = $startDate->toDateString();
                $toDate = $endDate->toDateString();
                break;

            case self::PERIOD_THIS_WEEK:
                $startDate = Carbon::now()->startOfWeek()->startOfDay();
                $endDate = Carbon::now()->endOfDay();
                $fromDate = $startDate->toDateString();
                $toDate = $endDate->toDateString();
                break;

            case self::PERIOD_LAST_WEEK:
                $startDate = Carbon::now()->subWeek()->startOfWeek()->startOfDay();
                $endDate = Carbon::now()->subWeek()->endOfWeek()->endOfDay();
                $fromDate = $startDate->toDateString();
                $toDate = $endDate->toDateString();
                break;

            case self::PERIOD_THIS_MONTH:
                $startDate = Carbon::now()->startOfMonth()->startOfDay();
                $endDate = Carbon::now()->endOfDay();
                $fromDate = $startDate->toDateString();
                $toDate = $endDate->toDateString();
                break;

            case self::PERIOD_LAST_MONTH:
                $startDate = Carbon::now()->subMonth()->startOfMonth()->startOfDay();
                $endDate = Carbon::now()->subMonth()->endOfMonth()->endOfDay();
                $fromDate = $startDate->toDateString();
                $toDate = $endDate->toDateString();
                break;

            case self::PERIOD_THIS_YEAR:
                $startDate = Carbon::now()->startOfYear()->startOfDay();
                $endDate = Carbon::now()->endOfDay();
                $fromDate = $startDate->toDateString();
                $toDate = $endDate->toDateString();
                break;

            case self::PERIOD_LAST_YEAR:
                $startDate = Carbon::now()->subYear()->startOfYear()->startOfDay();
                $endDate = Carbon::now()->subYear()->endOfYear()->endOfDay();
                $fromDate = $startDate->toDateString();
                $toDate = $endDate->toDateString();
                break;

            case self::PERIOD_CUSTOM:
                $fromDate = $request->input('from_date', Carbon::now()->subDays(30)->toDateString());
                $toDate = $request->input('to_date', Carbon::now()->toDateString());
                $startDate = Carbon::parse($fromDate)->startOfDay();
                $endDate = Carbon::parse($toDate)->endOfDay();
                break;

            default:
                $startDate = Carbon::now()->subDays(30)->startOfDay();
                $endDate = Carbon::now()->endOfDay();
                $fromDate = $startDate->toDateString();
                $toDate = $endDate->toDateString();
        }

        return [
            'start' => $startDate,
            'end' => $endDate,
            'from' => $fromDate,
            'to' => $toDate,
        ];
    }

    protected function getComparisonDateRange($currentPeriod, $compareWith, $currentStart, $currentEnd)
    {
        $daysDiff = $currentStart->diffInDays($currentEnd);
        
        switch ($compareWith) {
            case 'previous_period':
                $periodDiff = $currentEnd->diffInDays($currentStart);
                return [
                    'start' => $currentStart->copy()->subDays($periodDiff + 1),
                    'end' => $currentStart->copy()->subDay(),
                ];
                
            case 'previous_year':
                return [
                    'start' => $currentStart->copy()->subYear(),
                    'end' => $currentEnd->copy()->subYear(),
                ];
                
            case 'previous_month':
                return [
                    'start' => $currentStart->copy()->subMonth(),
                    'end' => $currentEnd->copy()->subMonth(),
                ];
                
            case 'previous_week':
                return [
                    'start' => $currentStart->copy()->subWeek(),
                    'end' => $currentEnd->copy()->subWeek(),
                ];
                
            default:
                return [
                    'start' => $currentStart->copy()->subDays($daysDiff + 1),
                    'end' => $currentStart->copy()->subDay(),
                ];
        }
    }

    protected function getReportData($startDate, $endDate)
    {
        return [
            'summary' => $this->getRevenueSummary($startDate, $endDate),
            'revenueByDate' => $this->getRevenueByDate($startDate, $endDate),
            'revenueByChannel' => $this->getRevenueByChannel($startDate, $endDate),
            'topProducts' => $this->getTopProducts($startDate, $endDate),
            'orderStatusStats' => $this->getOrderStatusStats($startDate, $endDate),
            'revenueByCategory' => $this->getRevenueByCategory($startDate, $endDate),
            'customerLoyalty' => $this->getCustomerLoyalty($startDate, $endDate),
            'purchaseFrequency' => $this->getPurchaseFrequency($startDate, $endDate),
            'customerRegions' => $this->getCustomerRegions($startDate, $endDate),
            'inventoryStats' => $this->getInventoryStats(),
            'inventoryProducts' => $this->getInventoryProducts(),
        ];
    }

    protected function getRevenueSummary($startDate, $endDate)
    {
        return Order::whereBetween('created_at', [$startDate, $endDate])
            ->select([
                DB::raw("SUM(total) as total_revenue"),
                DB::raw("COUNT(*) as total_orders"),
                DB::raw("AVG(total) as avg_order_value"),
                DB::raw("MAX(total) as max_order_value"),
                DB::raw("MIN(total) as min_order_value"),
            ])
            ->first();
    }

    protected function getRevenueByDate($startDate, $endDate)
    {
        return Order::whereBetween('created_at', [$startDate, $endDate])
            ->select([
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total) as total_revenue'),
                DB::raw('COUNT(*) as order_count')
            ])
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    protected function getRevenueByChannel($startDate, $endDate)
    {
        return Order::whereBetween('created_at', [$startDate, $endDate])
            ->select([
                'channel',
                DB::raw('SUM(total) as total_revenue'),
                DB::raw('COUNT(*) as order_count')
            ])
            ->groupBy('channel')
            ->orderByDesc('total_revenue')
            ->get();
    }

    protected function getTopProducts($startDate, $endDate, $limit = 10)
    {
        return OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->select([
                'products.id',
                'products.name as product_name',
                'product_variants.image',
                'product_variants.sku',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.quantity * order_items.price) as total_revenue'),
            ])
            ->groupBy('products.id', 'products.name', 'product_variants.image', 'product_variants.sku')
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get();
    }

    protected function getOrderStatusStats($startDate, $endDate)
    {
        $statuses = Order::whereBetween('created_at', [$startDate, $endDate])
            ->select([
                'status',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total) as total_value')
            ])
            ->groupBy('status')
            ->orderByDesc('count')
            ->get();

        $statusMap = [
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
            'processing' => 'Đang xử lý',
            'pending' => 'Chờ xử lý',
            'shipped' => 'Đã giao hàng',
            'returned' => 'Đã hoàn trả',
        ];

        return $statuses->map(function ($item) use ($statusMap) {
            $item->status_name = $statusMap[$item->status] ?? $item->status;
            return $item;
        });
    }

    protected function getRevenueByCategory($startDate, $endDate)
    {
        return Category::join('products', 'categories.id', '=', 'products.category_id')
            ->join('product_variants', 'products.id', '=', 'product_variants.product_id')
            ->join('order_items', 'product_variants.id', '=', 'order_items.product_variant_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->select([
                'categories.id',
                'categories.name',
                'categories.slug',
                DB::raw('SUM(order_items.quantity * order_items.price) as total_revenue'),
                DB::raw('SUM(order_items.quantity) as total_quantity'),
            ])
            ->groupBy('categories.id', 'categories.name', 'categories.slug')
            ->orderByDesc('total_revenue')
            ->get();
    }

    protected function getCustomerLoyalty($startDate, $endDate)
    {
        $userOrders = Order::join('users', 'orders.user_id', '=', 'users.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->select([
                'users.id',
                DB::raw('COUNT(orders.id) as order_count')
            ])
            ->groupBy('users.id')
            ->get();

        return (object) [
            'new_customers' => $userOrders->where('order_count', 1)->count(),
            'returning_customers' => $userOrders->where('order_count', 2)->count(),
            'loyal_customers' => $userOrders->where('order_count', '>', 2)->count(),
        ];
    }

    protected function getPurchaseFrequency($startDate, $endDate)
    {
        $userOrders = Order::join('users', 'orders.user_id', '=', 'users.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->select([
                'users.id',
                DB::raw('COUNT(orders.id) as order_count')
            ])
            ->groupBy('users.id')
            ->get();

        $frequencyRanges = [
            '1 lần' => 0,
            '2-3 lần' => 0,
            '4-5 lần' => 0,
            '6-10 lần' => 0,
            'Trên 10 lần' => 0
        ];

        foreach ($userOrders as $user) {
            if ($user->order_count == 1) {
                $frequencyRanges['1 lần']++;
            } elseif ($user->order_count >= 2 && $user->order_count <= 3) {
                $frequencyRanges['2-3 lần']++;
            } elseif ($user->order_count >= 4 && $user->order_count <= 5) {
                $frequencyRanges['4-5 lần']++;
            } elseif ($user->order_count >= 6 && $user->order_count <= 10) {
                $frequencyRanges['6-10 lần']++;
            } else {
                $frequencyRanges['Trên 10 lần']++;
            }
        }

        $result = [];
        foreach ($frequencyRanges as $range => $count) {
            $result[] = (object) [
                'frequency_range' => $range,
                'count' => $count
            ];
        }

        return collect($result);
    }

    protected function getCustomerRegions($startDate, $endDate)
    {
        return Order::join('users', 'orders.user_id', '=', 'users.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->whereNotNull('users.address')
            ->select([
                'users.address as region',
                DB::raw('COUNT(DISTINCT users.id) as count'),
                DB::raw('SUM(orders.total) as total_revenue')
            ])
            ->groupBy('users.address')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get();
    }

    protected function getInventoryStats()
    {
        return (object) [
            'total_products' => Product::count(),
            'total_inventory_value' => DB::table('product_variants')
                ->select(DB::raw('SUM(stock_quantity * price) as total_value'))
                ->first()->total_value ?? 0,
            'in_stock_products' => Product::whereHas('variants', function($query) {
                $query->where('stock_quantity', '>', 0);
            })->count(),
            'low_stock_products' => Product::whereHas('variants', function($query) {
                $query->where('stock_quantity', '>', 0)
                      ->whereColumn('stock_quantity', '<=', 'low_stock_threshold');
            })->count(),
            'out_of_stock_products' => Product::whereDoesntHave('variants', function($query) {
                $query->where('stock_quantity', '>', 0);
            })->count(),
        ];
    }

    protected function getInventoryProducts($limit = 10)
    {
        return Product::with(['variants' => function($query) {
                $query->select(['id', 'product_id', 'stock_quantity', 'low_stock_threshold', 'price'])
                      ->orderBy('stock_quantity');
            }])
            ->select(['id', 'name'])
            ->addSelect([
                'stock_quantity' => ProductVariant::select(DB::raw('SUM(stock_quantity)'))
                    ->whereColumn('product_variants.product_id', 'products.id'),
                'inventory_value' => ProductVariant::select(DB::raw('SUM(stock_quantity * price)'))
                    ->whereColumn('product_variants.product_id', 'products.id'),
            ])
            ->orderBy('stock_quantity')
            ->limit($limit)
            ->get();
    }
}