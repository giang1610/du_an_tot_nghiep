<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
<<<<<<< HEAD
use App\Models\Comment;
=======

>>>>>>> 498c9d628d49aa7a1e5326bd88ff5d9cf5a95a1b
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

<<<<<<< HEAD
     // Quan hệ nhiều-nhiều với Color0
=======
     // Quan hệ nhiều-nhiều với Color
>>>>>>> 498c9d628d49aa7a1e5326bd88ff5d9cf5a95a1b
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
<<<<<<< HEAD
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
=======
    
>>>>>>> 498c9d628d49aa7a1e5326bd88ff5d9cf5a95a1b
    
}
