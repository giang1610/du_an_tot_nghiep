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
use App\Models\Voucher;
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
        $timePeriod = $request->input('time_period', self::PERIOD_THIS_MONTH);
        $compareWith = $request->input('compare_with', null);
        
        $dateRange = $this->getDateRange($timePeriod, $request);
        $startDate = $dateRange['start'];
        $endDate = $dateRange['end'];
        $fromDate = $dateRange['from'];
        $toDate = $dateRange['to'];
        
        $compareData = null;
        if ($compareWith) {
            $compareRange = $this->getComparisonDateRange($timePeriod, $compareWith, $startDate, $endDate);
            $compareData = $this->getReportData($compareRange['start'], $compareRange['end']);
        }

        $reportData = $this->getReportData($startDate, $endDate);
        $isEmpty = $reportData['summary']->total_orders === 0;

        if ($request->has('export')) {
            $fileName = 'bao_cao_doanh_thu_' . $fromDate . '_den_' . $toDate . '.xlsx';
            return Excel::download(new RevenueReportExport($reportData, $fromDate, $toDate), $fileName);
        }

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

    public function exportRevenueReport(Request $request)
    {
        $timePeriod = $request->input('time_period', self::PERIOD_THIS_MONTH);
        $dateRange = $this->getDateRange($timePeriod, $request);
        
        $reportData = $this->getReportData($dateRange['start'], $dateRange['end']);
        
        $fileName = 'bao_cao_doanh_thu_' . $dateRange['from'] . '_den_' . $dateRange['to'] . '.xlsx';
        return Excel::download(new RevenueReportExport($reportData, $dateRange['from'], $dateRange['to']), $fileName);
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
            'revenueByPaymentMethod' => $this->getRevenueByPaymentMethod($startDate, $endDate),
            'topProducts' => $this->getTopProducts($startDate, $endDate),
            'orderStatusStats' => $this->getOrderStatusStats($startDate, $endDate),
            'revenueByCategory' => $this->getRevenueByCategory($startDate, $endDate),
            'customerLoyalty' => $this->getCustomerLoyalty($startDate, $endDate),
            'voucherUsage' => $this->getVoucherUsage($startDate, $endDate),
            'inventoryStats' => $this->getInventoryStats(),
        ];
    }

    protected function getRevenueSummary($startDate, $endDate)
    {
        return Order::whereBetween('created_at', [$startDate, $endDate])
            ->select([
                DB::raw("SUM(total) as total_revenue"),
                DB::raw("SUM(subtotal) as subtotal"),
                DB::raw("SUM(tax) as total_tax"),
                DB::raw("SUM(shipping) as total_shipping"),
                DB::raw("SUM(discount_amount) as total_discount"),
                DB::raw("COUNT(*) as total_orders"),
                DB::raw("AVG(total) as avg_order_value"),
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

    protected function getRevenueByPaymentMethod($startDate, $endDate)
    {
        return Order::whereBetween('created_at', [$startDate, $endDate])
            ->select([
                'payment_method',
                DB::raw('SUM(total) as total_revenue'),
                DB::raw('COUNT(*) as order_count')
            ])
            ->groupBy('payment_method')
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
                'product_variants.sku',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.quantity * order_items.price) as total_revenue'),
            ])
            ->groupBy('products.id', 'products.name', 'product_variants.sku')
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
            'pending' => 'Chờ xử lý',
            'processing' => 'Đang xử lý',
            'shipped' => 'Đã giao hàng',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
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
            'total_customers' => $userOrders->count(),
        ];
    }

    protected function getVoucherUsage($startDate, $endDate)
    {
        return Voucher::leftJoin('orders', function($join) use ($startDate, $endDate) {
                $join->on('vouchers.id', '=', 'orders.voucher_id')
                     ->whereBetween('orders.created_at', [$startDate, $endDate]);
            })
            ->select([
                'vouchers.id',
                'vouchers.name',
                'vouchers.code',
                'vouchers.type',
                DB::raw('COUNT(orders.id) as usage_count'),
                DB::raw('SUM(orders.discount_amount) as total_discount')
            ])
            ->groupBy('vouchers.id', 'vouchers.name', 'vouchers.code', 'vouchers.type')
            ->orderByDesc('usage_count')
            ->get();
    }

    protected function getInventoryStats()
    {
        return (object) [
            'total_products' => Product::count(),
            'total_variants' => ProductVariant::count(),
            'total_inventory_value' => ProductVariant::sum(DB::raw('price * (SELECT quantity FROM stocks WHERE stocks.product_variant_id = product_variants.id)')),
            'in_stock_products' => Product::whereHas('variants', function($query) {
                $query->whereHas('stock', function($q) {
                    $q->where('quantity', '>', 0);
                });
            })->count(),
            'out_of_stock_products' => Product::whereDoesntHave('variants', function($query) {
                $query->whereHas('stock', function($q) {
                    $q->where('quantity', '>', 0);
                });
            })->count(),
        ];
    }
}