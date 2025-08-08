<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PromotionReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $data;
    protected $filter;

    public function __construct($data, $filter = 'active')
    {
        $this->data = $data;
        $this->filter = $filter;
    }

    public function collection()
    {
        if ($this->filter === 'active') {
            return $this->data['activePromotions'] ?? collect();
        } elseif ($this->filter === 'ended') {
            return $this->data['endedPromotions'] ?? collect();
        } else {
            return $this->data['promotionPerformance'] ?? collect();
        }
    }

    public function headings(): array
    {
        $title = match($this->filter) {
            'active' => 'Danh sách chương trình khuyến mãi đang chạy',
            'ended' => 'Danh sách chương trình khuyến mãi đã kết thúc',
            default => 'Hiệu quả chương trình khuyến mãi',
        };
        
        $columns = [
            'Tên chương trình',
            'Mã',
            'Loại',
            'Giảm giá',
            'Ngày bắt đầu',
            'Ngày kết thúc',
            'Số đơn',
            'Tổng giảm giá'
        ];
        
        if ($this->filter === 'custom') {
            array_push($columns, 'Tổng doanh thu', 'Tỷ lệ sử dụng');
        }
        
        return [
            [$title],
            $columns
        ];
    }

    public function map($promotion): array
    {
        $row = [
            $promotion->name,
            $promotion->code,
            $promotion->type == 'shipping' ? 'Vận chuyển' : 'Sản phẩm',
            $promotion->discount_type == 'amount' 
                ? number_format($promotion->discount_amount) . 'đ' 
                : $promotion->discount_percent . '%',
            $promotion->start_date->format('d/m/Y'),
            $promotion->end_date->format('d/m/Y'),
            $promotion->orders_count,
            number_format($promotion->orders_sum_discount_amount) . 'đ'
        ];
        
        if ($this->filter === 'custom') {
            $usageRate = $promotion->quantity 
                ? round(($promotion->orders_count / $promotion->quantity) * 100, 2) . '%'
                : 'Không giới hạn';
            
            array_push($row, 
                number_format($promotion->orders_sum_total) . 'đ',
                $usageRate
            );
        }
        
        return $row;
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
        return match($this->filter) {
            'active' => 'KM đang chạy',
            'ended' => 'KM đã kết thúc',
            default => 'Hiệu quả KM',
        };
    }
}