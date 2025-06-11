<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id', 'sku', 'price', 'sale_price',
        'sale_start_date', 'sale_end_date', 'image', 'stock',
        'color_id', 'size_id'
    ];
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }
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

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }
    public function getCurrentPriceAttribute()
    {
        if ($this->sale_price && now()->between($this->sale_start_date, $this->sale_end_date)) {
            return $this->sale_price;
        }
        return $this->price;
    }

}
