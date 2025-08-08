<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RevenueReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $data;
    protected $fromDate;
    protected $toDate;

    public function __construct($data, $fromDate, $toDate)
    {
        $this->data = $data;
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
    }

    public function collection()
    {
        return collect($this->data['topProducts']);
    }

    public function headings(): array
    {
        return [
            ['Báo cáo doanh thu từ ' . $this->fromDate . ' đến ' . $this->toDate],
            [
                'STT',
                'Sản phẩm',
                'SKU',
                'Số lượng bán',
                'Doanh thu',
                'Lợi nhuận',
                'Biên lợi nhuận (%)'
            ]
        ];
    }

    public function map($product): array
    {
        static $index = 0;
        $index++;
        
        return [
            $index,
            $product->product_name,
            $product->sku,
            $product->total_quantity,
            $product->total_revenue,
            $product->gross_profit,
            $product->profit_margin
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => 'center'],
            ],
            2 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => ['argb' => 'FFD9D9D9'],
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Doanh thu';
    }
}