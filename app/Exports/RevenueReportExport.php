<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class RevenueReportExport implements WithMultipleSheets
{
    protected $reportData;
    protected $fromDate;
    protected $toDate;

    public function __construct($reportData, $fromDate, $toDate)
    {
        $this->reportData = $reportData;
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
    }

    public function sheets(): array
    {
        $sheets = [
            new RevenueSummarySheet($this->reportData['summary'], $this->fromDate, $this->toDate),
            new RevenueByDateSheet($this->reportData['revenueByDate']),
            new RevenueByPaymentMethodSheet($this->reportData['revenueByPaymentMethod']),
            new TopProductsSheet($this->reportData['topProducts']),
            new OrderStatusSheet($this->reportData['orderStatusStats']),
            new RevenueByCategorySheet($this->reportData['revenueByCategory']),
            new CustomerLoyaltySheet($this->reportData['customerLoyalty']),
            new VoucherUsageSheet($this->reportData['voucherUsage']),
            new InventoryStatsSheet($this->reportData['inventoryStats']),
        ];

        return $sheets;
    }
}

class RevenueSummarySheet implements FromCollection, WithHeadings, WithTitle
{
    protected $summary;
    protected $fromDate;
    protected $toDate;

    public function __construct($summary, $fromDate, $toDate)
    {
        $this->summary = $summary;
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
    }

    public function collection()
    {
        return collect([
            [
                'Từ ngày' => $this->fromDate,
                'Đến ngày' => $this->toDate,
                'Tổng đơn hàng' => $this->summary->total_orders,
                'Tổng doanh thu' => $this->summary->total_revenue,
                'Giá trị đơn trung bình' => $this->summary->avg_order_value,
                'Tổng giảm giá' => $this->summary->total_discount,
                'Tổng thuế' => $this->summary->total_tax,
                'Tổng phí vận chuyển' => $this->summary->total_shipping,
            ]
        ]);
    }

    public function headings(): array
    {
        return [
            'Từ ngày',
            'Đến ngày',
            'Tổng đơn hàng',
            'Tổng doanh thu',
            'Giá trị đơn trung bình',
            'Tổng giảm giá',
            'Tổng thuế',
            'Tổng phí vận chuyển',
        ];
    }

    public function title(): string
    {
        return 'Tổng quan';
    }
}

class RevenueByDateSheet implements FromCollection, WithHeadings, WithTitle
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data->map(function ($item) {
            return [
                'Ngày' => $item->date,
                'Doanh thu' => $item->total_revenue,
                'Số đơn hàng' => $item->order_count,
            ];
        });
    }

    public function headings(): array
    {
        return ['Ngày', 'Doanh thu', 'Số đơn hàng'];
    }

    public function title(): string
    {
        return 'Doanh thu theo ngày';
    }
}

class RevenueByPaymentMethodSheet implements FromCollection, WithHeadings, WithTitle
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data->map(function ($item) {
            return [
                'Phương thức thanh toán' => $item->payment_method,
                'Doanh thu' => $item->total_revenue,
                'Số đơn hàng' => $item->order_count,
            ];
        });
    }

    public function headings(): array
    {
        return ['Phương thức thanh toán', 'Doanh thu', 'Số đơn hàng'];
    }

    public function title(): string
    {
        return 'Doanh thu theo PT thanh toán';
    }
}

class TopProductsSheet implements FromCollection, WithHeadings, WithTitle
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data->map(function ($item, $index) {
            return [
                'STT' => $index + 1,
                'Tên sản phẩm' => $item->product_name,
                'SKU' => $item->sku,
                'Số lượng bán' => $item->total_quantity,
                'Doanh thu' => $item->total_revenue,
            ];
        });
    }

    public function headings(): array
    {
        return ['STT', 'Tên sản phẩm', 'SKU', 'Số lượng bán', 'Doanh thu'];
    }

    public function title(): string
    {
        return 'Sản phẩm bán chạy';
    }
}

class OrderStatusSheet implements FromCollection, WithHeadings, WithTitle
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data->map(function ($item) {
            return [
                'Trạng thái' => $item->status_name,
                'Số đơn hàng' => $item->count,
                'Tổng giá trị' => $item->total_value,
            ];
        });
    }

    public function headings(): array
    {
        return ['Trạng thái', 'Số đơn hàng', 'Tổng giá trị'];
    }

    public function title(): string
    {
        return 'Trạng thái đơn hàng';
    }
}

class RevenueByCategorySheet implements FromCollection, WithHeadings, WithTitle
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data->map(function ($item) {
            return [
                'Danh mục' => $item->name,
                'Số lượng bán' => $item->total_quantity,
                'Doanh thu' => $item->total_revenue,
            ];
        });
    }

    public function headings(): array
    {
        return ['Danh mục', 'Số lượng bán', 'Doanh thu'];
    }

    public function title(): string
    {
        return 'Doanh thu theo danh mục';
    }
}

class CustomerLoyaltySheet implements FromCollection, WithHeadings, WithTitle
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect([
            [
                'Khách hàng mới' => $this->data->new_customers,
                'Khách quay lại' => $this->data->returning_customers,
                'Khách hàng thân thiết' => $this->data->loyal_customers,
                'Tổng khách hàng' => $this->data->total_customers,
            ]
        ]);
    }

    public function headings(): array
    {
        return ['Khách hàng mới', 'Khách quay lại', 'Khách hàng thân thiết', 'Tổng khách hàng'];
    }

    public function title(): string
    {
        return 'Khách hàng';
    }
}

class VoucherUsageSheet implements FromCollection, WithHeadings, WithTitle
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data->map(function ($item) {
            return [
                'Mã voucher' => $item->code,
                'Tên' => $item->name,
                'Loại' => $item->type == 'shipping' ? 'Miễn phí vận chuyển' : 'Giảm giá sản phẩm',
                'Số lần sử dụng' => $item->usage_count,
                'Tổng giảm giá' => $item->total_discount,
            ];
        });
    }

    public function headings(): array
    {
        return ['Mã voucher', 'Tên', 'Loại', 'Số lần sử dụng', 'Tổng giảm giá'];
    }

    public function title(): string
    {
        return 'Sử dụng voucher';
    }
}

class InventoryStatsSheet implements FromCollection, WithHeadings, WithTitle
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect([
            [
                'Tổng sản phẩm' => $this->data->total_products,
                'Tổng biến thể' => $this->data->total_variants,
                'Giá trị tồn kho' => $this->data->total_inventory_value,
                'Sản phẩm có hàng' => $this->data->in_stock_products,
                'Sản phẩm hết hàng' => $this->data->out_of_stock_products,
            ]
        ]);
    }

    public function headings(): array
    {
        return ['Tổng sản phẩm', 'Tổng biến thể', 'Giá trị tồn kho', 'Sản phẩm có hàng', 'Sản phẩm hết hàng'];
    }

    public function title(): string
    {
        return 'Tồn kho';
    }
}