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
    public function revenueReport(Request $request)
    {
        // Xử lý các tùy chọn lọc nhanh
        $filter = $request->input('filter', 'today');

        // Thiết lập ngày mặc định
        switch ($filter) {
            case 'today':
                $startDate = Carbon::today()->startOfDay();
                $endDate = Carbon::now()->endOfDay();
                $fromDate = $startDate->toDateString();
                $toDate = $endDate->toDateString();
                break;

            case '7days':
                $startDate = Carbon::now()->subDays(7)->startOfDay();
                $endDate = Carbon::now()->endOfDay();
                $fromDate = $startDate->toDateString();
                $toDate = $endDate->toDateString();
                break;

            case '30days':
                $startDate = Carbon::now()->subDays(30)->startOfDay();
                $endDate = Carbon::now()->endOfDay();
                $fromDate = $startDate->toDateString();
                $toDate = $endDate->toDateString();
                break;

            case 'thismonth':
                $startDate = Carbon::now()->startOfMonth()->startOfDay();
                $endDate = Carbon::now()->endOfDay();
                $fromDate = $startDate->toDateString();
                $toDate = $endDate->toDateString();
                break;

            case 'custom':
                $fromDate = $request->input('from_date', Carbon::now()->subDays(30)->toDateString());
                $toDate = $request->input('to_date', Carbon::now()->toDateString());
                $startDate = Carbon::parse($fromDate)->startOfDay();
                $endDate = Carbon::parse($toDate)->endOfDay();
                break;
        }

        // Validate
        $request->validate([
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
        ]);

        // Lấy dữ liệu
        $reportData = $this->getReportData($startDate, $endDate);
        $isEmpty = $reportData['summary']->completed_orders === 0;

        return view('admin.reports.revenue', array_merge($reportData, [
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'filter' => $filter,
            'isEmpty' => $isEmpty,
            'dateRange' => $this->generateDateRangeArray($startDate, $endDate)
        ]));
    }

    // public function exportRevenueReport(Request $request)
    // {
    //     $fromDate = $request->input('from_date', Carbon::now()->subDays(30)->toDateString());
    //     $toDate = $request->input('to_date', Carbon::now()->toDateString());
    //     $startDate = Carbon::parse($fromDate)->startOfDay();
    //     $endDate = Carbon::parse($toDate)->endOfDay();

    //     $reportData = $this->getReportData($startDate, $endDate);

    //     $fileName = 'bao_cao_doanh_thu_' . $fromDate . '_den_' . $toDate . '.xlsx';

    //     return Excel::download(new RevenueReportExport($reportData, $fromDate, $toDate), $fileName);
    // }

    protected function getReportData($startDate, $endDate) // Lấy dữ liệu báo cáo
    {
        return [
            'summary' => $this->getRevenueSummary($startDate, $endDate),
            'revenueByDate' => $this->getRevenueByDate($startDate, $endDate),
            'topProducts' => $this->getTopProducts($startDate, $endDate),
            'orderStatusStats' => $this->getOrderStatusStats($startDate, $endDate),
            'revenueByCategory' => $this->getRevenueByCategory($startDate, $endDate),
        ];
    }

    protected function generateDateRangeArray($startDate, $endDate) // Tạo mảng ngày trong khoảng thời gian
    {
        $dates = [];
        $current = clone $startDate;

        while ($current <= $endDate) {
            $dates[] = $current->toDateString();
            $current->addDay();
        }

        return $dates;
    }

    protected function getRevenueSummary($startDate, $endDate) // Lấy tóm tắt doanh thu
    {
        return DB::table('orders')
            ->select([
                DB::raw("SUM(CASE WHEN status = 'completed' THEN total ELSE 0 END) as total_revenue"),
                DB::raw('COUNT(*) as total_orders'),
                DB::raw("SUM(CASE WHEN status = 'completed' THEN total ELSE 0 END) as completed_revenue"),
                DB::raw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_orders"),
                DB::raw("SUM(CASE WHEN payment_status = 'paid' THEN 1 ELSE 0 END) as paid_orders"),
                DB::raw("AVG(CASE WHEN status = 'completed' THEN total ELSE NULL END) as avg_order_value"),
                DB::raw("MAX(total) as max_order_value"),
                DB::raw("MIN(CASE WHEN status = 'completed' THEN total ELSE NULL END) as min_completed_order_value")
            ])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->first();
    }

    protected function getRevenueByDate($startDate, $endDate) // Lấy doanh thu theo ngày
    {
        $rawData = DB::table('orders')
            ->select([
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total) as total_revenue'),
                DB::raw('COUNT(*) as order_count')
            ])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        // Điền đầy đủ các ngày trong khoảng
        $dateRange = $this->generateDateRangeArray($startDate, $endDate);
        $result = [];

        foreach ($dateRange as $date) {
            $found = $rawData->firstWhere('date', $date);
            $result[] = [
                'date' => $date,
                'total_revenue' => $found ? $found->total_revenue : 0,
                'order_count' => $found ? $found->order_count : 0
            ];
        }

        return collect($result);
    }

    protected function getTopProducts($startDate, $endDate, $limit = 10) // Lấy top sản phẩm bán chạy
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
                DB::raw('SUM(order_items.quantity * order_items.price) as total_revenue')
            ])
            ->groupBy('products.id', 'products.name', 'product_variants.image', 'product_variants.sku')
            ->orderByDesc('total_quantity')
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
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
            'processing' => 'Đang xử lý',
            'pending' => 'Chờ xác nhận',
            'shipped' => 'Đã giao hàng',
            'picking' => 'Đang lấy hàng',
            'shipping' => 'Đang vận chuyển',
            'refunded' => 'Đã hoàn tiền',
            'failed' => 'Thất bại',
            'returned' => 'Đã trả hàng',
        ];

        return $statuses->map(function ($item) use ($statusMap) {
            $item->status = $statusMap[$item->status] ?? $item->status;
            return $item;
        });
    }

    protected function getRevenueByCategory($startDate, $endDate) // Thống kê doanh thu theo danh mục
    {
        return Category::query()
            ->select([
                'categories.id',
                'categories.name',
                'categories.slug',
                DB::raw('SUM(order_items.quantity * order_items.price) as total_revenue'),
                DB::raw('SUM(order_items.quantity) as total_quantity')
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

    public function inventoryReport(Request $request) // Báo cáo tồn kho
    {
        // Báo cáo tồn kho
        $inventoryStats = Product::query()
            ->with(['variants', 'category'])
            ->select([
                'products.id',
                'products.name',
                'products.sku',
                'products.price',
                DB::raw('SUM(product_variants.quantity) as total_quantity'),
                DB::raw('SUM(product_variants.quantity * products.price) as inventory_value')
            ])
            ->leftJoin('product_variants', 'products.id', '=', 'product_variants.product_id')
            ->groupBy('products.id', 'products.name', 'products.sku', 'products.price')
            ->orderBy('total_quantity')
            ->get();

        // Sản phẩm sắp hết hàng (dưới 10)
        $lowStockProducts = Product::query()
            ->whereHas('variants', function ($query) {
                $query->where('quantity', '<', 10);
            })
            ->with(['variants' => function ($query) {
                $query->where('quantity', '<', 10);
            }, 'category'])
            ->get();

        return view('admin.reports.inventory', compact(
            'inventoryStats',
            'lowStockProducts'
        ));
    }
}
