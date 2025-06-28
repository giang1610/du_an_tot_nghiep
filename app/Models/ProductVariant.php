<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id', 'color_id', 'size_id', 'price', 'sale_price', 'image'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function color()
    {
        return $this->belongsTo(Color::class);
    }

    public function size()
    {
        return $this->belongsTo(Size::class);
    }

    public function stock()
    {
        return $this->hasOne(Stock::class, 'product_variant_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_variant_id');
    }

}
