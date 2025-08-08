<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AdvancedReportExport implements WithMultipleSheets
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
            new SummarySheet($this->reportData, $this->fromDate, $this->toDate),
            new RevenueByDateSheet($this->reportData),
            new TopProductsSheet($this->reportData),
            new CategorySheet($this->reportData),
            new CustomerSheet($this->reportData),
            new InventorySheet($this->reportData),
        ];

        return $sheets;
    }
}

class SummarySheet implements FromCollection, WithHeadings, WithTitle
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

    public function collection()
    {
        $summary = $this->reportData['summary'];
        
        return collect([
            ['Từ ngày', $this->fromDate],
            ['Đến ngày', $this->toDate],
            ['Tổng doanh thu', $summary->total_revenue],
            ['Tổng đơn hàng', $summary->total_orders],
            ['Giá trị trung bình', $summary->avg_order_value],
            ['Doanh thu thuần', $summary->gross_profit],
        ]);
    }

    public function headings(): array
    {
        return ['Chỉ số', 'Giá trị'];
    }

    public function title(): string
    {
        return 'Tổng quan';
    }
}

class RevenueByDateSheet implements FromCollection, WithHeadings, WithTitle
{
    protected $reportData;

    public function __construct($reportData)
    {
        $this->reportData = $reportData;
    }

    public function collection()
    {
        return $this->reportData['revenueByDate']->map(function ($item) {
            return [
                'Ngày' => $item->date,
                'Doanh thu' => $item->total_revenue,
                'Doanh thu thuần' => $item->gross_profit,
                'Số đơn hàng' => $item->order_count,
            ];
        });
    }

    public function headings(): array
    {
        return ['Ngày', 'Doanh thu', 'Doanh thu thuần', 'Số đơn hàng'];
    }

    public function title(): string
    {
        return 'Doanh thu theo ngày';
    }
}

class TopProductsSheet implements FromCollection, WithHeadings, WithTitle
{
    protected $reportData;

    public function __construct($reportData)
    {
        $this->reportData = $reportData;
    }

    public function collection()
    {
        return $this->reportData['topProducts']->map(function ($item) {
            return [
                'Sản phẩm' => $item->product_name,
                'SKU' => $item->sku,
                'Số lượng' => $item->total_quantity,
                'Doanh thu' => $item->total_revenue,
                'Lợi nhuận' => $item->gross_profit,
                'Tỷ suất lợi nhuận' => $item->profit_margin . '%',
            ];
        });
    }

    public function headings(): array
    {
        return ['Sản phẩm', 'SKU', 'Số lượng', 'Doanh thu', 'Lợi nhuận', 'Tỷ suất lợi nhuận'];
    }

    public function title(): string
    {
        return 'Sản phẩm bán chạy';
    }
}

class CategorySheet implements FromCollection, WithHeadings, WithTitle
{
    protected $reportData;

    public function __construct($reportData)
    {
        $this->reportData = $reportData;
    }

    public function collection()
    {
        $totalRevenue = $this->reportData['revenueByCategory']->sum('total_revenue');
        
        return $this->reportData['revenueByCategory']->map(function ($item) use ($totalRevenue) {
            return [
                'Danh mục' => $item->name,
                'Số lượng' => $item->total_quantity,
                'Doanh thu' => $item->total_revenue,
                'Lợi nhuận' => $item->gross_profit,
                'Tỷ suất lợi nhuận' => $item->profit_margin . '%',
                'Tỷ trọng' => $totalRevenue > 0 ? round(($item->total_revenue / $totalRevenue) * 100, 2) . '%' : '0%',
            ];
        });
    }

    public function headings(): array
    {
        return ['Danh mục', 'Số lượng', 'Doanh thu', 'Lợi nhuận', 'Tỷ suất lợi nhuận', 'Tỷ trọng'];
    }

    public function title(): string
    {
        return 'Doanh thu theo danh mục';
    }
}

class CustomerSheet implements FromCollection, WithHeadings, WithTitle
{
    protected $reportData;

    public function __construct($reportData)
    {
        $this->reportData = $reportData;
    }

    public function collection()
    {
        $data = collect();
        
        // Thông tin trung thành khách hàng
        $loyalty = $this->reportData['customerLoyalty'];
        $data->push(['Phân tích khách hàng trung thành']);
        $data->push(['Khách mới', $loyalty->new_customers]);
        $data->push(['Khách quay lại', $loyalty->returning_customers]);
        $data->push(['Khách thân thiết', $loyalty->loyal_customers]);
        
        $data->push([]);
        $data->push(['Tần suất mua hàng', 'Số lượng khách hàng']);
        
        foreach ($this->reportData['purchaseFrequency'] as $frequency) {
            $data->push([$frequency->frequency_range, $frequency->count]);
        }
        
        $data->push([]);
        $data->push(['Khu vực', 'Số khách hàng', 'Doanh thu']);
        
        foreach ($this->reportData['customerRegions'] as $region) {
            $data->push([$region->region, $region->count, $region->total_revenue]);
        }
        
        return $data;
    }

    public function headings(): array
    {
        return [];
    }

    public function title(): string
    {
        return 'Phân tích khách hàng';
    }
}

class InventorySheet implements FromCollection, WithHeadings, WithTitle
{
    protected $reportData;

    public function __construct($reportData)
    {
        $this->reportData = $reportData;
    }

    public function collection()
    {
        $data = collect();
        
        // Thêm thông tin tổng quan
        $stats = $this->reportData['inventoryStats'];
        $data->push(['Tổng giá trị tồn kho', $stats->total_inventory_value]);
        $data->push(['Tổng số sản phẩm', $stats->total_products]);
        $data->push(['Sản phẩm còn hàng', $stats->in_stock_products]);
        $data->push(['Sản phẩm sắp hết', $stats->low_stock_products]);
        $data->push(['Sản phẩm hết hàng', $stats->out_of_stock_products]);
        
        $data->push([]);
        $data->push(['Sản phẩm', 'Tồn kho', 'Giá trị', 'Trạng thái']);
        
        foreach ($this->reportData['inventoryProducts'] as $product) {
            $status = '';
            if ($product->stock_quantity == 0) {
                $status = 'Hết hàng';
            } elseif ($product->stock_quantity <= $product->low_stock_threshold) {
                $status = 'Sắp hết';
            } else {
                $status = 'Còn hàng';
            }
            
            $data->push([
                $product->name,
                $product->stock_quantity,
                $product->inventory_value,
                $status
            ]);
        }
        
        return $data;
    }

    public function headings(): array
    {
        return [];
    }

    public function title(): string
    {
        return 'Tồn kho';
    }
}