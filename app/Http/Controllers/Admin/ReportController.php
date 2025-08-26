<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Category;
use App\Models\User;
use App\Models\Voucher;
use App\Exports\RevenueReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Exception;

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

    // Cache time in minutes
    const CACHE_TIME = 60;

    /**
     * Display revenue report
     */
    public function revenueReport(Request $request)
    {
        try {
            // Validate input
            $validator = Validator::make($request->all(), [
                'time_period' => 'sometimes|in:today,yesterday,this_week,last_week,this_month,last_month,this_year,last_year,custom',
                'compare_with' => 'nullable|in:previous_period,same_period_last_week,same_period_last_month,same_period_last_year',
                'from_date' => 'required_if:time_period,custom|date',
                'to_date' => 'required_if:time_period,custom|date|after_or_equal:from_date',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $timePeriod = $request->input('time_period', self::PERIOD_THIS_MONTH);
            $compareWith = $request->input('compare_with', null);

            $dateRange = $this->getDateRange($timePeriod, $request);
            $startDate = $dateRange['start'];
            $endDate = $dateRange['end'];
            $fromDate = $dateRange['from'];
            $toDate = $dateRange['to'];
            $daysCount = $startDate->diffInDays($endDate) + 1;

            // Generate cache key based on parameters
            $cacheKey = "revenue_report_{$timePeriod}_{$fromDate}_{$toDate}";
            if ($compareWith) {
                $cacheKey .= "_{$compareWith}";
            }

            // Use cache to improve performance
            $reportData = Cache::remember($cacheKey, self::CACHE_TIME, function () use ($startDate, $endDate) {
                return $this->getReportData($startDate, $endDate);
            });

            $compareData = null;
            if ($compareWith) {
                $compareRange = $this->getComparisonDateRange($timePeriod, $compareWith, $startDate, $endDate);
                $compareCacheKey = "compare_data_{$compareWith}_{$compareRange['start']}_{$compareRange['end']}";

                $compareData = Cache::remember($compareCacheKey, self::CACHE_TIME, function () use ($compareRange) {
                    return $this->getReportData($compareRange['start'], $compareRange['end']);
                });
            }

            $isEmpty = $reportData['summary']->total_orders === 0;

            if ($request->has('export')) {
                $fileName = 'bao_cao_doanh_thu_' . $fromDate . '_den_' . $toDate . '.xlsx';
                return Excel::download(new RevenueReportExport($reportData, $fromDate, $toDate), $fileName);
            }

            return view('admin.reports.revenue', array_merge($reportData, [
                'fromDate' => $fromDate,
                'toDate' => $toDate,
                'daysCount' => $daysCount,
                'time_period' => $timePeriod,
                'compareWith' => $compareWith,
                'isEmpty' => $isEmpty,
                'compareData' => $compareData,
            ]));
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Đã xảy ra lỗi khi tạo báo cáo: ' . $e->getMessage());
        }
    }

    /**
     * Get date range based on period selection
     */
    protected function getDateRange($timePeriod, $request)
    {
        $carbon = Carbon::now();

        switch ($timePeriod) {
            case self::PERIOD_TODAY:
                $startDate = $carbon->copy()->startOfDay();
                $endDate = $carbon->copy()->endOfDay();
                break;

            case self::PERIOD_YESTERDAY:
                $startDate = $carbon->copy()->subDay()->startOfDay();
                $endDate = $carbon->copy()->subDay()->endOfDay();
                break;

            case self::PERIOD_THIS_WEEK:
                $startDate = $carbon->copy()->startOfWeek()->startOfDay();
                $endDate = $carbon->copy()->endOfDay();
                break;

            case self::PERIOD_LAST_WEEK:
                $startDate = $carbon->copy()->subWeek()->startOfWeek()->startOfDay();
                $endDate = $carbon->copy()->subWeek()->endOfWeek()->endOfDay();
                break;

            case self::PERIOD_THIS_MONTH:
                $startDate = $carbon->copy()->startOfMonth()->startOfDay();
                $endDate = $carbon->copy()->endOfDay();
                break;

            case self::PERIOD_LAST_MONTH:
                $startDate = $carbon->copy()->subMonth()->startOfMonth()->startOfDay();
                $endDate = $carbon->copy()->subMonth()->endOfMonth()->endOfDay();
                break;

            case self::PERIOD_THIS_YEAR:
                $startDate = $carbon->copy()->startOfYear()->startOfDay();
                $endDate = $carbon->copy()->endOfDay();
                break;

            case self::PERIOD_LAST_YEAR:
                $startDate = $carbon->copy()->subYear()->startOfYear()->startOfDay();
                $endDate = $carbon->copy()->subYear()->endOfYear()->endOfDay();
                break;

            case self::PERIOD_CUSTOM:
                $fromDate = $request->input('from_date', $carbon->copy()->subDays(30)->toDateString());
                $toDate = $request->input('to_date', $carbon->copy()->toDateString());
                $startDate = Carbon::parse($fromDate)->startOfDay();
                $endDate = Carbon::parse($toDate)->endOfDay();
                break;

            default:
                $startDate = $carbon->copy()->startOfMonth()->startOfDay();
                $endDate = $carbon->copy()->endOfDay();
                $fromDate = $startDate->toDateString();
                $toDate = $endDate->toDateString();
        }

        // For non-custom periods, set from and to dates
        if ($timePeriod !== self::PERIOD_CUSTOM) {
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

    /**
     * Get comparison date range
     */
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

            case 'same_period_last_week':
                return [
                    'start' => $currentStart->copy()->subWeek(),
                    'end' => $currentEnd->copy()->subWeek(),
                ];

            case 'same_period_last_month':
                return [
                    'start' => $currentStart->copy()->subMonth(),
                    'end' => $currentEnd->copy()->subMonth(),
                ];

            case 'same_period_last_year':
                return [
                    'start' => $currentStart->copy()->subYear(),
                    'end' => $currentEnd->copy()->subYear(),
                ];

            default:
                return [
                    'start' => $currentStart->copy()->subDays($daysDiff + 1),
                    'end' => $currentStart->copy()->subDay(),
                ];
        }
    }

    /**
     * Get all report data
     */
    protected function getReportData($startDate, $endDate)
    {
        return [
            'summary' => $this->getRevenueSummary($startDate, $endDate),
            'revenueByDate' => $this->getRevenueByDate($startDate, $endDate),
            'revenueByPaymentMethod' => $this->getRevenueByPaymentMethod($startDate, $endDate),
            'topProducts' => $this->getTopProducts($startDate, $endDate),
            'orderStatusStats' => $this->getOrderStatusStats($startDate, $endDate),
            'revenueByCategory' => $this->getRevenueByCategory($startDate, $endDate),
            'customerLoyalty' => $this->getCustomerLoyalty($startDate, $endDate),
            'voucherUsage' => $this->getVoucherUsage($startDate, $endDate),
            'inventoryStats' => $this->getInventoryStats(),
        ];
    }

    /**
     * Get revenue summary - CHỈ TÍNH ĐƠN HÀNG ĐÃ HOÀN THÀNH
     */
    protected function getRevenueSummary($startDate, $endDate)
    {
        return Order::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select([
                DB::raw("COALESCE(SUM(total), 0) as total_revenue"),
                DB::raw("COALESCE(SUM(subtotal), 0) as subtotal"),
                DB::raw("COALESCE(SUM(tax), 0) as total_tax"),
                DB::raw("COALESCE(SUM(shipping), 0) as total_shipping"),
                DB::raw("COALESCE(SUM(discount_amount), 0) as total_discount"),
                DB::raw("COUNT(*) as total_orders"),
                DB::raw("COALESCE(AVG(total), 0) as avg_order_value"),
            ])
            ->first();
    }

    /**
     * Get revenue by date - CHỈ TÍNH ĐƠN HÀNG ĐÃ HOÀN THÀNH
     */
    protected function getRevenueByDate($startDate, $endDate)
    {
        return Order::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select([
                DB::raw('DATE(created_at) as date'),
                DB::raw('COALESCE(SUM(total), 0) as total_revenue'),
                DB::raw('COUNT(*) as order_count')
            ])
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Get revenue by payment method - CHỈ TÍNH ĐƠN HÀNG ĐÃ HOÀN THÀNH
     */
    protected function getRevenueByPaymentMethod($startDate, $endDate)
{
    $paymentMethods = Order::where('status', 'completed')
        ->whereBetween('created_at', [$startDate, $endDate])
        ->select([
            'payment_method',
            DB::raw('COALESCE(SUM(total), 0) as total_revenue'),
            DB::raw('COUNT(*) as order_count')
        ])
        ->groupBy('payment_method')
        ->orderByDesc('total_revenue')
        ->get();

    // Định nghĩa màu sắc cho từng phương thức thanh toán
    $paymentColors = [
        'cod' => '#14C9EF',    // Màu xanh dương nhạt cho COD
        'momo' => '#A50063',   // Màu đỏ cam cho Momo
        'vnpay' => '#288652',  // Màu xanh lá cho VNPay
    ];

    // Gán màu sắc cho mỗi phương thức thanh toán
    return $paymentMethods->map(function ($item) use ($paymentColors) {
        $item->color = $paymentColors[strtolower($item->payment_method)] ?? '#  ';
        return $item;
    });
}

    /**
     * Get top products - CHỈ TÍNH ĐƠN HÀNG ĐÃ HOÀN THÀNH
     */
    protected function getTopProducts($startDate, $endDate, $limit = 10)
    {
        return OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->where('orders.status', 'completed')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->select([
                'products.id',
                'products.name as product_name',
                'product_variants.sku',
                DB::raw('COALESCE(SUM(order_items.quantity), 0) as total_quantity'),
                DB::raw('COALESCE(SUM(order_items.quantity * order_items.price), 0) as total_revenue'),
            ])
            ->groupBy('products.id', 'products.name', 'product_variants.sku')
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get();
    }

    /**
     * Get order status statistics - TÍNH TẤT CẢ TRẠNG THÁI
     */
    protected function getOrderStatusStats($startDate, $endDate)
    {
        $statuses = Order::whereBetween('created_at', [$startDate, $endDate])
            ->select([
                'status',
                DB::raw('COUNT(*) as count'),
                DB::raw('COALESCE(SUM(total), 0) as total_value')
            ])
            ->groupBy('status')
            ->orderByDesc('count')
            ->get();

        $statusMap = [
            'pending' => ['name' => 'Chờ xử lý', 'color' => '#FCC61D'],
            'processing' => ['name' => 'Đang xử lý', 'color' => '#0B6FFD'],
            'completed' => ['name' => 'Hoàn thành', 'color' => '#1B8655'],
            'cancelled' => ['name' => 'Đã hủy', 'color' => '#e74a3b'],
            'shipped' => ['name' => 'Đã giao hàng', 'color' => '#36b9cc'],
            'failed' => ['name' => 'Giao hàng thất bại', 'color' => '#8C1007'],
            'picking' => ['name' => 'Đang lấy hàng', 'color' => '#14C9EF'],
            'shipping' => ['name' => 'Đang giao hàng', 'color' => '#6D747D'],
            'returned' => ['name' => 'Đã hoàn trả', 'color' => '#7480AB'],
            'return_requested' => ['name' => 'Yêu cầu hoàn trả', 'color' => '#f6c23e'],
        ];

        return $statuses->map(function ($item) use ($statusMap) {
            $statusInfo = $statusMap[$item->status] ?? ['name' => $item->status, 'color' => '#6c757d'];
            $item->status_name = $statusInfo['name'];
            $item->color = $statusInfo['color'];
            return $item;
        });
    }

    /**
     * Get revenue by category - CHỈ TÍNH ĐƠN HÀNG ĐÃ HOÀN THÀNH
     */
    protected function getRevenueByCategory($startDate, $endDate)
    {
        return Category::join('products', 'categories.id', '=', 'products.category_id')
            ->join('product_variants', 'products.id', '=', 'product_variants.product_id')
            ->join('order_items', 'product_variants.id', '=', 'order_items.product_variant_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 'completed')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->select([
                'categories.id',
                'categories.name',
                'categories.slug',
                DB::raw('COALESCE(SUM(order_items.quantity * order_items.price), 0) as total_revenue'),
                DB::raw('COALESCE(SUM(order_items.quantity), 0) as total_quantity'),
            ])
            ->groupBy('categories.id', 'categories.name', 'categories.slug')
            ->orderByDesc('total_revenue')
            ->get();
    }

    /**
     * Get customer loyalty statistics - LOGIC MỚI CHÍNH XÁC
     */
    protected function getCustomerLoyalty($startDate, $endDate)
    {
        // Lấy tất cả khách hàng đã mua hàng trong khoảng thời gian
        $customersInPeriod = Order::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('user_id')
            ->pluck('user_id')
            ->unique()
            ->toArray();

        if (empty($customersInPeriod)) {
            return (object) [
                'new_customers' => 0,
                'returning_customers' => 0,
                'loyal_customers' => 0,
                'total_customers' => 0,
            ];
        }

        // Lấy lịch sử mua hàng của các khách hàng này
        $customerOrders = Order::where('status', 'completed')
            ->whereIn('user_id', $customersInPeriod)
            ->select('user_id', DB::raw('COUNT(*) as order_count'))
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        $newCustomers = 0;
        $returningCustomers = 0;
        $loyalCustomers = 0;

        foreach ($customersInPeriod as $customerId) {
            $orderCount = $customerOrders->get($customerId)->order_count ?? 0;

            if ($orderCount === 1) {
                $newCustomers++;
            } elseif ($orderCount === 2) {
                $returningCustomers++;
            } elseif ($orderCount >= 3) {
                $loyalCustomers++;
            }
        }

        return (object) [
            'new_customers' => $newCustomers,
            'returning_customers' => $returningCustomers,
            'loyal_customers' => $loyalCustomers,
            'total_customers' => count($customersInPeriod),
        ];
    }

    /**
     * Get voucher usage statistics - CHỈ TÍNH ĐƠN HÀNG ĐÃ HOÀN THÀNH
     */
    protected function getVoucherUsage($startDate, $endDate)
    {
        return Voucher::leftJoin('orders', function ($join) use ($startDate, $endDate) {
            $join->on('vouchers.id', '=', 'orders.voucher_id')
                ->where('orders.status', 'completed')
                ->whereBetween('orders.created_at', [$startDate, $endDate]);
        })
            ->select([
                'vouchers.id',
                'vouchers.name',
                'vouchers.code',
                'vouchers.type',
                DB::raw('COUNT(orders.id) as usage_count'),
                DB::raw('COALESCE(SUM(orders.discount_amount), 0) as total_discount')
            ])
            ->groupBy('vouchers.id', 'vouchers.name', 'vouchers.code', 'vouchers.type')
            ->orderByDesc('usage_count')
            ->get();
    }

    /**
     * Get inventory statistics - ĐÃ SỬA LOGIC ĐẾM SẢN PHẨM HẾT HÀNG
     */
    protected function getInventoryStats()
    {
        // Tính tổng giá trị tồn kho
        $inventoryValue = DB::table('product_variants')
            ->join('stocks', 'product_variants.id', '=', 'stocks.product_variant_id')
            ->select(DB::raw('COALESCE(SUM(product_variants.price * stocks.quantity), 0) as total_value'))
            ->value('total_value');

        // Đếm số biến thể còn hàng (>0)
        $inStockVariants = ProductVariant::whereHas('stock', function ($query) {
            $query->where('quantity', '>', 0);
        })->count();

        // Đếm số biến thể hết hàng (<=0)
        $outOfStockVariants = ProductVariant::whereDoesntHave('stock', function ($query) {
            $query->where('quantity', '>', 0);
        })->orWhereHas('stock', function ($query) {
            $query->where('quantity', '<=', 0);
        })->count();

        // Thống kê theo sản phẩm (để giữ tính tương thích)
        $inStockProducts = Product::whereHas('variants.stock', function ($query) {
            $query->where('quantity', '>', 0);
        })->count();

        $outOfStockProducts = Product::whereDoesntHave('variants.stock', function ($query) {
            $query->where('quantity', '>', 0);
        })->count();

        return (object) [
            'total_products' => Product::count(),
            'total_variants' => ProductVariant::count(),
            'total_inventory_value' => $inventoryValue,
            'in_stock_products' => $inStockProducts,
            'out_of_stock_products' => $outOfStockProducts,
            'in_stock_variants' => $inStockVariants,
            'out_of_stock_variants' => $outOfStockVariants,
        ];
    }

    /**
     * Export revenue report to Excel
     */
    public function exportRevenueReport(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'time_period' => 'sometimes|in:today,yesterday,this_week,last_week,this_month,last_month,this_year,last_year,custom',
                'from_date' => 'required_if:time_period,custom|date',
                'to_date' => 'required_if:time_period,custom|date|after_or_equal:from_date',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $timePeriod = $request->input('time_period', self::PERIOD_THIS_MONTH);
            $dateRange = $this->getDateRange($timePeriod, $request);

            $reportData = $this->getReportData($dateRange['start'], $dateRange['end']);

            $fileName = 'bao_cao_doanh_thu_' . $dateRange['from'] . '_den_' . $dateRange['to'] . '.xlsx';
            return Excel::download(new RevenueReportExport($reportData, $dateRange['from'], $dateRange['to']), $fileName);
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Đã xảy ra lỗi khi xuất báo cáo: ' . $e->getMessage());
        }
    }
}
