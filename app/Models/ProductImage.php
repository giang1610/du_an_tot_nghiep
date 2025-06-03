<?php

namespace App\Models;

<<<<<<< HEAD
=======
use Illuminate\Database\Eloquent\Factories\HasFactory;
>>>>>>> 498c9d628d49aa7a1e5326bd88ff5d9cf5a95a1b
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
<<<<<<< HEAD
    protected $table = 'product_images'; // Tên bảng, nếu khác với tên model theo chuẩn Laravel

    protected $fillable = [
        'product_id',
        'url',
        'is_default',
    ];

    // Quan hệ: ảnh thuộc về một sản phẩm
=======
    use HasFactory;

    protected $fillable = [
        'url',
        'product_id',
        'product_variant_id',
        'is_default',
    ];

>>>>>>> 498c9d628d49aa7a1e5326bd88ff5d9cf5a95a1b
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

<<<<<<< HEAD
    // Nếu muốn sử dụng $casts để hỗ trợ Boolean rõ ràng:
    protected $casts = [
        'is_default' => 'boolean',
    ];
=======
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
>>>>>>> 498c9d628d49aa7a1e5326bd88ff5d9cf5a95a1b
}
