<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InventoryReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data['lowStockProducts']);
    }

    public function headings(): array
    {
        return [
            ['Báo cáo tồn kho - Danh sách sản phẩm sắp hết hàng'],
            [
                'Sản phẩm',
                'SKU',
                'Màu sắc',
                'Kích thước',
                'Số lượng tồn',
                'Giá bán',
                'Giá vốn',
                'Giá trị tồn'
            ]
        ];
    }

    public function map($product): array
    {
        return [
            $product->product->name,
            $product->sku,
            $product->color->name,
            $product->size->name,
            $product->quantity,
            $product->price,
            $product->cost_price,
            $product->quantity * $product->cost_price
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
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
        return 'Tồn kho';
    }
}