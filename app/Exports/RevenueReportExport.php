<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RevenueReportExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles
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
        return collect([
            ['BÁO CÁO DOANH THU', '', '', ''],
            ['Từ ngày: ' . $this->fromDate . ' đến ngày: ' . $this->toDate, '', '', ''],
            ['', '', '', ''],
            ['TỔNG QUAN', '', '', ''],
            ['Tổng doanh thu', number_format($this->reportData['summary']->total_revenue) . '₫', '', ''],
            ['Tổng đơn hàng', $this->reportData['summary']->total_orders, '', ''],
            ['Giá trị đơn trung bình', number_format($this->reportData['summary']->avg_order_value) . '₫', '', ''],
            ['Đơn hàng lớn nhất', number_format($this->reportData['summary']->max_order_value) . '₫', '', ''],
            ['', '', '', ''],
            ['DOANH THU THEO NGÀY', '', '', ''],
            ['Ngày', 'Doanh thu', 'Số đơn', '']
        ])->merge(
            $this->reportData['revenueByDate']->map(function ($item) {
                return [
                    $item->date,
                    number_format($item->total_revenue) . '₫',
                    $item->order_count,
                    ''
                ];
            })
        )->merge([
            ['', '', '', ''],
            ['SẢN PHẨM BÁN CHẠY', '', '', ''],
            ['STT', 'Tên sản phẩm', 'Số lượng', 'Doanh thu']
        ])->merge(
            $this->reportData['topProducts']->map(function ($item, $index) {
                return [
                    $index + 1,
                    $item->product_name,
                    $item->total_quantity,
                    number_format($item->total_revenue) . '₫'
                ];
            })
        )->merge([
            ['', '', '', ''],
            ['TRẠNG THÁI ĐƠN HÀNG', '', '', ''],
            ['Trạng thái', 'Số lượng', 'Tổng giá trị', '']
        ])->merge(
            $this->reportData['orderStatusStats']->map(function ($item) {
                return [
                    $item->status_name,
                    $item->count,
                    number_format($item->total_value) . '₫',
                    ''
                ];
            })
        );
    }

    public function headings(): array
    {
        return [];
    }

    public function map($row): array
    {
        return $row;
    }

    public function title(): string
    {
        return 'Báo cáo doanh thu';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 16]],
            4 => ['font' => ['bold' => true]],
            10 => ['font' => ['bold' => true]],
            11 => ['font' => ['bold' => true]],
            'A11:C11' => ['fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'DDDDDD']]],
            'A' . (12 + count($this->reportData['revenueByDate'])) => ['font' => ['bold' => true]],
            'A' . (13 + count($this->reportData['revenueByDate'])) => ['font' => ['bold' => true]],
            'A' . (14 + count($this->reportData['revenueByDate'])) => ['font' => ['bold' => true]],
            'A' . (14 + count($this->reportData['revenueByDate'])) . ':D' . 
                (14 + count($this->reportData['revenueByDate'])) => 
                ['fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'DDDDDD']]],
            'A' . (15 + count($this->reportData['revenueByDate']) + count($this->reportData['topProducts'])) => ['font' => ['bold' => true]],
            'A' . (16 + count($this->reportData['revenueByDate']) + count($this->reportData['topProducts'])) => ['font' => ['bold' => true]],
            'A' . (17 + count($this->reportData['revenueByDate']) + count($this->reportData['topProducts'])) => ['font' => ['bold' => true]],
            'A' . (17 + count($this->reportData['revenueByDate']) + count($this->reportData['topProducts'])) . ':D' . 
                (17 + count($this->reportData['revenueByDate']) + count($this->reportData['topProducts'])) => 
                ['fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'DDDDDD']]],
        ];
    }
}