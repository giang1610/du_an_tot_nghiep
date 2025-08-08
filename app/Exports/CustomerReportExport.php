<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomerReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
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
        return collect($this->data['customerDemographics']['byLocation']);
    }

    public function headings(): array
    {
        return [
            ['Báo cáo khách hàng từ ' . $this->fromDate . ' đến ' . $this->toDate],
            [
                'Khu vực',
                'Số lượng khách hàng',
                'Tỷ lệ (%)'
            ]
        ];
    }

    public function map($location): array
    {
        $total = $this->collection()->sum('count');
        $percentage = $total > 0 ? round(($location->count / $total) * 100, 2) : 0;
        
        return [
            $location->location,
            $location->count,
            $percentage
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
        return 'Khách hàng';
    }
}