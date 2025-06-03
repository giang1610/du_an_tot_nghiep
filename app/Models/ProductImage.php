<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    protected $table = 'product_images'; // Tên bảng, nếu khác với tên model theo chuẩn Laravel

    protected $fillable = [
        'product_id',
        'url',
        'is_default',
    ];

    // Quan hệ: ảnh thuộc về một sản phẩm
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Nếu muốn sử dụng $casts để hỗ trợ Boolean rõ ràng:
    protected $casts = [
        'is_default' => 'boolean',
    ];
}
