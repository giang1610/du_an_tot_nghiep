<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'type',
        'status',
        'short_description',
        'description',
        'thumbnail',
    ];

    /**
     * Quan hệ: 1 sản phẩm thuộc 1 danh mục
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Quan hệ: 1 sản phẩm có nhiều biến thể
     */
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * Quan hệ: 1 sản phẩm có nhiều ảnh
     */
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }
}
