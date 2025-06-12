<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Size extends Model
{
    use HasFactory;

    protected $table = 'sizes';

    protected $fillable = [
        'name',
    ];

    // Quan hệ 1 size có thể có nhiều biến thể sản phẩm
    public function productVariants()
    {
        return $this->hasMany(ProductVariant::class);
    }
     public function products()
    {
        return $this->belongsToMany(Product::class, 'product_size', 'size_id', 'product_id');
    }

}
