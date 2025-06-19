<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProductVariant;
use App\Models\ProductImage;


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
        'price_products',
    ];

    /**
     * Quan hệ: 1 sản phẩm thuộc 1 danh mục
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    /**
     * Quan hệ: 1 sản phẩm có nhiều biến thể
     */
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    // Quan hệ nhiều-nhiều với Color
    public function colors()
    {
        return $this->belongsToMany(Color::class, 'color_product', 'product_id', 'color_id');
    }
    public function sizes()
    {
        return $this->belongsToMany(Size::class, 'product_size', 'product_id', 'size_id');
    }

    /**
     * Quan hệ: 1 sản phẩm có nhiều ảnh
     */

    // public function images()
    // {
    //     return $this->hasMany(ProductImage::class);
    // }

    public function getImgAttribute()
    {
        return url('storage/products/' . $this->thumbnail);
    }
    protected $appends = ['img'];
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

}
