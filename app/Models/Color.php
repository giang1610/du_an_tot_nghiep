<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Color extends Model
{
    protected $fillable = ['name', 'image'];

    use HasFactory;

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }
    public function products()
    {
        return $this->belongsToMany(Product::class, 'color_product', 'color_id', 'product_id');
    }
        use HasFactory;

    protected $table = 'colors';  // khai báo rõ tên bảng nếu khác với tên model số nhiều

    // Quan hệ 1 màu có thể có nhiều biến thể sản phẩm
   public function productVariants()
    {
        return $this->hasMany(ProductVariant::class);
    }

}
