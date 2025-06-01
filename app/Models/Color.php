<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    use HasFactory;

    protected $table = 'colors';  // khai báo rõ tên bảng nếu khác với tên model số nhiều

    protected $fillable = [
        'name',
        'image',  // nếu bạn lưu đường dẫn ảnh hoặc tên ảnh trong trường này
    ];

    // Quan hệ 1 màu có thể có nhiều biến thể sản phẩm
   public function productVariants()
    {
        return $this->hasMany(ProductVariant::class);
    }
     public function products()
    {
        return $this->belongsToMany(Product::class, 'color_product', 'color_id', 'product_id');
    }

}
