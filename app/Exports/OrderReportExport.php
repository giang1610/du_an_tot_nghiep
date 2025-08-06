<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OrderReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $data;
    protected $fromDate;
    protected $toDate;
    protected $status;

    public function __construct($data, $fromDate, $toDate, $status = null)
    {
        $this->data = $data;
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
        $this->status = $status;
    }

    public function collection()
    {
        if ($this->status && isset($this->data['filteredOrders'])) {
            return $this->data['filteredOrders'];
        }
        
        return collect($this->data['statusStats']);
    }

    public function headings(): array
    {
        $title = $this->status 
            ? 'Danh sách đơn hàng ' . $this->status . ' từ ' . $this->fromDate . ' đến ' . $this->toDate
            : 'Báo cáo trạng thái đơn hàng từ ' . $this->fromDate . ' đến ' . $this->toDate;
        
        if ($this->status) {
            return [
                [$title],
                [
                    'Mã đơn',
                    'Khách hàng',
                    'Ngày đặt',
                    'Số lượng SP',
                    'Tổng tiền',
                    'Trạng thái'
                ]
            ];
        }
        
        return [
            [$title],
            [
                'Trạng thái',
                'Số lượng',
                'Tỷ lệ (%)'
            ]
        ];
    }

    public function map($item): array
    {
        if ($this->status) {
            return [
                $item->order_number,
                $item->user->name,
                $item->created_at->format('d/m/Y H:i'),
                $item->items->sum('quantity'),
                $item->total,
                $item->status
            ];
        }
        
        $total = $this->collection()->sum('count');
        $percentage = $total > 0 ? round(($item->count / $total) * 100, 2) : 0;
        
        return [
            $item->status,
            $item->count,
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
        return $this->status ? 'Đơn ' . $this->status : 'Trạng thái đơn';
    }
}