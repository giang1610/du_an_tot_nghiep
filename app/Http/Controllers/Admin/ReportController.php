<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Category;
use App\Exports\RevenueReportExport;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    const FILTER_TODAY = 'today';
    const FILTER_WEEK = '7days';
    const FILTER_MONTH = '30days';
    const FILTER_THIS_MONTH = 'this_month';
    const FILTER_YEAR = '1year';
    const FILTER_THIS_YEAR = 'this_year';
    const FILTER_CUSTOM = 'custom';

    public function revenueReport(Request $request)
    {
        $filter = $request->input('filter', self::FILTER_TODAY);
        $compareWith = $request->input('compare_with', null);
        
        $dateRange = $this->getDateRange($filter, $request);
        $startDate = $dateRange['start'];
        $endDate = $dateRange['end'];
        $fromDate = $dateRange['from'];
        $toDate = $dateRange['to'];
        
        $compareData = null;
        if ($compareWith) {
            $compareRange = $this->getComparisonDateRange($filter, $compareWith, $startDate, $endDate);
            $compareData = $this->getReportData($compareRange['start'], $compareRange['end']);
        }

        $reportData = $this->getReportData($startDate, $endDate);
        $isEmpty = $reportData['summary']->completed_orders === 0;

        return view('admin.reports.revenue', array_merge($reportData, [
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'filter' => $filter,
            'compareWith' => $compareWith,
            'isEmpty' => $isEmpty,
            'compareData' => $compareData,
        ]));
       
    }

    protected function getDateRange($filter, $request)
    {
        switch ($filter) {
            case self::FILTER_TODAY:
                $startDate = Carbon::today()->startOfDay();
                $endDate = Carbon::now()->endOfDay();
                $fromDate = $startDate->toDateString();
                $toDate = $endDate->toDateString();
                break;

            case self::FILTER_WEEK:
                $startDate = Carbon::now()->subDays(7)->startOfDay();
                $endDate = Carbon::now()->endOfDay();
                $fromDate = $startDate->toDateString();
                $toDate = $endDate->toDateString();
                break;

            case self::FILTER_MONTH:
                $startDate = Carbon::now()->subDays(30)->startOfDay();
                $endDate = Carbon::now()->endOfDay();
                $fromDate = $startDate->toDateString();
                $toDate = $endDate->toDateString();
                break;

            case self::FILTER_THIS_MONTH:
                $startDate = Carbon::now()->startOfMonth()->startOfDay();
                $endDate = Carbon::now()->endOfDay();
                $fromDate = $startDate->toDateString();
                $toDate = $endDate->toDateString();
                break;

            case self::FILTER_YEAR:
                $startDate = Carbon::now()->subYear()->startOfDay();
                $endDate = Carbon::now()->endOfDay();
                $fromDate = $startDate->toDateString();
                $toDate = $endDate->toDateString();
                break;

            case self::FILTER_THIS_YEAR:
                $startDate = Carbon::now()->startOfYear()->startOfDay();
                $endDate = Carbon::now()->endOfDay();
                $fromDate = $startDate->toDateString();
                $toDate = $endDate->toDateString();
                break;

            case self::FILTER_CUSTOM:
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

    protected function getComparisonDateRange($currentFilter, $compareWith, $currentStart, $currentEnd)
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
            'topProducts' => $this->getTopProducts($startDate, $endDate),
            'orderStatusStats' => $this->getOrderStatusStats($startDate, $endDate),
            'revenueByCategory' => $this->getRevenueByCategory($startDate, $endDate),
        ];
        
    }

    protected function getRevenueSummary($startDate, $endDate)
    {
        return DB::table('orders')
            ->select([
                DB::raw("SUM(CASE WHEN status = 'completed' THEN total ELSE 0 END) as completed_revenue"),
                DB::raw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_orders"),
                DB::raw("AVG(CASE WHEN status = 'completed' THEN total ELSE NULL END) as avg_order_value"),
                // Tính lợi nhuận dựa trên giá gốc (nếu không có cost_price thì coi như lợi nhuận = doanh thu)
                DB::raw("SUM(CASE WHEN status = 'completed' THEN total ELSE 0 END) as gross_profit"),
                DB::raw("100 as profit_margin") // Giả sử lợi nhuận 100% nếu không có giá gốc
            ])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->first();
    }

    protected function getRevenueByDate($startDate, $endDate)
    {
        return DB::table('orders')
            ->select([
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(CASE WHEN status = "completed" THEN total ELSE 0 END) as total_revenue'),
                DB::raw('SUM(CASE WHEN status = "completed" THEN total ELSE 0 END) as gross_profit'),
                DB::raw('COUNT(CASE WHEN status = "completed" THEN 1 ELSE NULL END) as order_count')
            ])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();
    }

    protected function getTopProducts($startDate, $endDate, $limit = 10)
    {
        return DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->where('orders.status', 'completed')
            ->select([
                'products.id',
                'products.name as product_name',
                'product_variants.image',
                'product_variants.sku',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.quantity * order_items.price) as total_revenue'),
                // Giả sử lợi nhuận = doanh thu nếu không có giá gốc
                DB::raw('SUM(order_items.quantity * order_items.price) as gross_profit'),
                DB::raw('100 as profit_margin')
            ])
            ->groupBy('products.id', 'products.name', 'product_variants.image', 'product_variants.sku')
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get();
    }

    protected function getOrderStatusStats($startDate, $endDate)
    {
        $statuses = Order::query()
            ->select([
                'status',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total) as total_value')
            ])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('status')
            ->orderByDesc('count')
            ->get();

        $statusMap = [
             'cancelled' => 'Hủy đơn hàng',
            'pending' => 'Chờ xử lý',
            'processing' => 'Đang xử lý',
            'picking' => 'Đang lấy hàng',
            'shipping' => 'Đang giao hàng',
            'shipped' => 'Đã giao hàng',
            'return_requested' => 'Yêu cầu hoàn hàng',
            'delivered' => 'Đã nhận hàng',
            'returned' => 'Đồng ý hoàn hàng',
            'restocked' => 'Hàng đã trả về kho',
            'completed' => 'Đơn hàng hoàn thành',
            'failed_1' => 'Giao hàng thất bại lần 1',
            'failed_2' => 'Giao hàng thất bại lần 2',
            'failed' => 'Giao hàng thất bại',
            'shipper_en_route' => 'Shipper đang đến lấy hàng',

        ];

        return $statuses->map(function ($item) use ($statusMap) {
            $item->status = $statusMap[$item->status] ?? $item->status;
            return $item;
        });
    }

    protected function getRevenueByCategory($startDate, $endDate)
    {
        return Category::query()
            ->select([
                'categories.id',
                'categories.name',
                'categories.slug',
                DB::raw('SUM(order_items.quantity * order_items.price) as total_revenue'),
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                // Giả sử lợi nhuận = doanh thu nếu không có giá gốc
                DB::raw('SUM(order_items.quantity * order_items.price) as gross_profit'),
                DB::raw('100 as profit_margin')
            ])
            ->join('products', 'categories.id', '=', 'products.category_id')
            ->join('product_variants', 'products.id', '=', 'product_variants.product_id')
            ->join('order_items', 'product_variants.id', '=', 'order_items.product_variant_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->where('orders.status', 'completed')
            ->groupBy('categories.id', 'categories.name', 'categories.slug')
            ->orderByDesc('total_revenue')
            ->get();
    }

    public function exportRevenueReport(Request $request)
    {
        $filter = $request->input('filter', self::FILTER_TODAY);
        $dateRange = $this->getDateRange($filter, $request);
        
        $reportData = $this->getReportData($dateRange['start'], $dateRange['end']);
        $fileName = 'revenue_report_' . $dateRange['from'] . '_to_' . $dateRange['to'] . '.xlsx';

        return Excel::download(new RevenueReportExport($reportData, $dateRange['from'], $dateRange['to']), $fileName);
    }
    
}